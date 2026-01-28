<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'last_name',
        'first_name',
        'middle_name',
        'passport_series',
        'passport_number',
        'passport_issued_by',
        'passport_issued_date',
        'passport_division_code',
        'registration_address',
        'residential_address',
        'drivers_license',
        'phone',
        'additional_phone',
        'guarantor',
        'notes',
        'status',
    ];

    protected $casts = [
        'passport_issued_date' => 'date',
    ];

    // Статусы клиента
    const STATUS_ACTIVE = 'active';        // Свободен (нет сделок вообще)
    const STATUS_DRAFT = 'draft';          // Черновик (есть только черновые сделки)
    const STATUS_IN_DEAL = 'in_deal';      // В сделке (есть активная ПОДПИСАННАЯ сделка)
    const STATUS_ARCHIVED = 'archived';    // Архивный

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Свободен',
            self::STATUS_DRAFT => 'Черновик',
            self::STATUS_IN_DEAL => 'В сделке',
            self::STATUS_ARCHIVED => 'Архивный',
        ];
    }

/**
 * Автоматически обновить статус клиента на основе его сделок
 * НОВАЯ ПРАВИЛЬНАЯ ЛОГИКА:
 * 1. STATUS_IN_DEAL если есть активные или просроченные сделки
 * 2. STATUS_DRAFT если есть только черновики (и нет активных/просроченных)
 * 3. STATUS_ACTIVE если нет сделок или только завершенные
 */
public function updateStatusBasedOnDeals(): void
{
    \Log::info("=== Начало обновления статуса клиента {$this->id} ===");
    
    // Получаем ВСЕ сделки клиента
    $deals = $this->deals()->get();
    
    \Log::info("Всего сделок у клиента: " . $deals->count());
    
    // Проверяем наличие АКТИВНЫХ или ПРОСРОЧЕННЫХ сделок
    $hasActiveOrOverdueDeals = $deals->contains(function ($deal) {
        return in_array($deal->status, ['active', 'overdue']);
    });
    
    // Проверяем наличие ЧЕРНОВИКОВ
    $hasDraftDeals = $deals->contains(function ($deal) {
        return $deal->status === 'draft';
    });
    
    \Log::info("Есть активные/просроченные сделки: " . ($hasActiveOrOverdueDeals ? 'ДА' : 'НЕТ'));
    \Log::info("Есть черновики: " . ($hasDraftDeals ? 'ДА' : 'НЕТ'));
    
    // Определяем новый статус
    if ($hasActiveOrOverdueDeals) {
        $newStatus = self::STATUS_IN_DEAL;
    } elseif ($hasDraftDeals) {
        $newStatus = self::STATUS_DRAFT;
    } else {
        $newStatus = self::STATUS_ACTIVE;
    }
    
    \Log::info("Старый статус: " . $this->status);
    \Log::info("Новый статус: " . $newStatus);
    
    // Обновляем только если изменился
    if ($this->status !== $newStatus) {
        $this->status = $newStatus;
        $this->save();
        
        \Log::info("Статус клиента {$this->id} изменен: {$this->status}");
    } else {
        \Log::info("Статус не изменился");
    }
    
    \Log::info("=== Конец обновления статуса клиента ===");
}

    // Полное ФИО
    public function getFullNameAttribute()
    {
        return trim($this->last_name . ' ' . $this->first_name . ' ' . $this->middle_name);
    }

    public function documents()
    {
        return $this->hasMany(ClientDocument::class);
    }

    // Связь со сделками
    public function deals()
    {
        return $this->hasMany(Deal::class);
    }

    // ПРОВЕРКА 1: Есть ли активные ПОДПИСАННЫЕ сделки (статус 'active')
    public function hasActiveSignedDeals(): bool
    {
        return $this->deals()
            ->where('status', 'active')
            ->exists();
    }

    // ПРОВЕРКА 2: Есть ли сделки в черновике (статус 'draft')
    public function hasDraftDeals(): bool
    {
        return $this->deals()
            ->where('status', 'draft')
            ->exists();
    }

    // ПРОВЕРКА 3: Есть ли просроченные сделки (статус 'overdue')
    public function hasOverdueDeals(): bool
    {
        return $this->deals()
            ->where('status', 'overdue')
            ->exists();
    }

    // Старый метод для совместимости
    public function hasActiveDeals(): bool
    {
        // Проверяет ТОЛЬКО активные подписанные сделки
        return $this->hasActiveSignedDeals();
    }

    // Проверка, можно ли удалить клиента
    // Нельзя удалить, если есть ЛЮБЫЕ сделки (даже черновики)
    public function canBeDeleted(): bool
    {
        return $this->deals()->count() === 0;
    }

    // Проверка, можно ли создать сделку с клиентом
    // По ТЗ: нельзя создать, если уже есть АКТИВНАЯ ПОДПИСАННАЯ сделка
    // Но можно создать, если есть только черновик
    public function canCreateDeals(): bool
    {
        return !$this->hasActiveSignedDeals(); // ТОЛЬКО если нет активных подписанных сделок
    }

    // Получить текст статуса
    public function getStatusTextAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? 'Неизвестно';
    }

    // Получить цвет статуса для отображения
    public function getStatusColorAttribute(): string
    {
        $colors = [
            self::STATUS_ACTIVE => 'free',    // Зеленый
            self::STATUS_DRAFT => 'deal-draw',        // Серый (черновик)
            self::STATUS_IN_DEAL => 'deal-active',   // Желтый
            self::STATUS_ARCHIVED => 'secondary',// Серый
        ];

        return $colors[$this->status] ?? 'secondary';
    }
    
    /**
     * Получить статус сделок для отображения в таблице
     */
    public function getDealsStatusAttribute(): string
    {
        if ($this->hasActiveSignedDeals()) {
            return 'active_deals';
        } elseif ($this->hasDraftDeals()) {
            return 'draft_deals';
        } elseif ($this->hasOverdueDeals()) {
            return 'overdue_deals';
        } else {
            return 'no_deals';
        }
    }

    /**
     * Получить текст статуса сделок
     */
    public function getDealsStatusTextAttribute(): string
    {
        return match($this->deals_status) {
            'active_deals' => 'В активной сделке',
            'draft_deals' => 'Договор не подписан',
            'overdue_deals' => 'Просрочка',
            default => 'Нет сделок'
        };
    }

    /**
     * Получить цвет статуса сделок
     */
    public function getDealsStatusColorAttribute(): string
    {
        return match($this->deals_status) {
            'active_deals' => 'warning',    // Оранжевый
            'draft_deals' => 'info',        // Синий
            'overdue_deals' => 'danger',    // Красный
            default => 'secondary'          // Серый
        };
    }
    
    /**
     * Получить ближайшую дату оплаты из активных сделок
     * Возвращает null, если нет активных сделок
     */
    public function getNextPaymentDateAttribute(): ?string
    {
        $activeDeal = $this->deals()
            ->where('status', 'active')
            ->first();
            
        if (!$activeDeal) {
            return null;
        }
        
        // Временное решение - показываем дату начала
        return $activeDeal->start_date?->format('Y-m-d');
    }
    
    /**
     * Проверяем, есть ли у клиента активные подписанные сделки (алиас)
     */
    public function getHasActiveDealsAttribute(): bool
    {
        return $this->hasActiveSignedDeals();
    }
    
    /**
     * Получить прогресс до даты оплаты (заглушка)
     * Пока возвращаем фиксированное значение
     */
    public function getPaymentProgressAttribute(): int
    {
        return 100; // По умолчанию зеленая полоска
    }
    
    /**
     * Получить цвет градиента на основе прогресса
     */
    public function getProgressColorAttribute(): string
    {
        return 'success'; // По умолчанию зеленый
    }
}