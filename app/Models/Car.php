<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand',
        'model',
         'year',
        'vin',
        'color',
        'license_plate',
        'mileage',
        'fuel_type',
        'investor_id',
        'manager_id',
        'price',
        'gps_tracker_id',
        'deal_count',
        'status',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'mileage' => 'integer',
        'deal_count' => 'integer',
        'year' => 'integer',
    ];

    // Статусы автомобиля
    const STATUS_AVAILABLE = 'available';
    const STATUS_IN_DEAL = 'in_deal';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_SOLD = 'sold';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_AVAILABLE => 'Свободен',
            self::STATUS_IN_DEAL => 'В сделке',
            self::STATUS_MAINTENANCE => 'На обслуживании',
            self::STATUS_SOLD => 'Продан',
            'in_draft_deal' => 'В черновой сделке',
        ];
    }

    // Типы топлива
    const FUEL_92 = 'АИ-92';
    const FUEL_95 = 'АИ-95';
    const FUEL_98 = 'АИ-98';
    const FUEL_DIESEL = 'Дизель';
    const FUEL_GAS = 'Газ';
    const FUEL_ELECTRO = 'Электро';

    public static function getFuelTypes(): array
    {
        return [
            self::FUEL_92 => 'АИ-92',
            self::FUEL_95 => 'АИ-95',
            self::FUEL_98 => 'АИ-98',
            self::FUEL_DIESEL => 'Дизель',
            self::FUEL_GAS => 'Газ',
            self::FUEL_ELECTRO => 'Электро',
        ];
    }


/**
 * Scope для фильтрации по статусу
 */
public function scopeByStatus($query, $status)
{
    if ($status) {
        return $query->where('status', $status);
    }
    return $query;
}

/**
 * Scope для фильтрации по менеджеру
 */
public function scopeByManager($query, $managerId)
{
    if ($managerId) {
        return $query->where('manager_id', $managerId);
    }
    return $query;
}

/**
 * Scope для фильтрации по инвестору
 */
public function scopeByInvestor($query, $investorId)
{
    if ($investorId) {
        return $query->where('investor_id', $investorId);
    }
    return $query;
}

/**
 * Scope для фильтрации по типу топлива
 */
public function scopeByFuelType($query, $fuelType)
{
    if ($fuelType) {
        return $query->where('fuel_type', $fuelType);
    }
    return $query;
}

/**
 * Scope для поиска
 */
public function scopeSearch($query, $searchTerm)
{
    if (!$searchTerm) {
        return $query;
    }
    
    return $query->where(function($q) use ($searchTerm) {
        $q->where('brand', 'like', "%{$searchTerm}%")
          ->orWhere('model', 'like', "%{$searchTerm}%")
          ->orWhere('license_plate', 'like', "%{$searchTerm}%")
          ->orWhere('vin', 'like', "%{$searchTerm}%")
          ->orWhere('color', 'like', "%{$searchTerm}%");
    });
}

/**
 * Scope для фильтрации по году
 */
public function scopeByYearRange($query, $yearFrom, $yearTo)
{
    if ($yearFrom) {
        $query->where('year', '>=', $yearFrom);
    }
    
    if ($yearTo) {
        $query->where('year', '<=', $yearTo);
    }
    
    return $query;
}

/**
 * Scope для фильтрации по цене
 */
public function scopeByPriceRange($query, $priceFrom, $priceTo)
{
    if ($priceFrom) {
        $query->where('price', '>=', $priceFrom);
    }
    
    if ($priceTo) {
        $query->where('price', '<=', $priceTo);
    }
    
    return $query;
}

/**
 * Scope для фильтрации по дате добавления
 */
public function scopeByDateRange($query, $startDate, $endDate)
{
    if ($startDate) {
        $query->whereDate('created_at', '>=', $startDate);
    }
    
    if ($endDate) {
        $query->whereDate('created_at', '<=', $endDate);
    }
    
    return $query;
}




    // Связи
    public function deals()
    {
        return $this->hasMany(Deal::class);
    }
    
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
    
    // Связь с инвестором
    public function investor()
    {
        return $this->belongsTo(User::class, 'investor_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CarDocument::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(CarExpense::class);
    }

    // Вспомогательные методы
    public function getFullNameAttribute(): string
    {
        $year = $this->year ? " ({$this->year})" : '';
        return "{$this->brand} {$this->model} ({$this->license_plate})";
    }

    public function getStatusTextAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? 'Неизвестно';
    }

    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    /**
     * Проверить, можно ли редактировать автомобиль
     */
    public function canBeEdited(): bool
    {
        // Нельзя редактировать проданные автомобили (даже админам)
        if ($this->status === self::STATUS_SOLD) {
            return false;
        }
        
        // Администраторы могут редактировать автомобили в сделке
        if (auth()->check() && auth()->user()->isAdmin()) {
            return true;
        }
        
        // Менеджеры могут редактировать только свободные автомобили
        return $this->status === self::STATUS_AVAILABLE;
    }

    /**
     * Проверить, можно ли удалить автомобиль
     */
    public function canBeDeleted(): bool
    {
        // Администраторы могут удалять любые автомобили (включая проданные)
        if (auth()->check() && auth()->user()->isAdmin()) {
            return true;
        }
        
        // Обычные пользователи не могут удалять автомобили в сделке или проданные
        if (in_array($this->status, [self::STATUS_IN_DEAL, self::STATUS_SOLD])) {
            return false;
        }
        
        return true;
    }

    /**
     * Проверить, можно ли добавлять документы
     */
    public function canAddDocuments(): bool
    {
        // Документы можно добавлять ко всем, кроме проданных
        return $this->status !== self::STATUS_SOLD;
    }

    /**
     * Проверить, можно ли добавлять расходы
     */
    public function canAddExpenses(): bool
    {
        // Расходы можно добавлять ко всем, кроме проданных
        return $this->status !== self::STATUS_SOLD;
    }

    /**
     * Проверить, можно ли создавать сделки
     */
    public function canCreateDeals(): bool
    {
        // Сделки можно создавать только со свободными автомобилями
        // И только если у них нет черновых сделок
        return $this->status === self::STATUS_AVAILABLE && !$this->hasDraftDeals();
    }

    /**
     * Проверить, есть ли у автомобиля черновые сделки
     */
    public function hasDraftDeals(): bool
    {
        return $this->deals()
            ->where('status', Deal::STATUS_DRAFT)
            ->exists();
    }

    /**
     * Проверить, есть ли у автомобиля активные сделки
     */
    public function hasActiveDeals(): bool
    {
        return $this->deals()
            ->whereIn('status', [Deal::STATUS_ACTIVE, Deal::STATUS_OVERDUE])
            ->exists();
    }

    /**
     * Получить текущий статус автомобиля (с учетом черновых сделок)
     */
    public function getActualStatusAttribute(): string
    {
        if ($this->hasActiveDeals()) {
            return self::STATUS_IN_DEAL;
        }
        
        if ($this->hasDraftDeals()) {
            return 'in_draft_deal'; // Добавим специальный статус
        }
        
        return $this->status;
    }

    /**
     * Получить текст актуального статуса
     */
    public function getActualStatusTextAttribute(): string
    {
        $statuses = self::getStatuses();
        return $statuses[$this->actual_status] ?? 'Неизвестно';
    }

    /**
     * Получить объект инвестора
     */
    public function getInvestorAttribute()
    {
        if (empty($this->investor_id)) {
            return null;
        }
        
        // Если investor_id это число
        if (is_numeric($this->investor_id)) {
            return User::find($this->investor_id);
        }
        
        // Если это JSON строка
        if (is_string($this->investor_id) && json_decode($this->investor_id, true)) {
            $data = json_decode($this->investor_id, true);
            
            // Создаем временный объект пользователя
            $user = new User();
            $user->id = $data['id'] ?? null;
            $user->name = $data['name'] ?? 'Неизвестный инвестор';
            $user->email = $data['email'] ?? null;
            $user->commission_percent = $data['commission_percent'] ?? null;
            
            return $user;
        }
        
        return null;
    }

    /**
     * Получить имя инвестора
     */
    public function getInvestorNameAttribute(): string
    {
        $investor = $this->investor;
        
        if (!$investor) {
            return 'Не назначен';
        }
        
        return $investor->name;
    }
    
    
    /**
 * Автоматически обновить статус автомобиля на основе сделок
 * ЛОГИКА:
 * 1. STATUS_IN_DEAL если есть активные или просроченные сделки
 * 2. STATUS_DRAFT (или специальный статус) если есть черновики
 * 3. STATUS_AVAILABLE если нет сделок или только завершенные
 */
public function updateStatusBasedOnDeals(): void
{
    \Log::info("=== Начало обновления статуса автомобиля {$this->id} ===");
    
    // 1. Проверяем наличие ЗАВЕРШЕННЫХ сделок типа "лизинг с выкупом"
    $hasCompletedLeaseDeals = $this->deals()
        ->where('status', Deal::STATUS_COMPLETED)
        ->where('deal_type', Deal::TYPE_LEASE) // Только лизинг с выкупом
        ->exists();
    
    // Если есть завершенные сделки лизинга с выкупом - автомобиль ПРОДАН
    if ($hasCompletedLeaseDeals) {
        $newStatus = self::STATUS_SOLD;
        \Log::info("Найден завершенный лизинг с выкупом. Автомобиль {$this->id} помечен как ПРОДАН");
    } else {
        // 2. Проверяем наличие АКТИВНЫХ или ПРОСРОЧЕННЫХ сделок
        $hasActiveOrOverdueDeals = $this->deals()
            ->whereIn('status', ['active', 'overdue'])
            ->exists();
        
        // 3. Проверяем наличие ЧЕРНОВИКОВ
        $hasDraftDeals = $this->deals()
            ->where('status', 'draft')
            ->exists();
        
        \Log::info("Есть активные/просроченные сделки: " . ($hasActiveOrOverdueDeals ? 'ДА' : 'НЕТ'));
        \Log::info("Есть черновики: " . ($hasDraftDeals ? 'ДА' : 'НЕТ'));
        
        // Определяем новый статус
        if ($hasActiveOrOverdueDeals) {
            $newStatus = self::STATUS_IN_DEAL;
        } elseif ($hasDraftDeals) {
            $newStatus = self::STATUS_IN_DRAFT_DEAL;
        } else {
            $newStatus = self::STATUS_AVAILABLE;
        }
    }
    
    \Log::info("Старый статус: " . $this->status);
    \Log::info("Новый статус: " . $newStatus);
    
    // Обновляем только если изменился
    if ($this->status !== $newStatus) {
        $this->status = $newStatus;
        $this->save();
        
        \Log::info("Статус автомобиля {$this->id} изменен: {$this->status}");
    } else {
        \Log::info("Статус не изменился");
    }
    
    \Log::info("=== Конец обновления статуса автомобиля ===");
}
    
    // Добавьте в список констант STATUS_DRAFT_DEAL
const STATUS_IN_DRAFT_DEAL = 'В черновой сделке';

public function scopeAvailable($query)
{
    return $query->where('status', 'available');
}
    
}