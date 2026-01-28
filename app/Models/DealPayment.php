<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class DealPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'deal_id',
        'payment_number',
        'due_date',
        'amount',
        'status',
        'paid_at',
        'payment_method',
        'transaction_id',
        'notes',
        'payment_document_path',
        'is_deferred',
        'deferred_reason'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'is_deferred' => 'boolean',
    ];

    // Статусы платежей
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_OVERDUE = 'overdue';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Ожидает оплаты',
            self::STATUS_PAID => 'Оплачен',
            self::STATUS_OVERDUE => 'Просрочен',
        ];
    }

    // Методы оплаты
    const METHOD_CASH = 'cash';
    const METHOD_CARD = 'card';
    const METHOD_TRANSFER = 'transfer';
    const METHOD_OTHER = 'other';

    public static function getPaymentMethods(): array
    {
        return [
            self::METHOD_CASH => 'Наличные',
            self::METHOD_CARD => 'Банковская карта',
            self::METHOD_TRANSFER => 'Банковский перевод',
            self::METHOD_OTHER => 'Другое',
        ];
    }

    // Связи
    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function notifications()
    {
        return $this->hasMany(DealNotification::class, 'payment_id');
    }

    // Вспомогательные методы
    public function getStatusTextAttribute(): string
    {
        if ($this->is_deferred) {
            return 'Отсрочка';
        }
        
        return self::getStatuses()[$this->status] ?? 'Неизвестно';
    }

    public function getPaymentMethodTextAttribute(): string
    {
        return self::getPaymentMethods()[$this->payment_method] ?? 'Неизвестно';
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2, '.', ' ') . ' ₽';
    }

    public function getIsOverdueAttribute(): bool
    {
        // Отсроченные платежи не считаются просроченными
        if ($this->is_deferred) {
            return false;
        }
        
        return $this->status === self::STATUS_PENDING && 
               $this->due_date < Carbon::today();
    }

    public function getDaysOverdueAttribute(): ?int
    {
        if (!$this->is_overdue || $this->is_deferred) {
            return null;
        }

        return Carbon::parse($this->due_date)->diffInDays(Carbon::now());
    }

/**
 * Получить оригинальную сумму платежа (до отсрочки)
 */
public function getOriginalAmountAttribute(): float
{
    // Пытаемся получить оригинальную сумму из БД
    $original = $this->getOriginal('amount');
    
    if ($original !== null) {
        return (float) $original;
    }
    
    // Если не удалось, используем текущую сумму
    return (float) $this->amount;
}


/**
 * Отметить платеж как оплаченный с прикреплением документа
 * Обновлено для поддержки отсрочек
 */
public function markAsPaid(string $method = null, string $transactionId = null, string $notes = null, string $documentPath = null, bool $isDeferred = false, string $deferredReason = null, float $amount = null): void
{
    \Log::info("=== Начало markAsPaid() для платежа {$this->id} ===");
    \Log::info("Параметры: isDeferred = {$isDeferred}, deferredReason = {$deferredReason}, amount = {$amount}, текущий is_deferred = {$this->is_deferred}");
    
    $this->status = self::STATUS_PAID;
    $this->paid_at = now();
    
    // Если отменяем существующую отсрочку
    if ($this->is_deferred && !$isDeferred) {
        \Log::info("Отменяем существующую отсрочку");
        $this->is_deferred = false;
        $this->deferred_reason = null;
    } else {
        $this->is_deferred = $isDeferred;
        
        if ($isDeferred) {
            $this->deferred_reason = $deferredReason;
        }
    }
    
    // Устанавливаем сумму
    if ($isDeferred) {
        // Для отсроченных платежей устанавливаем сумму в 0
        $this->amount = 0;
        \Log::info("Платеж помечен как отсрочка, сумма установлена в 0");
    } elseif ($amount !== null) {
        // Для обычных платежей используем переданную сумму
        $this->amount = $amount;
        \Log::info("Сумма платежа установлена: {$amount}");
    }
    
    if ($method) {
        $this->payment_method = $method;
    }
    
    if ($transactionId) {
        $this->transaction_id = $transactionId;
    }
    
    if ($notes) {
        $this->notes = $notes;
    }
    
    if ($documentPath) {
        $this->payment_document_path = $documentPath;
    }
    
    $this->save();
    
    if ($isDeferred) {
        \Log::info("Платеж {$this->id} помечен как отсрочка");
        \Log::info("Отсрочка платежа зарегистрирована", [
            'payment_id' => $this->id,
            'deferred_reason' => $deferredReason,
        ]);
    } else {
        if ($this->getOriginal('is_deferred')) {
            \Log::info("Отсрочка платежа {$this->id} отменена");
        } else {
            \Log::info("Платеж {$this->id} помечен как оплаченный");
        }
    }
    
    // Обновляем статус сделки
    \Log::info("Обновляем статус сделки {$this->deal_id}");
    $this->deal->updateStatus();
    
    \Log::info("=== Конец markAsPaid() ===");
}





    /**
     * Проверить, есть ли прикрепленный платежный документ
     */
    public function hasPaymentDocument(): bool
    {
        return !empty($this->payment_document_path);
    }

    /**
     * Получить имя файла платежного документа
     */
    public function getPaymentDocumentNameAttribute(): ?string
    {
        if (!$this->payment_document_path) {
            return null;
        }
        
        return basename($this->payment_document_path);
    }

    /**
     * Получить расширение файла платежного документа
     */
    public function getPaymentDocumentExtensionAttribute(): ?string
    {
        if (!$this->payment_document_path) {
            return null;
        }
        
        return pathinfo($this->payment_document_path, PATHINFO_EXTENSION);
    }

    /**
     * Получить полный путь к платежному документу
     */
    public function getPaymentDocumentFullPathAttribute(): ?string
    {
        if (!$this->payment_document_path) {
            return null;
        }
        
        return storage_path('app/public/' . $this->payment_document_path);
    }

    /**
     * Получить краткую информацию об отсрочке
     */
    public function getDeferredInfoAttribute(): ?string
    {
        if (!$this->is_deferred) {
            return null;
        }
        
        $info = "Отсрочка платежа";
        
        if ($this->deferred_reason) {
            $info .= ": " . $this->deferred_reason;
        }
        
        return $info;
    }

    /**
     * Проверка, можно ли зарегистрировать этот платеж как отсрочку
     */
    public function canBeDeferred(): bool
    {
        // Можно отсрочить только ожидающие платежи
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Scope для получения отсроченных платежей
     */
    public function scopeDeferred($query)
    {
        return $query->where('is_deferred', true);
    }

    /**
     * Scope для получения платежей, которые можно отсрочить
     */
    public function scopeCanBeDeferred($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Получить иконку для типа платежа
     */
    public function getPaymentIconAttribute(): string
    {
        if ($this->is_deferred) {
            return 'fa-clock text-warning';
        }
        
        if ($this->status === self::STATUS_PAID) {
            return 'fa-check-circle text-success';
        }
        
        if ($this->is_overdue) {
            return 'fa-exclamation-triangle text-danger';
        }
        
        return 'fa-clock text-secondary';
    }

    /**
     * Получить CSS класс для строки таблицы
     */
    public function getTableRowClassAttribute(): string
    {
        if ($this->is_deferred) {
            return 'table-warning';
        }
        
        if ($this->is_overdue) {
            return 'table-danger';
        }
        
        if ($this->status === self::STATUS_PAID) {
            return 'table-success';
        }
        
        return '';
    }

    public function sendReminder(): bool
    {
        // Не отправляем напоминания для отсроченных платежей
        if ($this->is_deferred) {
            return false;
        }
        
        // Здесь будет логика отправки SMS
        // Пока просто создаем запись об уведомлении
        DealNotification::create([
            'deal_id' => $this->deal_id,
            'payment_id' => $this->id,
            'type' => 'sms',
            'phone' => $this->deal->client->phone,
            'message' => "Напоминание: оплата по договору {$this->deal->deal_number} в размере {$this->formatted_amount} до {$this->due_date->format('d.m.Y')}",
            'status' => 'pending',
        ]);

        return true;
    }

    /**
     * Создать отсрочку платежа
     */
    public function createDeferral(string $reason, int $userId = null): bool
    {
        if (!$this->canBeDeferred()) {
            throw new \Exception('Этот платеж нельзя отсрочить');
        }
        
        $this->markAsPaid(
            method: self::METHOD_OTHER,
            notes: $this->notes . "\n\nОтсрочка платежа. " . $reason,
            isDeferred: true,
            deferredReason: $reason
        );
        
        // Логируем действие
        if ($userId) {
            activity()
                ->performedOn($this)
                ->causedBy(\App\Models\User::find($userId))
                ->withProperties(['reason' => $reason])
                ->log('Платеж отсрочен');
        }
        
        return true;
    }

    /**
     * Получить полную информацию о платеже для отображения
     */
    public function getFullInfoAttribute(): array
    {
        return [
            'id' => $this->id,
            'payment_number' => $this->payment_number === 0 ? 'Первоначальный взнос' : 'Платеж №' . $this->payment_number,
            'amount' => $this->formatted_amount,
            'status' => $this->status_text,
            'due_date' => $this->due_date->format('d.m.Y'),
            'paid_at' => $this->paid_at ? $this->paid_at->format('d.m.Y H:i') : null,
            'payment_method' => $this->payment_method_text,
            'transaction_id' => $this->transaction_id,
            'notes' => $this->notes,
            'is_deferred' => $this->is_deferred,
            'deferred_reason' => $this->deferred_reason,
            'has_document' => $this->hasPaymentDocument(),
            'document_name' => $this->payment_document_name,
            'days_overdue' => $this->days_overdue,
            'is_overdue' => $this->is_overdue,
            'icon' => $this->payment_icon,
            'row_class' => $this->table_row_class,
        ];
    }
}
