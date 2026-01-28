<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Deal extends Model
{
    use HasFactory;

    protected $fillable = [
        'deal_number',
        'client_id',
        'car_id',
        'manager_id',
        'deal_type',
        'total_amount',
        'initial_payment',
        'payment_count',
        'payment_amount',
        'start_date',
        'end_date',
        'payment_period',
        'payment_day',
        'status',
        'sms_notifications',
        'last_sms_sent_at',
        'sms_count',
        'contract_path',
        'contract_signed_date',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'initial_payment' => 'decimal:2',
        'payment_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'contract_signed_date' => 'date',
        'last_sms_sent_at' => 'datetime',
        'sms_notifications' => 'boolean',
    ];

    // Типы сделок
    const TYPE_RENTAL = 'rental';
    const TYPE_LEASE = 'lease';

    public static function getDealTypes(): array
    {
        return [
            self::TYPE_RENTAL => 'Аренда',
            self::TYPE_LEASE => 'Лизинг с выкупом',
        ];
    }

    // Периоды оплаты
    const PERIOD_DAY = 'day';
    const PERIOD_WEEK = 'week';
    const PERIOD_MONTH = 'month';

    public static function getPaymentPeriods(): array
    {
        return [
            self::PERIOD_DAY => 'По дням',
            self::PERIOD_WEEK => 'По неделям',
            self::PERIOD_MONTH => 'По месяцам',
        ];
    }

    // Статусы сделок
    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_OVERDUE = 'overdue';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Черновик',
            self::STATUS_ACTIVE => 'Активная',
            self::STATUS_COMPLETED => 'Завершена',
            self::STATUS_CANCELLED => 'Отменена',
            self::STATUS_OVERDUE => 'Просрочена',
        ];
    }

    // Дни недели для русских названий
    public static function getWeekDays(): array
    {
        return [
            1 => 'Понедельник',
            2 => 'Вторник',
            3 => 'Среда',
            4 => 'Четверг',
            5 => 'Пятница',
            6 => 'Суббота',
            7 => 'Воскресенье',
        ];
    }





 /**
     * Получить сумму арендного платежа прописью
     */
    public function getPaymentAmountWordsAttribute(): string
    {
        return $this->num2str($this->payment_amount);
    }
    
    /**
     * Получить первоначальный платеж прописью
     */
    public function getInitialPaymentWordsAttribute(): string
    {
        return $this->num2str($this->initial_payment);
    }
    
    /**
     * Получить общую сумму прописью
     */
    public function getTotalAmountWordsAttribute(): string
    {
        return $this->num2str($this->total_amount);
    }
    
    /**
     * Преобразование числа в строку (сумма прописью)
     */
    private function num2str($num): string
    {
        $nul = 'ноль';
        $ten = [
            ['', 'один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'],
            ['', 'одна', 'две', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'],
        ];
        $a20 = [
            'десять', 'одиннадцать', 'двенадцать', 'тринадцать', 'четырнадцать', 
            'пятнадцать', 'шестнадцать', 'семнадцать', 'восемнадцать', 'девятнадцать'
        ];
        $tens = [
            2 => 'двадцать', 'тридцать', 'сорок', 'пятьдесят', 
            'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто'
        ];
        $hundred = [
            '', 'сто', 'двести', 'триста', 'четыреста', 
            'пятьсот', 'шестьсот', 'семьсот', 'восемьсот', 'девятьсот'
        ];
        $unit = [
            ['копейка', 'копейки', 'копеек', 1],
            ['рубль', 'рубля', 'рублей', 0],
            ['тысяча', 'тысячи', 'тысяч', 1],
            ['миллион', 'миллиона', 'миллионов', 0],
            ['миллиард', 'миллиарда', 'миллиардов', 0],
        ];
        
        list($rub, $kop) = explode('.', sprintf("%015.2f", floatval($num)));
        $out = [];
        
        if (intval($rub) > 0) {
            foreach (str_split($rub, 3) as $uk => $v) {
                if (!intval($v)) {
                    continue;
                }
                $uk = count($unit) - $uk - 1;
                $gender = $unit[$uk][3];
                list($i1, $i2, $i3) = array_map('intval', str_split($v, 1));
                
                // сотни
                if ($i1 > 0) {
                    $out[] = $hundred[$i1];
                }
                
                // десятки
                if ($i2 > 1) {
                    $out[] = $tens[$i2] . ' ' . $ten[$gender][$i3];
                } else {
                    $num = $i2 * 10 + $i3;
                    if ($num > 0) {
                        $out[] = $num < 10 ? $ten[$gender][$num] : $a20[$num - 10];
                    }
                }
                
                // единицы с учетом рода
                if ($uk > 1) {
                    if ($i2 == 1) {
                        $out[] = $unit[$uk][0];
                    } else {
                        $out[] = $this->morph($v, $unit[$uk][0], $unit[$uk][1], $unit[$uk][2]);
                    }
                }
            }
        } else {
            $out[] = $nul;
        }
        
        // добавляем "рублей"
        $out[] = $this->morph(intval($rub), $unit[1][0], $unit[1][1], $unit[1][2]);
        
        // добавляем копейки
        $out[] = $kop . ' ' . $this->morph($kop, $unit[0][0], $unit[0][1], $unit[0][2]);
        
        return trim(preg_replace('/ {2,}/', ' ', implode(' ', $out)));
    }
    
    /**
     * Склонение слова по числу
     */
    private function morph($n, $f1, $f2, $f5)
    {
        $n = abs(intval($n)) % 100;
        if ($n > 10 && $n < 20) {
            return $f5;
        }
        $n = $n % 10;
        if ($n > 1 && $n < 5) {
            return $f2;
        }
        if ($n == 1) {
            return $f1;
        }
        return $f5;
    }

// app/Models/Deal.php



// В классе Deal добавьте/измените методы:

/**
 * Получить прогресс оплаты всей сделки (сколько уже оплачено)
 */
public function getTotalPaymentProgressAttribute(): float
{
    if ($this->total_amount <= 0) {
        return 0;
    }

    return ($this->total_paid / $this->total_amount) * 100;
}

/**
 * Получить прогресс до следующего платежа в процентах (время до оплаты)
 */
public function getNextPaymentProgressAttribute(): float
{
    $daysToPayment = $this->days_to_next_payment;
    
    // Если нет данных о следующем платеже
    if ($daysToPayment === null) {
        return 100;
    }
    
    // Определяем лимит дней в зависимости от периода
    $limitDays = $this->getPaymentPeriodLimitDays();
    
    // Если просрочка
    if ($daysToPayment < 0) {
        return 0;
    }
    
    // Рассчитываем прогресс (0-100%)
    $progress = ($daysToPayment / $limitDays) * 100;
    
    // Ограничиваем от 0 до 100
    return max(0, min(100, $progress));
}

/**
 * Получить лимит дней для периода платежа
 */
private function getPaymentPeriodLimitDays(): int
{
    return match($this->payment_period) {
        self::PERIOD_DAY => 1,    // Суточный - 24 часа
        self::PERIOD_WEEK => 7,   // Недельный - 7 дней
        self::PERIOD_MONTH => 30, // Месячный - примерно 30 дней
        default => 30,
    };
}

/**
 * Получить цвет для полоски времени до платежа
 */
public function getTimeProgressColorAttribute(): string
{
    $progress = $this->next_payment_progress;
    
    // Определяем цвет в зависимости от прогресса
    if ($progress >= 70) return 'success';    // Зеленый - много времени
    if ($progress >= 30) return 'warning';    // Желтый - средний срок
    return 'danger';                          // Красный - мало времени/просрочка
}

/**
 * Получить CSS-градиент для полоски времени
 */
public function getTimeProgressGradientAttribute(): string
{
    $progress = $this->next_payment_progress;
    
    if ($progress >= 70) {
        // В основном зеленый
        return 'linear-gradient(90deg, #dc3545 0%, #ffc107 30%, #28a745 70%)';
    } elseif ($progress >= 30) {
        // Смешанный
        return 'linear-gradient(90deg, #dc3545 0%, #ffc107 50%, #28a745 100%)';
    } else {
        // В основном красный
        return 'linear-gradient(90deg, #dc3545 0%, #ffc107 70%, #28a745 100%)';
    }
}

/**
 * Получить текст подсказки для полоски времени
 */
public function getTimeProgressTooltipAttribute(): string
{
    if (!$this->next_payment_due_date) {
        return 'Нет данных о следующем платеже';
    }
    
    $days = $this->days_to_next_payment;
    $period = $this->payment_period_text;
    $date = $this->next_payment_due_date->format('d.m.Y');
    $progress = round($this->next_payment_progress, 1);
    
    if ($days < 0) {
        return "Просрочено на " . abs($days) . " дн. ({$date})";
    } elseif ($days === 0) {
        return "Оплатить сегодня! Прогресс: {$progress}%";
    } else {
        return "Осталось {$days} дн. до оплаты ({$date}). Прогресс: {$progress}%";
    }
}

/**
 * Получить HTML для отображения прогресса времени
 */
public function getTimeProgressHtmlAttribute(): string
{
    if (!$this->next_payment_due_date) {
        return '<span class="text-muted">Нет данных</span>';
    }
    
    $progress = $this->next_payment_progress;
    $days = $this->days_to_next_payment;
    
    $html = '<div class="progress time-progress" style="height: 12px;" ';
    $html .= 'data-bs-toggle="tooltip" title="'.$this->time_progress_tooltip.'">';
    
    if ($this->payment_period === self::PERIOD_DAY) {
        // Суточный - показываем часы
        $hours = $days * 24;
        $hoursRemaining = max(0, $hours);
        $html .= '<div class="progress-bar" style="width: '.$progress.'%; background: '.$this->time_progress_gradient.';">';
        $html .= '<small class="fw-bold">'.$hoursRemaining.'ч</small>';
    } elseif ($this->payment_period === self::PERIOD_WEEK) {
        // Недельный
        $html .= '<div class="progress-bar" style="width: '.$progress.'%; background: '.$this->time_progress_gradient.';">';
        $html .= '<small class="fw-bold">'.$days.'д</small>';
    } else {
        // Месячный
        $html .= '<div class="progress-bar" style="width: '.$progress.'%; background: '.$this->time_progress_gradient.';">';
        $html .= '<small class="fw-bold">'.$days.'д</small>';
    }
    
    $html .= '</div></div>';
    
    return $html;
}







    // Связи
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(DealPayment::class)->orderBy('due_date');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(DealNotification::class);
    }

    // Вспомогательные методы
    public function getDealTypeTextAttribute(): string
    {
        return self::getDealTypes()[$this->deal_type] ?? 'Неизвестно';
    }

    public function getStatusTextAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? 'Неизвестно';
    }

    public function getPaymentPeriodTextAttribute(): string
    {
        return self::getPaymentPeriods()[$this->payment_period] ?? 'Неизвестно';
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return null;
        }

        return Carbon::parse($this->end_date)->diffInDays(Carbon::now(), false) * -1;
    }

public function getTotalPaidAttribute(): float
{
    // Только оплаченные платежи (статус 'paid')
    // Первоначальный взнос НЕ учитывается автоматически!
    return (float) $this->payments()->where('status', 'paid')->sum('amount');
}

    public function getRemainingAmountAttribute(): float
    {
        return $this->total_amount - $this->total_paid;
    }

    public function getNextPaymentAttribute(): ?DealPayment
    {
        return $this->payments()
            ->where('status', 'pending')
            ->where('due_date', '>=', Carbon::today())
            ->orderBy('due_date')
            ->first();
    }

    public function getNextPaymentDueDateAttribute(): ?Carbon
    {
        return $this->next_payment?->due_date;
    }

    public function getDaysToNextPaymentAttribute(): ?int
    {
        if (!$this->next_payment_due_date) {
            return null;
        }

        return Carbon::parse($this->next_payment_due_date)->diffInDays(Carbon::now(), false) * -1;
    }

    public function getPaymentProgressAttribute(): float
    {
        if ($this->total_amount <= 0) {
            return 0;
        }

        return ($this->total_paid / $this->total_amount) * 100;
    }

    public function isOverdue(): bool
    {
        return $this->payments()->where('status', 'pending')
            ->where('due_date', '<', Carbon::today())
            ->exists();
    }

public function updateStatus(): void
{
    \Log::info("=== Начало updateStatus() для сделки {$this->id} ===");
    
    if ($this->status === self::STATUS_CANCELLED || $this->status === self::STATUS_COMPLETED) {
        \Log::info("Сделка уже отменена или завершена, пропускаем");
        return;
    }

    // Используем точность до 2 знаков для сравнения
    $totalPaid = round($this->total_paid, 2);
    $totalAmount = round($this->total_amount, 2);
    
    // Проверяем с погрешностью до 5 копеек
    $isFullyPaid = abs($totalAmount - $totalPaid) <= 0.05;

    \Log::info("Всего оплачено: {$totalPaid}, Общая сумма: {$totalAmount}, Полностью оплачено: " . ($isFullyPaid ? 'ДА' : 'НЕТ'));

    $oldStatus = $this->status;
    
    if ($isFullyPaid) {
        $this->status = self::STATUS_COMPLETED;
        \Log::info("Установлен статус: completed");
    } elseif ($this->isOverdue()) {
        $this->status = self::STATUS_OVERDUE;
        \Log::info("Установлен статус: overdue");
    } elseif ($this->contract_signed_date) {
        $this->status = self::STATUS_ACTIVE;
        \Log::info("Установлен статус: active");
    } else {
        $this->status = self::STATUS_DRAFT;
        \Log::info("Установлен статус: draft");
    }

    $this->save();
    \Log::info("Сделка сохранена со статусом: {$this->status}");
    
    // ВАЖНО: Обновляем статусы клиента и автомобиля при любом изменении статуса сделки
    if ($oldStatus !== $this->status) {
        \Log::info("Статус сделки изменился ({$oldStatus} -> {$this->status}), обновляем клиента и автомобиль");
        
        // Обновляем статус клиента
        try {
            \Log::info("Обновляем статус клиента {$this->client_id}");
            $this->client->updateStatusBasedOnDeals();
            \Log::info("Статус клиента обновлен: {$this->client->status}");
        } catch (\Exception $e) {
            \Log::error("Ошибка обновления статуса клиента: " . $e->getMessage());
        }
        
        // Обновляем статус автомобиля
        try {
            \Log::info("Обновляем статус автомобиля {$this->car_id}");
            $this->car->updateStatusBasedOnDeals();
            \Log::info("Статус автомобиля обновлен: {$this->car->status}");
            
            // ДОПОЛНИТЕЛЬНО: если сделка завершена и это лизинг с выкупом, проверяем статус
            if ($this->status === self::STATUS_COMPLETED && $this->deal_type === self::TYPE_LEASE) {
                \Log::info("Сделка {$this->id} - завершенный лизинг с выкупом, проверяем статус автомобиля {$this->car_id}");
                $this->car->refresh();
                if ($this->car->status !== Car::STATUS_SOLD) {
                    \Log::warning("Автомобиль {$this->car_id} должен быть ПРОДАН после лизинга с выкупом, но имеет статус: {$this->car->status}");
                    $this->car->status = Car::STATUS_SOLD;
                    $this->car->save();
                    \Log::info("Статус автомобиля {$this->car_id} принудительно установлен как ПРОДАН");
                }
            }
        } catch (\Exception $e) {
            \Log::error("Ошибка обновления статуса автомобиля: " . $e->getMessage());
        }
    } else {
        \Log::info("Статус сделки не изменился, пропускаем обновление клиента и автомобиля");
    }
    
    \Log::info("=== Конец updateStatus() ===");
}

    // Генерация номера сделки
    public static function generateDealNumber(): string
    {
        $prefix = 'DL';
        $year = date('Y');
        $month = date('m');
        $lastDeal = self::where('deal_number', 'like', "{$prefix}{$year}{$month}%")
            ->orderBy('deal_number', 'desc')
            ->first();

        if ($lastDeal) {
            $lastNumber = (int) substr($lastDeal->deal_number, -4);
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return "{$prefix}{$year}{$month}{$nextNumber}";
    }
    
    public function scopeActive($query)
{
    return $query->whereIn('status', ['active', 'overdue']);
}

    
}