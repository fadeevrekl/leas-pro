<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Deal;
use App\Models\DealPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class InvestorController extends Controller
{
    /**
     * Главная страница - таблица автомобилей инвестора
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // Получаем автомобили инвестора с активной сделкой
        $cars = Car::where('investor_id', $user->id)
            ->with(['deals' => function($query) {
                // Берем последнюю активную сделку
                $query->whereIn('status', ['active', 'in_progress'])
                      ->orderBy('created_at', 'desc')
                      ->limit(1);
            }])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // ВАЖНО: Обновляем статусы всех автомобилей перед отображением
        foreach ($cars as $car) {
            try {
                // Это обновит статус на основе текущих сделок
                $car->updateStatusBasedOnDeals();
                // Обновляем объект, чтобы статус был актуальным
                $car->refresh();
            } catch (\Exception $e) {
                \Log::error("Ошибка обновления статуса автомобиля {$car->id} в кабинете инвестора: " . $e->getMessage());
            }
        }
        
        return view('investor.dashboard', compact('user', 'cars'));
    }
    
    /**
     * Раздел "Мои инвестиции" - статистика и графики
     */
    public function investments()
    {
        $user = Auth::user();
        
        // Получаем автомобили инвестора
        $cars = Car::where('investor_id', $user->id)
            ->with(['deals', 'deals.payments', 'deals.client'])
            ->get();
        
        // ВАЖНО: Обновляем статусы всех автомобилей перед отображением
        foreach ($cars as $car) {
            try {
                $car->updateStatusBasedOnDeals();
                $car->refresh();
            } catch (\Exception $e) {
                \Log::error("Ошибка обновления статуса автомобиля {$car->id}: " . $e->getMessage());
            }
        }
        
        $carIds = $cars->pluck('id');
        
        // Получаем все сделки с платежами
        $deals = Deal::whereIn('car_id', $carIds)
            ->with(['payments', 'client', 'car'])
            ->get();
        
        // Рассчитываем статистику
        $basicStats = $this->calculateInvestmentStats($deals, $user, $cars);
        $extendedStats = $this->calculateExtendedStats($deals, $user, $cars);
        
        // Объединяем статистику
        $stats = array_merge($basicStats, $extendedStats);
        
        return view('investor.investments', compact('user', 'stats'));
    }
    
    /**
     * Расчет статистики
     */
    private function calculateInvestmentStats($deals, $user, $cars)
    {
        $totalIncome = 0;
        $incomeByCar = [];
        $incomeByMonth = [];
        $incomeByYear = [];
        
        foreach ($deals as $deal) {
            $amount = $deal->total_amount ?? 0;
            $date = $deal->created_at ?? now();
            
            $totalIncome += $amount;
            
            // По автомобилю
            $carId = $deal->car_id;
            $incomeByCar[$carId] = ($incomeByCar[$carId] ?? 0) + $amount;
            
            // По месяцу
            $month = $date->format('Y-m');
            $incomeByMonth[$month] = ($incomeByMonth[$month] ?? 0) + $amount;
            
            // По году
            $year = $date->format('Y');
            $incomeByYear[$year] = ($incomeByYear[$year] ?? 0) + $amount;
        }
        
        // Сортируем
        ksort($incomeByMonth);
        ksort($incomeByYear);
        
        $netIncome = $totalIncome * (1 - ($user->commission_percent / 100));
        
        return [
            'total_income' => $totalIncome,
            'net_income' => $netIncome,
            'commission' => $totalIncome - $netIncome,
            'income_by_car' => $incomeByCar,
            'income_by_month' => $incomeByMonth,
            'income_by_year' => $incomeByYear,
            'cars' => $cars->keyBy('id'),
            'total_cars' => $cars->count(),
            'active_deals' => $deals->whereIn('status', ['active', 'in_progress'])->count(),
        ];
    }
    
    /**
     * Просмотр автомобиля (опционально)
     */
    public function showCar(Car $car)
    {
        if ($car->investor_id !== Auth::id()) {
            abort(403);
        }
        
        // ВАЖНО: Обновляем статус автомобиля перед отображением
        try {
            $car->updateStatusBasedOnDeals();
            $car->refresh();
        } catch (\Exception $e) {
            \Log::error("Ошибка обновления статуса автомобиля {$car->id}: " . $e->getMessage());
        }
        
        $deals = Deal::where('car_id', $car->id)->get();
        
        return view('investor.car', compact('car', 'deals'));
    }
    
    /**
     * Расчет расширенной статистики с прогнозами
     */
    private function calculateExtendedStats($deals, $user, $cars)
    {
        $basicStats = $this->calculateInvestmentStats($deals, $user, $cars);
        
        // Получаем активные сделки
        $activeDeals = $deals->where('status', Deal::STATUS_ACTIVE);
        
        // 1. Расчет текущей загрузки парка
        $carsInUse = $activeDeals->pluck('car_id')->unique()->count();
        $totalCars = $cars->count();
        $utilizationRate = $totalCars > 0 ? ($carsInUse / $totalCars) * 100 : 0;
        
        // 2. Прогноз выкупа (ближайшие 12 месяцев)
        $buyoutForecast = $this->calculateBuyoutForecast($activeDeals);
        
// 3. Прогноз доходов на 12 месяцев
$incomeForecastData = $this->calculateIncomeForecast($activeDeals, $buyoutForecast);
$incomeForecast = $incomeForecastData['simple']; // Берем только простой массив для обратной совместимости
        
        // 4. Расчет ключевых метрик
        $metrics = $this->calculateKeyMetrics($cars, $activeDeals, $basicStats);
        
        // 5. Рекомендации
        $recommendations = $this->generateRecommendations($utilizationRate, $buyoutForecast, $totalCars);
        
        // ВОТ ИСПРАВЛЕНИЕ: убрали $reserveMetrics из этого метода
        return array_merge($basicStats, [
            'extended_metrics' => [
                'utilization_rate' => round($utilizationRate, 1),
                'cars_in_use' => $carsInUse,
                'cars_available' => $totalCars - $carsInUse,
                'cars_in_maintenance' => $cars->where('status', Car::STATUS_MAINTENANCE)->count(),
          'avg_monthly_income_per_car' => $totalCars > 0 ? 
    ((float)$basicStats['total_income'] / max($totalCars, 1)) : 0,
            ],
            'buyout_forecast' => $buyoutForecast,
            'income_forecast' => $incomeForecast,
            'recommendations' => $recommendations,
            'metrics' => $metrics,
            'monthly_income_data' => $this->prepareMonthlyIncomeData($basicStats['income_by_month']),
        ]);
    }
    
 /**
 * Прогноз выкупа автомобилей (только для лизинга с выкупом)
 */
private function calculateBuyoutForecast($activeDeals)
{
    $forecast = [];
    $rentalReturns = []; // Для аренды - возвраты автомобилей
    
    foreach ($activeDeals as $deal) {
        // Грубый расчет оставшихся платежей
        $remainingPayments = $deal->payments()
            ->where('status', DealPayment::STATUS_PENDING)
            ->count();
        
        // Только для сделок, которые заканчиваются в ближайшие 12 месяцев
        if ($remainingPayments > 0 && $remainingPayments <= 12) {
            $item = [
                'deal_id' => $deal->id,
                'deal_number' => $deal->deal_number,
                'car_id' => $deal->car_id,
                'car_name' => $deal->car ? "{$deal->car->brand} {$deal->car->model}" : 'Неизвестно',
                'car_plate' => $deal->car->license_plate ?? null,
                'deal_type' => $deal->deal_type,
                'deal_type_text' => $deal->deal_type_text,
                'remaining_payments' => $remainingPayments,
                'months_to_buyout' => $remainingPayments,
                'expected_buyout_date' => Carbon::now()->addMonths($remainingPayments)->format('Y-m-d'),
                'monthly_payment' => $deal->payment_amount ?? 0,
                'expected_buyout_month' => Carbon::now()->addMonths($remainingPayments)->format('Y-m'),
            ];
            
            // РАЗДЕЛЯЕМ по типу сделки
            if ($deal->deal_type === Deal::TYPE_LEASE) {
                // Лизинг с выкупом - автомобиль уходит
                $forecast[] = $item;
            } else {
                // Простая аренда - автомобиль возвращается
                $rentalReturns[] = $item;
            }
        }
    }
    
    // Группируем по месяцам выкупа (только лизинг с выкупом)
    $groupedByMonth = [];
    foreach ($forecast as $item) {
        $month = $item['expected_buyout_month'];
        if (!isset($groupedByMonth[$month])) {
            $groupedByMonth[$month] = [];
        }
        $groupedByMonth[$month][] = $item;
    }
    
    // Группируем возвраты аренды
    $rentalReturnsByMonth = [];
    foreach ($rentalReturns as $item) {
        $month = $item['expected_buyout_month'];
        if (!isset($rentalReturnsByMonth[$month])) {
            $rentalReturnsByMonth[$month] = [];
        }
        $rentalReturnsByMonth[$month][] = $item;
    }
    
    ksort($groupedByMonth);
    ksort($rentalReturnsByMonth);
    
    return [
        'detailed' => $forecast,
        'grouped_by_month' => $groupedByMonth,
        'total_near_buyout' => count($forecast),
        'rental_returns_detailed' => $rentalReturns,
        'rental_returns_by_month' => $rentalReturnsByMonth,
        'total_rental_returns' => count($rentalReturns),
    ];
}
    
/**
 * Прогноз доходов на 12 месяцев
 */
private function calculateIncomeForecast($activeDeals, $buyoutForecast)
{
    // Разделяем сделки по типу
    $leaseDeals = $activeDeals->where('deal_type', Deal::TYPE_LEASE); // Лизинг с выкупом
    $rentalDeals = $activeDeals->where('deal_type', Deal::TYPE_RENTAL); // Простая аренда
    
    $currentMonthlyIncome = $activeDeals->sum('payment_amount');
    $forecast = [];
    $forecastDetails = []; // Детальная информация для отображения
    
    // Русские названия месяцев
    $russianMonths = [
        1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель',
        5 => 'Май', 6 => 'Июнь', 7 => 'Июль', 8 => 'Август',
        9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь'
    ];
    
    for ($i = 0; $i < 12; $i++) {
        $month = Carbon::now()->addMonths($i);
        $monthKey = $month->format('Y-m');
        $monthNumber = $month->month;
        $year = $month->year;
        
        // Используем русские названия
        $monthName = $russianMonths[$monthNumber] . ' ' . $year;
        
        // Количество ВЫКУПОВ (лизинг с выкупом) в этом месяце
        $buyoutsThisMonth = isset($buyoutForecast['grouped_by_month'][$monthKey]) 
            ? count($buyoutForecast['grouped_by_month'][$monthKey]) 
            : 0;
        
        // Количество ВОЗВРАТОВ (аренда) в этом месяце
        $returnsThisMonth = isset($buyoutForecast['rental_returns_by_month'][$monthKey]) 
            ? count($buyoutForecast['rental_returns_by_month'][$monthKey]) 
            : 0;
        
        // Потеря дохода от выкупов (автомобиль уходит НАВСЕГДА)
        $buyoutIncomeLoss = 0;
        if (isset($buyoutForecast['grouped_by_month'][$monthKey])) {
            foreach ($buyoutForecast['grouped_by_month'][$monthKey] as $deal) {
                $buyoutIncomeLoss += $deal['monthly_payment'];
            }
        }
        
        // Временная потеря дохода от возвратов аренды (автомобиль вернется)
        $rentalReturnIncomeLoss = 0;
        if (isset($buyoutForecast['rental_returns_by_month'][$monthKey])) {
            foreach ($buyoutForecast['rental_returns_by_month'][$monthKey] as $deal) {
                $rentalReturnIncomeLoss += $deal['monthly_payment'];
            }
        }
        
        // Общая потеря дохода в этом месяце
        $incomeLoss = $buyoutIncomeLoss + $rentalReturnIncomeLoss;
        
        $forecastedIncome = max(0, $currentMonthlyIncome - $incomeLoss);
        
        // Сохраняем детали для отображения
        $forecastDetails[$monthName] = [
            'income' => round($forecastedIncome, 2),
            'buyout_count' => $buyoutsThisMonth,
            'return_count' => $returnsThisMonth,
            'buyout_income_loss' => $buyoutIncomeLoss,
            'rental_income_loss' => $rentalReturnIncomeLoss,
            'total_income_loss' => $incomeLoss,
            'month_key' => $monthKey,
        ];
        
        // Для простого массива (обратная совместимость)
        $forecast[$monthName] = round($forecastedIncome, 2);
        
        // Уменьшаем текущий доход только на ВЫКУПЫ (аренда - временная потеря)
        $currentMonthlyIncome = max(0, $currentMonthlyIncome - $buyoutIncomeLoss);
    }
    
    // Возвращаем оба массива для разных нужд
    return [
        'simple' => $forecast,           // Для текущего использования
        'detailed' => $forecastDetails,  // Для новых отображений
    ];
}
    
    /**
     * Расчет ключевых метрик
     */
    private function calculateKeyMetrics($cars, $activeDeals, $basicStats)
    {
        // Средний срок аренды
        $avgDealDuration = 0;
        if ($activeDeals->count() > 0) {
            $totalMonths = 0;
            foreach ($activeDeals as $deal) {
                if ($deal->start_date && $deal->end_date) {
                    $months = $deal->start_date->diffInMonths($deal->end_date);
                    $totalMonths += $months;
                }
            }
            $avgDealDuration = round($totalMonths / $activeDeals->count(), 1);
        }
        
        // ROI (Return on Investment) - грубый расчет
        $totalInvestment = $cars->sum('price');
        $roi = $totalInvestment > 0 ? ($basicStats['net_income'] / $totalInvestment) * 100 : 0;
        
        // Payback Period (срок окупаемости)
        $avgMonthlyIncome = $basicStats['total_income'] > 0 ? 
            $basicStats['total_income'] / max($activeDeals->count(), 1) : 0;
        $paybackMonths = $avgMonthlyIncome > 0 ? round($totalInvestment / $avgMonthlyIncome) : 0;
        
        return [
            'avg_deal_duration' => $avgDealDuration,
            'roi_percentage' => round($roi, 1),
            'payback_months' => $paybackMonths,
            'total_investment' => $totalInvestment,
            'avg_monthly_income' => round($avgMonthlyIncome),
        ];
    }
    
    /**
     * Генерация рекомендаций
     */
    private function generateRecommendations($utilizationRate, $buyoutForecast, $totalCars)
    {
        $recommendations = [];
        
        // Проверка загрузки
        if ($utilizationRate > 85) {
            $recommendations[] = [
                'type' => 'warning',
                'icon' => 'bi-exclamation-triangle',
                'title' => 'Высокая загрузка парка',
                'message' => 'Загрузка парка составляет ' . round($utilizationRate) . '%. Рекомендуется докупить автомобили.',
                'action' => 'Рассмотреть покупку 1-2 дополнительных автомобилей',
                'priority' => 1,
            ];
        } elseif ($utilizationRate < 50) {
            $recommendations[] = [
                'type' => 'info',
                'icon' => 'bi-info-circle',
                'title' => 'Низкая загрузка парка',
                'message' => 'Загрузка парка составляет ' . round($utilizationRate) . '%. Возможно, стоит уменьшить количество автомобилей.',
                'action' => 'Проанализировать эффективность каждого автомобиля',
                'priority' => 3,
            ];
        }
        
        // Проверка выкупа
        $buyoutPercentage = $totalCars > 0 ? 
            ($buyoutForecast['total_near_buyout'] / $totalCars) * 100 : 0;
        
        if ($buyoutPercentage > 30) {
            $recommendations[] = [
                'type' => 'danger',
                'icon' => 'bi-clock',
                'title' => 'Массовый выкуп автомобилей',
                'message' => 'В ближайшие 12 месяцев будет выкуплено ' . 
                    $buyoutForecast['total_near_buyout'] . ' авто (' . round($buyoutPercentage) . '% парка).',
                'action' => 'Запланировать покупку ' . ceil($buyoutForecast['total_near_buyout'] * 0.5) . ' автомобилей',
                'timeline' => '3-4 месяца',
                'priority' => 1,
            ];
        }
        
        
        // 3. Рекомендации по типам сделок
    $totalEndingDeals = $buyoutForecast['total_near_buyout'] + $buyoutForecast['total_rental_returns'];
    $leasePercentage = $totalCars > 0 ? 
        ($buyoutForecast['total_near_buyout'] / $totalCars) * 100 : 0;
    $rentalPercentage = $totalCars > 0 ? 
        ($buyoutForecast['total_rental_returns'] / $totalCars) * 100 : 0;
    
    // Рекомендация по лизингу с выкупом (требуют замены)
    if ($leasePercentage > 20) {
        $recommendations[] = [
            'type' => 'danger',
            'icon' => 'bi-car-front',
            'title' => 'Массовый выкуп автомобилей',
            'message' => 'В ближайшие 12 месяцев будет выкуплено ' . 
                $buyoutForecast['total_near_buyout'] . ' авто (' . round($leasePercentage) . '% парка) по договорам лизинга с выкупом.',
            'action' => 'Запланировать покупку ' . ceil($buyoutForecast['total_near_buyout'] * 0.8) . ' автомобилей',
            'timeline' => '3-4 месяца до выкупа',
            'priority' => 1,
        ];
    }
    
    // Рекомендация по аренде (возвраты - временная потеря)
    if ($rentalPercentage > 30) {
        $recommendations[] = [
            'type' => 'warning',
            'icon' => 'bi-arrow-repeat',
            'title' => 'Массовые возвраты аренды',
            'message' => 'В ближайшие 12 месяцев завершится ' . 
                $buyoutForecast['total_rental_returns'] . ' договоров аренды (' . round($rentalPercentage) . '% парка). Автомобили вернутся.',
            'action' => 'Заранее искать новых клиентов для ' . ceil($buyoutForecast['total_rental_returns'] * 0.6) . ' возвращаемых авто',
            'timeline' => '1-2 месяца до возврата',
            'priority' => 2,
        ];
    }
        
        
        // Сортируем по приоритету
        usort($recommendations, function($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });
        
        return $recommendations;
    }
    
/**
 * Подготовка данных для графика месячного дохода
 */
/**
 * Подготовка данных для графика месячного дохода
 */
private function prepareMonthlyIncomeData($incomeByMonth)
{
    if (empty($incomeByMonth)) {
        return [
            'labels' => [],
            'data' => [],
            'month_keys' => [], // Добавляем ключи месяцев
        ];
    }
    
    $labels = [];
    $data = [];
    $monthKeys = []; // Массив для хранения Y-m формата
    
    // Русские названия месяцев (сокращенные)
    $russianMonthsShort = [
        1 => 'янв', 2 => 'фев', 3 => 'мар', 4 => 'апр',
        5 => 'май', 6 => 'июн', 7 => 'июл', 8 => 'авг',
        9 => 'сен', 10 => 'окт', 11 => 'ноя', 12 => 'дек'
    ];
    
    // Берем последние 12 месяцев
    $last12Months = [];
    for ($i = 11; $i >= 0; $i--) {
        $month = Carbon::now()->subMonths($i)->format('Y-m');
        $last12Months[$month] = $incomeByMonth[$month] ?? 0;
    }
    
    foreach ($last12Months as $month => $income) {
        // Безопасный парсинг даты
        try {
            $date = Carbon::createFromFormat('Y-m-d', $month . '-01');
            if (!$date) {
                $date = Carbon::now();
            }
        } catch (\Exception $e) {
            $date = Carbon::now();
        }
        
        $monthNumber = $date->month;
        $year = $date->year;
        
        // Сокращенное русское название для отображения
        $labels[] = ucfirst($russianMonthsShort[$monthNumber]) . ' ' . $year;
        
        // Ключ месяца в формате Y-m для обработки в шаблоне
        $monthKeys[] = $date->format('Y-m');
        
        $data[] = $income;
    }
    
    return [
        'labels' => $labels,    // Для отображения: "янв 2024"
        'data' => $data,
        'month_keys' => $monthKeys, // Для обработки: "2024-01"
    ];
}
    
    /**
     * Расширенная аналитика инвестиций
     */
    public function investmentsAdvanced()
    {
        $user = Auth::user();
        
        // Получаем автомобили инвестора
        $cars = Car::where('investor_id', $user->id)
            ->with(['deals', 'deals.payments', 'deals.client'])
            ->get();
        
        // ВАЖНО: Обновляем статусы всех автомобилей перед отображением
        foreach ($cars as $car) {
            try {
                $car->updateStatusBasedOnDeals();
                $car->refresh();
            } catch (\Exception $e) {
                \Log::error("Ошибка обновления статуса автомобиля {$car->id}: " . $e->getMessage());
            }
        }
        
        $carIds = $cars->pluck('id');
        
        // Получаем все сделки с платежами
        $deals = Deal::whereIn('car_id', $carIds)
            ->with(['payments', 'client', 'car'])
            ->get();
        
        // Рассчитываем расширенную статистику
        $stats = $this->calculateAdvancedStats($deals, $user, $cars);
        
        return view('investor.investments_advanced', compact('user', 'stats'));
    }
    
    /**
     * Расчет продвинутой статистики
     */
    private function calculateAdvancedStats($deals, $user, $cars)
    {
        $basicStats = $this->calculateInvestmentStats($deals, $user, $cars);
        
        // Получаем активные сделки
        $activeDeals = $deals->where('status', Deal::STATUS_ACTIVE);
        
        // 1. Расчет текущей загрузки парка
        $carsInUse = $activeDeals->pluck('car_id')->unique()->count();
        $totalCars = $cars->count();
        $utilizationRate = $totalCars > 0 ? ($carsInUse / $totalCars) * 100 : 0;
        
        // 2. Прогноз выкупа (ближайшие 12 месяцев)
        $buyoutForecast = $this->calculateBuyoutForecast($activeDeals);
        
  // 3. Прогноз доходов на 12 месяцев
$incomeForecastData = $this->calculateIncomeForecast($activeDeals, $buyoutForecast);
$incomeForecast = $incomeForecastData['simple']; // Используем простой массив для совместимости
        
        // 4. Расчет резерва на выкуп
        $reserveMetrics = $this->calculateReserveMetrics($buyoutForecast, $cars);
        
        // 5. Расчет ключевых метрик
        $metrics = $this->calculateKeyMetrics($cars, $activeDeals, $basicStats);
        
        // 6. Рекомендации
        $recommendations = $this->generateRecommendations($utilizationRate, $buyoutForecast, $totalCars);
        
        // 7. Данные для графиков
        $monthlyIncomeData = $this->prepareMonthlyIncomeData($basicStats['income_by_month']);
        
        // 8. Распределение по брендам
        $brandDistribution = $this->calculateBrandDistribution($cars, $basicStats);
        
        // 9. Эффективность по автомобилям
        $carEfficiency = $this->calculateCarEfficiency($cars, $basicStats);
        
        return array_merge($basicStats, [
            'extended_metrics' => [
                'utilization_rate' => round($utilizationRate, 1),
                'cars_in_use' => $carsInUse,
                'cars_available' => $totalCars - $carsInUse,
                'cars_in_maintenance' => $cars->where('status', Car::STATUS_MAINTENANCE)->count(),
                'cars_sold' => $cars->where('status', Car::STATUS_SOLD)->count(),
                'avg_monthly_income_per_car' => $totalCars > 0 ? 
                    ($basicStats['total_income'] / max($totalCars, 1)) : 0,
            ],
            'reserve_metrics' => $reserveMetrics,
            'buyout_forecast' => $buyoutForecast,
            'income_forecast' => $incomeForecastData,
            'recommendations' => $recommendations,
            'metrics' => $metrics,
            'monthly_income_data' => $monthlyIncomeData,
            'brand_distribution' => $brandDistribution,
            'car_efficiency' => $carEfficiency,
            'heatmap_data' => $this->prepareHeatmapData($buyoutForecast),
        ]);
    }
    
    /**
     * Распределение доходов по брендам
     */
    private function calculateBrandDistribution($cars, $basicStats)
    {
        $distribution = [];
        
        foreach ($cars as $car) {
            $brand = $car->brand ?? 'Неизвестно';
            $carIncome = $basicStats['income_by_car'][$car->id] ?? 0;
            
            if (!isset($distribution[$brand])) {
                $distribution[$brand] = [
                    'count' => 0,
                    'income' => 0,
                    'avg_income' => 0,
                ];
            }
            
            $distribution[$brand]['count']++;
            $distribution[$brand]['income'] += $carIncome;
        }
        
        // Рассчитываем средний доход
        foreach ($distribution as $brand => &$data) {
            $data['avg_income'] = $data['count'] > 0 ? $data['income'] / $data['count'] : 0;
        }
        
        // Сортируем по доходу
        uasort($distribution, function($a, $b) {
            return $b['income'] <=> $a['income'];
        });
        
        return $distribution;
    }
    
    /**
     * Эффективность автомобилей
     */
    private function calculateCarEfficiency($cars, $basicStats)
    {
        $efficiency = [];
        
        foreach ($cars as $car) {
            $carIncome = $basicStats['income_by_car'][$car->id] ?? 0;
            $carPrice = $car->price ?? 1; // Чтобы избежать деления на 0
            
            // ROI для конкретного авто
            $roi = $carPrice > 0 ? ($carIncome / $carPrice) * 100 : 0;
            
            // Эффективность (доход на рубль инвестиций)
            $efficiencyScore = $carPrice > 0 ? $carIncome / $carPrice : 0;
            
            $efficiency[] = [
                'car' => $car,
                'income' => $carIncome,
                'price' => $carPrice,
                'roi' => round($roi, 1),
                'efficiency_score' => round($efficiencyScore, 2),
                'deal_count' => $car->deal_count ?? 0,
                'status' => $car->status,
            ];
        }
        
        // Сортируем по эффективности
        usort($efficiency, function($a, $b) {
            return $b['efficiency_score'] <=> $a['efficiency_score'];
        });
        
        return $efficiency;
    }
    
    /**
     * Подготовка данных для тепловой карты
     */
    private function prepareHeatmapData($buyoutForecast)
    {
        if (empty($buyoutForecast['detailed'])) {
            return [];
        }
        
        $heatmap = [];
        
        // Русские названия месяцев
        $russianMonths = [
            1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель',
            5 => 'Май', 6 => 'Июнь', 7 => 'Июль', 8 => 'Август',
            9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь'
        ];
        
     for ($i = 0; $i < 12; $i++) {
    $month = Carbon::now()->addMonths($i);
    $monthKey = $month->format('Y-m');
    $monthNumber = $month->month;
    $year = $month->year;
    
    // Используем русские названия
    $monthName = $russianMonths[$monthNumber] . ' ' . $year;
    
    $count = isset($buyoutForecast['grouped_by_month'][$monthKey]) 
        ? count($buyoutForecast['grouped_by_month'][$monthKey]) 
        : 0;
    
    // Определяем цвет в зависимости от количества
    $color = 'success'; // 0
    if ($count >= 3) $color = 'danger'; // 3+
    elseif ($count >= 2) $color = 'warning'; // 2
    elseif ($count >= 1) $color = 'info'; // 1
    
    $heatmap[] = [
        'month' => $monthKey,
        'month_name' => $monthName,
        'count' => $count,
        'color' => $color,
        'cars' => isset($buyoutForecast['grouped_by_month'][$monthKey]) 
            ? $buyoutForecast['grouped_by_month'][$monthKey] 
            : [],
    ];
}
        
        return $heatmap;
    }
    
    /**
     * Расчет метрик резерва на выкуп
     */
    private function calculateReserveMetrics($buyoutForecast, $cars)
    {
        $totalCars = $cars->count();
        
        if ($totalCars === 0 || empty($buyoutForecast['grouped_by_month'])) {
            return [
                'reserve_months' => 12,
                'reserve_days' => 365,
                'needed_cars' => 0,
                'critical_months_count' => 0,
                'next_critical_date' => null,
                'status' => 'no_data',
            ];
        }
        
        // 1. Определяем критические месяцы (когда выкупается >20% парка)
        $criticalMonths = [];
        $currentMonth = Carbon::now()->format('Y-m');
        
        foreach ($buyoutForecast['grouped_by_month'] as $month => $monthData) {
            $buyoutCount = is_array($monthData) ? count($monthData) : 0;
            $percentage = $totalCars > 0 ? ($buyoutCount / $totalCars) * 100 : 0;
            
            if ($percentage >= 20 && $month >= $currentMonth) {
                $criticalMonths[$month] = [
                    'count' => $buyoutCount,
                    'percentage' => round($percentage, 1),
                    'cars' => $monthData
                ];
            }
        }
        
        // 2. Расчет запаса в месяцах до первого критического месяца
        $reserveMonths = 12;
        $nextCriticalDate = null;
        
        if (!empty($criticalMonths)) {
            ksort($criticalMonths);
            $firstCriticalMonth = array_key_first($criticalMonths);
            $nextCriticalDate = $firstCriticalMonth;
            
            $currentDate = Carbon::now();
            // ВАЖНО: Исправляем парсинг даты
            try {
                $criticalDate = Carbon::createFromFormat('Y-m', $firstCriticalMonth);
                if ($criticalDate) {
                    $criticalDate->day(1); // Устанавливаем первое число месяца
                    $reserveMonths = max(0, $currentDate->diffInMonths($criticalDate, false));
                }
            } catch (\Exception $e) {
                \Log::error("Ошибка парсинга даты: {$firstCriticalMonth} - " . $e->getMessage());
            }
        }
        
        // 3. Расчет необходимого количества авто для покупки (на 3 месяца вперед)
        $neededCars = 0;
        $next3Months = array_slice($criticalMonths, 0, 3, true);
        
        foreach ($next3Months as $month => $data) {
            $expectedLoss = $data['count'];
            $acceptableLoss = ceil($totalCars * 0.1); // Допустимая потеря 10% парка в месяц
            $neededCars += max(0, $expectedLoss - $acceptableLoss);
        }
        
        // 4. Определяем статус
        $status = 'good';
        if ($reserveMonths < 1) {
            $status = 'critical';
        } elseif ($reserveMonths < 3) {
            $status = 'warning';
        }
        
        return [
            'reserve_months' => $reserveMonths,
            'reserve_days' => $reserveMonths * 30,
            'needed_cars' => $neededCars,
            'critical_months_count' => count($criticalMonths),
            'next_critical_date' => $nextCriticalDate,
            'status' => $status,
            'total_cars' => $totalCars,
        ];
    }
}