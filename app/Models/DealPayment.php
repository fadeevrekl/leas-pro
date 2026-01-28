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
        'payment_document_path', // Добавляем поле для пути к платежному документу
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
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
        return $this->status === self::STATUS_PENDING && 
               $this->due_date < Carbon::today();
    }

    public function getDaysOverdueAttribute(): ?int
    {
        if (!$this->is_overdue) {
            return null;
        }

        return Carbon::parse($this->due_date)->diffInDays(Carbon::now());
    }

    /**
     * Отметить платеж как оплаченный с прикреплением документа
     */
public function markAsPaid(string $method = null, string $transactionId = null, string $notes = null, string $documentPath = null): void
{
    \Log::info("=== Начало markAsPaid() для платежа {$this->id} ===");
    
    $this->status = self::STATUS_PAID;
    $this->paid_at = Carbon::now();
    
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
    \Log::info("Платеж {$this->id} помечен как оплаченный");
    
    // Обновляем статус сделки (а она уже должна обновить клиента и автомобиль)
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

    public function sendReminder(): bool
    {
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
}