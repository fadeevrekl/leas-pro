@extends('layouts.app')

<!-- Подключаем стили для подсказок -->
<link href="{{ asset('css/investor-tooltips.css') }}?v={{ time() }}" rel="stylesheet">

@section('content')
<!-- Единая навигация -->
@include('investor.partials.navigation')

<!-- Информационная панель -->
<div class="alert alert-warning mb-4 position-relative">
    <i class="fas fa-info-circle info-icon" 
       data-bs-toggle="tooltip" data-bs-html="true"
       title="<div class='tooltip-content'>
               <strong>Интеллектуальный анализ</strong><br><br>
               Этот раздел использует алгоритмы для анализа вашего инвестиционного портфеля.<br><br>
               <strong>Особенности:</strong><br>
               • AI-рекомендации по оптимизации<br>
               • Прогнозы на 12 месяцев<br>
               • Тепловые карты выкупа<br>
               • Рейтинг эффективности авто<br><br>
               <em>Все данные обновляются в реальном времени</em>
              </div>">
    </i>
    <div class="d-flex">
        <div class="flex-shrink-0">
            <i class="bi bi-lightbulb fs-4"></i>
        </div>
        <div class="flex-grow-1 ms-3">
            <h3 class="alert-heading">Расширенный анализ портфеля</h3>
            <p class="mb-1">Интеллектуальный анализ инвестиций с AI-рекомендациями.</p>
            <p class="mb-0">Включает: тепловые карты выкупа, рейтинг эффективности, прогнозы на 12 месяцев.</p>
        </div>
    </div>
</div>

<!-- Блок 1: Панель быстрого доступа к ключевым метрикам -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-start border-primary border-1 card-tooltip">
            <i class="fas fa-info-circle info-icon" 
               data-bs-toggle="tooltip" data-bs-html="true"
               title="<div class='tooltip-content'>
                       <strong>Загрузка парка</strong><br><br>
                       Процент автомобилей, находящихся в активных сделках.<br><br>
                       <em>Оптимальный диапазон:</em> 70-85%<br><br>
                       <strong>Высокая загрузка (>85%)</strong>: требуется докупка авто<br>
                       <strong>Низкая загрузка (<50%)</strong>: стоит сократить парк<br><br>
                       <em>Расчет:</em> (Авто в сделке / Всего авто) × 100%
                      </div>">
            </i>
            <div class="card-body pt-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="text-muted mb-1">Загрузка парка</h3>
                        <h3 class="mb-0 {{ $stats['extended_metrics']['utilization_rate'] > 85 ? 'text-danger' : 'text-success' }}">
                            {{ $stats['extended_metrics']['utilization_rate'] }}%
                        </h3>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-primary rounded-circle">
                            <i class="fas fa-tachometer-alt2"></i>
                        </span>
                    </div>
                </div>
                <div class="progress mt-3" style="height: 6px;">
                    <div class="progress-bar {{ $stats['extended_metrics']['utilization_rate'] > 85 ? 'bg-danger' : 'bg-success' }}" 
                         style="width: {{ $stats['extended_metrics']['utilization_rate'] }}%"></div>
                </div>
                <small class="text-muted">
                    {{ $stats['extended_metrics']['cars_in_use'] }} из {{ $stats['total_cars'] }} авто в работе
                </small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-start border-success border-1 card-tooltip">
            <i class="fas fa-info-circle info-icon" 
               data-bs-toggle="tooltip" data-bs-html="true"
               title="<div class='tooltip-content'>
                       <strong>ROI (Return on Investment)</strong><br><br>
                       Доходность инвестиций в процентах.<br><br>
                       Показывает, сколько процентов от вложенных средств вы заработали.<br><br>
                       <em>Интерпретация:</em><br>
                       ✅ <strong>ROI > 20%</strong>: отличная доходность<br>
                       ⚠️ <strong>ROI 10-20%</strong>: хорошая доходность<br>
                       🔴 <strong>ROI < 10%</strong>: требует оптимизации<br><br>
                       <em>Расчет:</em> (Чистая прибыль / Инвестиции) × 100%
                      </div>">
            </i>
            <div class="card-body pt-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="text-muted mb-1">ROI</h3>
                        <h3 class="mb-0 text-success">{{ $stats['metrics']['roi_percentage'] }}%</h3>
                    </div>
                   
                </div>
                <p class="text-muted mt-3 mb-0">
                    Доходность инвестиций
                </p>
            </div>
        </div>
    </div>
    
<div class="col-md-3">
    <div class="card border-start border-{{ $stats['reserve_metrics']['status'] == 'critical' ? 'danger' : ($stats['reserve_metrics']['status'] == 'warning' ? 'warning' : 'success') }} border-1 card-tooltip">
        <i class="fas fa-info-circle info-icon" 
           data-bs-toggle="tooltip" data-bs-html="true"
           title="<div class='tooltip-content'>
                   <strong>Резерв на выкуп</strong><br><br>
                   Сколько месяцев осталось до начала дефицита автомобилей.<br><br>
                   <em>Как рассчитывается:</em><br>
                   • Анализ графика выкупа на 12 месяцев<br>
                   • Определение месяцев, когда выкупается >20% парка<br>
                   • Расчет времени до первого такого месяца<br><br>
                   <strong>Рекомендации:</strong><br>
                   🔴 <strong>< 1 месяца</strong>: СРОЧНО покупать авто<br>
                   🟡 <strong>1-3 месяца</strong>: Планировать покупку<br>
                   🟢 <strong>> 3 месяцев</strong>: Запас достаточный<br><br>
                   @if($stats['reserve_metrics']['needed_cars'] > 0)
                   <em>Требуется купить:</em> {{ $stats['reserve_metrics']['needed_cars'] }} авто
                   @else
                   <em>Дополнительные покупки не требуются</em>
                   @endif
                  </div>">
        </i>
        <div class="card-body pt-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="text-muted mb-1">Резерв на выкуп</h3>
                    @php
                        $reserveClass = $stats['reserve_metrics']['status'] == 'critical' ? 'text-danger' : 
                                       ($stats['reserve_metrics']['status'] == 'warning' ? 'text-warning' : 'text-success');
                    @endphp
                    <h3 class="mb-0 {{ $reserveClass }}">
                        {{ $stats['reserve_metrics']['reserve_months'] }} мес
                    </h3>
                </div>
       
            </div>
            <p class="text-muted mt-3 mb-0">
                @if($stats['reserve_metrics']['needed_cars'] > 0)
                Купить: {{ $stats['reserve_metrics']['needed_cars'] }} авто
                @else
                Запас достаточен
                @endif
            </p>
        </div>
    </div>
</div>
    
    <div class="col-md-3">
        <div class="card border-start border-info border-1 card-tooltip">
            <i class="fas fa-info-circle info-icon" 
               data-bs-toggle="tooltip" data-bs-html="true"
               title="<div class='tooltip-content'>
                       <strong>Средний срок аренды</strong><br><br>
                       Сколько месяцев в среднем длится сделка с одним клиентом.<br><br>
                       <em>Оптимальный диапазон:</em> 6-12 месяцев<br><br>
                       <strong>Менее 3 месяцев</strong>: высокий оборот, много работы с документами<br>
                       <strong>6-12 месяцев</strong>: оптимальный баланс<br>
                       <strong>Более 18 месяцев</strong>: стабильный доход, но выше риск выкупа<br><br>
                       Влияет на стабильность дохода и нагрузку на менеджеров
                      </div>">
            </i>
            <div class="card-body pt-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="text-muted mb-1">Средний срок</h3>
                        <h3 class="mb-0 text-info">{{ $stats['metrics']['avg_deal_duration'] }} мес</h3>
                    </div>
                    
                </div>
                <p class="text-muted mt-3 mb-0">
                    аренды на автомобиль
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Блок 2: Рекомендации системы -->
@if(!empty($stats['recommendations']))
<div class="row mb-4">
    <div class="col-12">
        <div class="card card-tooltip">
            <i class="fas fa-info-circle info-icon" 
               data-bs-toggle="tooltip" data-bs-html="true"
               title="<div class='tooltip-content'>
                       <strong>AI-рекомендации</strong><br><br>
                       Система анализирует ваш портфель и дает рекомендации по оптимизации.<br><br>
                       <strong>Приоритеты:</strong><br>
                       🔴 <strong>Высокий (1)</strong> - требуют немедленного внимания<br>
                       🟡 <strong>Средний (2)</strong> - планируйте в ближайшее время<br>
                       🔵 <strong>Низкий (3)</strong> - для долгосрочного планирования<br><br>
                       <em>Рекомендации обновляются</em> при изменении активных сделок и загрузки парка
                      </div>">
            </i>
            <div class="card-header bg-{{ $stats['recommendations'][0]['type'] ?? 'primary' }} text-white">
                <h3 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Рекомендации по оптимизации портфеля
                    <small class="float-end ms-2">AI Analysis</small>
                </h3>
            </div>
            <div class="card-body">
                @foreach($stats['recommendations'] as $recommendation)
                <div class="alert alert-{{ $recommendation['type'] }} alert-dismissible fade show mb-3">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="bi {{ $recommendation['icon'] }} fs-3"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="alert-heading">{{ $recommendation['title'] }}</h3>
                            <p>{{ $recommendation['message'] }}</p>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Рекомендуемое действие:</strong> {{ $recommendation['action'] }}
                                    @if(isset($recommendation['timeline']))
                                    <br><small class="text-muted">Рекомендуемый срок: {{ $recommendation['timeline'] }}</small>
                                    @endif
                                </div>
                                <div>
                                    <span class="badge bg-{{ $recommendation['type'] }}">
                                        Приоритет: {{ $recommendation['priority'] == 1 ? 'Высокий' : ($recommendation['priority'] == 2 ? 'Средний' : 'Низкий') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

<!-- Блок 3: Визуализация данных -->
<div class="row mb-4">
    <!-- График доходов -->
    <div class="col-md-8">
        <div class="card h-100 card-tooltip">
            <i class="fas fa-info-circle info-icon" 
               data-bs-toggle="tooltip" data-bs-html="true"
               title="<div class='tooltip-content'>
                       <strong>Динамика доходов</strong><br><br>
                       График показывает ваш ежемесячный доход за последние 12 месяцев.<br><br>
                       <strong>Как использовать:</strong><br>
                       • <strong>Наведите</strong> на точку графика - увидите точную сумму<br>
                       • <strong>Кликните</strong> на столбец - детальная информация за месяц<br>
                       • <strong>Сравните</strong> месяцы - выявите сезонность<br>
                       • <strong>Анализируйте</strong> тренд - рост или падение доходов<br><br>
                       Зеленый цвет = положительный доход
                      </div>">
            </i>
            <div class="card-header bg-primary-lt">
                <h3 class="mb-0">
                    <i class="fas fa-bar-chart me-1"></i>Динамика доходов
                    <small class="text-muted float-end ms-2">за последние 12 месяцев</small>
                </h3>
            </div>
            <div class="card-body">
                @if(!empty($stats['monthly_income_data']['labels']))
                <div style="height: 350px;">
                    <canvas id="incomeChart"></canvas>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="fas fa-chart-bar display-4 text-muted"></i>
                    <p class="text-muted mt-3">Нет данных для отображения графика</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Распределение по брендам -->
    <div class="col-md-4">
        <div class="card h-100 card-tooltip">
            <i class="fas fa-info-circle info-icon" 
               data-bs-toggle="tooltip" data-bs-html="true"
               title="<div class='tooltip-content'>
                       <strong>Доход по брендам</strong><br><br>
                       Круговая диаграмма показывает распределение дохода между марками автомобилей.<br><br>
                       <strong>Как использовать:</strong><br>
                       • <strong>Наведите</strong> на сектор - увидите точный процент и сумму<br>
                       • <strong>Кликните</strong> на сектор - изолируете бренд от остальных<br>
                       • <strong>Анализируйте</strong> - какие марки приносят больше всего дохода<br>
                       • <strong>Планируйте</strong> покупки - инвестируйте в прибыльные бренды<br><br>
                       Большие секторы = наиболее доходные бренды
                      </div>">
            </i>
            <div class="card-header bg-primary-lt">
                <h3 class="mb-0">
                    <i class="fas fa-chart-pie me-1"></i>Доход по брендам
                </h3>
            </div>
            <div class="card-body">
                @if(!empty($stats['brand_distribution']))
                <div style="height: 350px;">
                    <canvas id="brandChart"></canvas>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="fas fa-chart-pie display-4 text-muted"></i>
                    <p class="text-muted mt-3">Нет данных по брендам</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Блок 4: Тепловая карта выкупа -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card card-tooltip">
            <i class="fas fa-info-circle info-icon" 
               data-bs-toggle="tooltip" data-bs-html="true"
               title="<div class='tooltip-content'>
                       <strong>Тепловая карта выкупа</strong><br><br>
                       Показывает, в какие месяцы планируется выкуп автомобилей, чем ярче цвет, тем больше автомобилей запланировано к выкупу в указанную неделю.<br><br>
                       <strong>Цветовая шкала:</strong><br>
                       🟢 <strong>Зеленый</strong> - выкупов нет (0 авто)<br>
                       🔵 <strong>Синий</strong> - 1 авто на выкуп<br>
                       🟡 <strong>Желтый</strong> - 2 авто на выкуп<br>
                       🔴 <strong>Красный</strong> - 3+ авто на выкуп<br><br>
                       <strong>Как использовать:</strong><br>
                       • <strong>Планируйте покупки</strong> за 2-3 месяца до выкупа<br>
                       • <strong>Балансируйте парк</strong> - избегайте массового выкупа<br>
                       • <strong>Анализируйте сезонность</strong> - когда чаще выкупают<br><br>
                       Наведите на ячейку для деталей
                      </div>">
            </i>
            <div class="card-header bg-primary-lt">
                <h3 class="mb-0">
                    <i class="fas fa-calendar me-1"></i>Тепловая карта выкупа автомобилей
                    <small class="text-muted float-end ms-2">прогноз на 12 месяцев вперед</small>
                </h3>
            </div>
            <div class="card-body">
                @if(!empty($stats['heatmap_data']))
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Месяц</th>
                                @for($i = 1; $i <= 4; $i++)
                                <th class="text-center">Неделя {{ $i }}</th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stats['heatmap_data'] as $monthData)
                            <tr>
                                <td><strong>{{ $monthData['month_name'] }}</strong></td>
                                @for($week = 1; $week <= 4; $week++)
                                <td class="text-center align-middle" style="height: 60px;">
                                    @php
                                        // Распределяем автомобили по неделям (простая логика)
                                        $carsInWeek = [];
                                        if (!empty($monthData['cars'])) {
                                            $totalCars = count($monthData['cars']);
                                            $carsPerWeek = ceil($totalCars / 4);
                                            $start = ($week - 1) * $carsPerWeek;
                                            $carsInWeek = array_slice($monthData['cars'], $start, $carsPerWeek);
                                        }
                                        
                                        // Определяем цвет в зависимости от количества
                                        $weekCount = count($carsInWeek);
                                        $weekColor = 'success';
                                        if ($weekCount >= 3) $weekColor = 'danger';
                                        elseif ($weekCount >= 2) $weekColor = 'deal-overdue';
                                        elseif ($weekCount >= 1) $weekColor = 'deal-active';
                                        
                                    @endphp
                                    
                                    @if($weekCount > 0)
                                    <div class="d-flex flex-column">
                                        <span class="badge bg-{{ $weekColor }} mb-1">{{ $weekCount }} авто</span>
                                        <small class="text-muted">
                                            @foreach($carsInWeek as $car)
                                            <div>{{ $car['car_name'] }}</div>
                                            @endforeach
                                        </small>
                                    </div>
                                    @else
                                    <span class="text-muted">—</span>
                                    @endif
                                </td>
                                @endfor
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        <span class="badge bg-free me-2">0 авто</span>
                        <span class="badge bg-deal-active me-2">1 авто</span>
                        <span class="badge bg-warning me-2">2 авто</span>
                        <span class="badge bg-danger me-2">3+ авто</span>
                        — количество автомобилей, которые будут выкуплены
                    </small>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-calendar-check display-4 text-muted"></i>
                    <p class="text-muted mt-3">В ближайший год выкупов не планируется</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Блок 5: Эффективность автомобилей -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card card-tooltip">
            <i class="fas fa-info-circle info-icon" 
               data-bs-toggle="tooltip" data-bs-html="true"
               title="<div class='tooltip-content'>
                       <strong>Рейтинг эффективности автомобилей</strong><br><br>
                       Список автомобилей отсортирован по эффективности (доход на рубль инвестиций).<br><br>
                       <strong>Эффективность = Доход ÷ Стоимость авто</strong><br><br>
                       <strong>Интерпретация:</strong><br>
                       ✅ <strong>> 1.0</strong> - авто окупился и приносит чистую прибыль<br>
                       ⚠️ <strong>0.5-1.0</strong> - в процессе окупаемости<br>
                       🔴 <strong>< 0.5</strong> - низкая эффективность, требует анализа<br><br>
                       <strong>Как использовать:</strong><br>
                       • <strong>Принимайте решения</strong> о продаже неперспективных авто<br>
                       • <strong>Планируйте покупки</strong> по аналогии с лидерами рейтинга<br>
                       • <strong>Оптимизируйте парк</strong> - избавляйтесь от аутсайдеров
                      </div>">
            </i>
            <div class="card-header bg-primary-lt">
                <h3 class="mb-0"><i class="fas fa-trophy me-1"></i>Рейтинг эффективности автомобилей
                    <small class="text-muted float-end  ms-2">сортировка по доходности</small>
                </h3>
            </div>
            <div class="card-body">
                @if(!empty($stats['car_efficiency']))
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="50">#</th>
                                <th>Автомобиль</th>
                                <th>Инвестировано</th>
                                <th>Заработано</th>
                                <th>ROI</th>
                                <th>Эффективность</th>
                                <th>Сделок</th>
                                <th>Статус</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stats['car_efficiency'] as $index => $carData)
                            @php
                                $car = $carData['car'];
                                $rank = $index + 1;
                                $rankClass = '';
                                if ($rank <= 3) $rankClass = 'table-success';
                                elseif ($rank >= count($stats['car_efficiency']) - 2) $rankClass = 'table-light';
                            @endphp
                            <tr class="{{ $rankClass }}">
                                <td>
                                    @if($rank <= 3)
                                        <span class="badge bg-free">{{ $rank }}</span>
                                    @else
                                        <span class="badge bg-deal-draw">{{ $rank }}</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $car->brand }} {{ $car->model }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $car->license_plate ?? '—' }}</small>
                                </td>
                                <td>{{ number_format($carData['price'], 0, '', ' ') }} ₽</td>
                                <td class="text-success">{{ number_format($carData['income'], 0, '', ' ') }} ₽</td>
                                <td>
                                    <span class="badge bg-{{ $carData['roi'] > 50 ? 'free' : ($carData['roi'] > 20 ? 'info' : 'warning') }}">
                                        {{ $carData['roi'] }}%
                                    </span>
                                </td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        @php
                                            $maxEfficiency = max(array_column($stats['car_efficiency'], 'efficiency_score'));
                                            $width = $maxEfficiency > 0 ? ($carData['efficiency_score'] / $maxEfficiency) * 100 : 0;
                                        @endphp
                                        <div class="progress-bar bg-{{ $carData['efficiency_score'] > 1 ? 'success' : ($carData['efficiency_score'] > 0.5 ? 'info' : 'warning') }}" 
                                             style="width: {{ $width }}%">
                                            {{ round($carData['efficiency_score'], 2) }}
                                        </div>
                                    </div>
                                    <small class="text-muted">₽ дохода на ₽ инвестиций</small>
                                </td>
                                <td>
                                    <span class="badge">{{ $carData['deal_count'] }}</span>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'available' => 'free',
                                            'in_deal' => 'deal-active',
                                            'maintenance' => 'deal-overdue',
                                            'sold' => 'deal-draw',
                                             'in_draft_deal' => 'deal-draw'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$carData['status']] ?? 'gray' }}">
                                        {{ $car->status_text }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Эффективность</strong> = (Общий доход) / (Стоимость автомобиля). 
                    Значение больше 1 означает, что автомобиль уже окупился и приносит чистую прибыль.
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-emoji-frown display-4 text-muted"></i>
                    <p class="text-muted mt-3">Нет данных для анализа эффективности</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Блок 6: Прогноз доходов -->
<!-- Блок 6: Прогноз доходов -->
<div class="row">
    <div class="col-12">
        <div class="card card-tooltip">
            <i class="fas fa-info-circle info-icon" 
               data-bs-toggle="tooltip" data-bs-html="true"
               title="<div class='tooltip-content'>
                       <strong>Прогноз доходов на 12 месяцев</strong><br><br>
                       Прогноз ежемесячного дохода с учетом ВЫКУПА автомобилей (лизинг с выкупом) и ВОЗВРАТОВ (аренда).<br><br>
                       <strong>Как читать таблицу:</strong><br>
                       • <strong>Прогноз дохода</strong> - ожидаемая сумма в месяц<br>
                       • <strong>Выкуп/Возврат</strong> - сколько авто уходит/возвращается<br>
                       • <strong>Изменение</strong> - рост/падение относительно предыдущего месяца (%)<br>
                       • <strong>Прогресс</strong> - визуализация доли от максимального дохода<br>
                       • <strong>Рекомендация</strong> - что делать в этом месяце<br><br>
                       <strong>Важно:</strong> Выкуп = постоянная потеря дохода, Возврат = временная потеря
                      </div>">
            </i>
            <div class="card-header bg-primary-lt">
                <h3 class="mb-0">
                    <i class="fas fa-calendar me-1"></i>Прогноз доходов на 12 месяцев
                    <small class="text-muted float-end ms-2">с учетом выкупа и возвратов</small>
                </h3>
            </div>
            <div class="card-body">
                @if(!empty($stats['income_forecast']['simple']))
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Месяц</th>
                                <th>Прогноз дохода</th>
                                <th>Выкуп / Возврат</th>
                                <th>Изменение</th>
                                <th>Прогресс</th>
                                <th>Рекомендация</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $previousIncome = null;
                                $maxIncome = max($stats['income_forecast']['simple']);
                            @endphp
                            @foreach($stats['income_forecast']['simple'] as $month => $income)
                            @php
                                // Получаем детали прогноза
                                $forecastDetails = $stats['income_forecast']['detailed'][$month] ?? null;
                                
                                // Парсим месяц для получения ключа
                                $monthKey = '';
                                $isCurrentMonth = false;
                                
                                if ($forecastDetails) {
                                    // Используем данные из детального прогноза
                                    $monthKey = $forecastDetails['month_key'];
                                    $buyoutCount = $forecastDetails['buyout_count'];
                                    $returnCount = $forecastDetails['return_count'];
                                } else {
                                    // Старая логика для обратной совместимости
                                    try {
                                        if (str_contains($month, ' ')) {
                                            $parts = explode(' ', $month);
                                            if (count($parts) === 2 && is_numeric($parts[1])) {
                                                $year = $parts[1];
                                                $monthName = mb_strtolower($parts[0], 'UTF-8');
                                                
                                                $russianMonths = [
                                                    'январь' => '01', 'февраль' => '02', 'март' => '03', 
                                                    'апрель' => '04', 'май' => '05', 'июнь' => '06',
                                                    'июль' => '07', 'август' => '08', 'сентябрь' => '09',
                                                    'октябрь' => '10', 'ноябрь' => '11', 'декабрь' => '12'
                                                ];
                                                
                                                if (isset($russianMonths[$monthName])) {
                                                    $monthKey = $year . '-' . $russianMonths[$monthName];
                                                }
                                            }
                                        }
                                    } catch (\Exception $e) {
                                        // Если не удалось распарсить
                                    }
                                    
                                    if (empty($monthKey)) {
                                        try {
                                            $monthKey = \Carbon\Carbon::parse($month)->format('Y-m');
                                        } catch (\Exception $e) {
                                            $monthKey = now()->format('Y-m');
                                        }
                                    }
                                    
                                    // Для старой версии - считаем что нет данных по выкупам/возвратам
                                    $buyoutCount = 0;
                                    $returnCount = 0;
                                }
                                
                                $isCurrentMonth = ($monthKey == now()->format('Y-m'));
                                
                                // Расчет изменений
                                $change = $previousIncome !== null ? 
                                    ($income - $previousIncome) / max($previousIncome, 1) * 100 : 0;
                                $percentage = $maxIncome > 0 ? ($income / $maxIncome) * 100 : 0;
                                
                                // Определяем рекомендацию
                                $recommendation = '';
                                $recommendationClass = '';
                                
                                if ($income == 0) {
                                    $recommendation = 'Требуется пополнение парка';
                                    $recommendationClass = 'text-danger';
                                } elseif ($forecastDetails && $forecastDetails['buyout_count'] > 2) {
                                    $recommendation = 'СРОЧНО: закупать авто (выкуп ' . $forecastDetails['buyout_count'] . ' авто)';
                                    $recommendationClass = 'text-danger';
                                } elseif ($forecastDetails && $forecastDetails['buyout_count'] > 0) {
                                    $recommendation = 'Планировать покупку ' . $forecastDetails['buyout_count'] . ' авто';
                                    $recommendationClass = 'text-warning';
                                } elseif ($forecastDetails && $forecastDetails['return_count'] > 3) {
                                    $recommendation = 'Искать клиентов на ' . $forecastDetails['return_count'] . ' авто';
                                    $recommendationClass = 'text-info';
                                } elseif ($change < -20) {
                                    $recommendation = 'Анализировать падение доходов';
                                    $recommendationClass = 'text-warning';
                                } elseif ($income < ($maxIncome * 0.5)) {
                                    $recommendation = 'Рассмотреть оптимизацию';
                                    $recommendationClass = 'text-info';
                                }
                                
                                $previousIncome = $income;
                            @endphp
                            <tr class="{{ $isCurrentMonth ? 'table-primary' : '' }}">
                                <td>
                                    <strong>{{ $month }}</strong>
                                    @if($isCurrentMonth)
                                        <span class="badge bg-primary ms-2">Текущий</span>
                                    @endif
                                </td>
                                <td class="{{ $income > 0 ? 'text-success' : 'text-muted' }}">
                                    @if($income > 0)
                                        <strong>{{ number_format($income, 0, '', ' ') }} ₽</strong>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if($buyoutCount > 0 || $returnCount > 0)
                                        <div class="d-flex gap-2">
                                            @if($buyoutCount > 0)
                                                <span class="badge bg-danger" data-bs-toggle="tooltip" 
                                                      title="Выкуп {{ $buyoutCount }} авто навсегда">
                                                    🔴 {{ $buyoutCount }}
                                                </span>
                                            @endif
                                            @if($returnCount > 0)
                                                <span class="badge bg-info" data-bs-toggle="tooltip" 
                                                      title="Возврат {{ $returnCount }} авто в аренду">
                                                    🔵 {{ $returnCount }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="{{ $change > 0 ? 'text-success' : ($change < 0 ? 'text-danger' : 'text-muted') }}">
                                    @if($change != 0)
                                        {{ $change > 0 ? '+' : '' }}{{ round($change, 1) }}%
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-{{ $income > 0 ? 'success' : 'secondary' }}" 
                                             style="width: {{ $percentage }}%">
                                            @if($percentage > 30)
                                                {{ round($percentage, 0) }}%
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="{{ $recommendationClass }}">
                                    <small>{{ $recommendation }}</small>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Внимание!</strong> 
                    <strong class="text-danger">🔴 Выкуп</strong> = автомобиль уходит НАВСЕГДА (лизинг с выкупом)<br>
                    <strong class="text-info">🔵 Возврат</strong> = автомобиль вернется (аренда) и будет доступен для новой сделки
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-chart-line display-4 text-muted"></i>
                    <p class="text-muted mt-3">Нет активных сделок для прогноза</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>



</div>

@if(!empty($stats['monthly_income_data']['labels']))
<script>
document.addEventListener('DOMContentLoaded', function() {
    // График 1: Динамика доходов
    const incomeCtx = document.getElementById('incomeChart').getContext('2d');
    const incomeChart = new Chart(incomeCtx, {
        type: 'line',
        data: {
            labels: @json($stats['monthly_income_data']['labels']),
            datasets: [{
                label: 'Доход, ₽',
                data: @json($stats['monthly_income_data']['data']),
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return new Intl.NumberFormat('ru-RU').format(context.raw) + ' ₽';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            if (value >= 1000000) {
                                return (value / 1000000).toFixed(1) + 'M ₽';
                            } else if (value >= 1000) {
                                return (value / 1000).toFixed(0) + 'K ₽';
                            }
                            return value + ' ₽';
                        }
                    }
                }
            }
        }
    });
    
    // График 2: Распределение по брендам
    @if(!empty($stats['brand_distribution']))
    const brandCtx = document.getElementById('brandChart').getContext('2d');
    
    // Подготавливаем данные
    const brands = @json(array_keys($stats['brand_distribution']));
    const incomes = @json(array_column($stats['brand_distribution'], 'income'));
    const counts = @json(array_column($stats['brand_distribution'], 'count'));
    
    // Цвета для графиков
    const brandColors = [
        'rgba(255, 99, 132, 0.8)',
        'rgba(54, 162, 235, 0.8)',
        'rgba(255, 206, 86, 0.8)',
        'rgba(75, 192, 192, 0.8)',
        'rgba(153, 102, 255, 0.8)',
        'rgba(255, 159, 64, 0.8)'
    ];
    
    const brandChart = new Chart(brandCtx, {
        type: 'doughnut',
        data: {
            labels: brands,
            datasets: [{
                data: incomes,
                backgroundColor: brandColors,
                borderWidth: 1,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = incomes.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            
                            return `${label}: ${new Intl.NumberFormat('ru-RU').format(value)} ₽ (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
    @endif
    
    // Инициализация тултипов
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    
    tooltips.forEach(tooltip => {
        new bootstrap.Tooltip(tooltip, {
            placement: 'auto',
            trigger: 'hover click',
            html: true,
            delay: {show: 100, hide: 100},
            fallbackPlacements: ['top', 'bottom', 'left', 'right'],
            boundary: 'viewport',
            customClass: 'custom-tooltip'
        });
    });
    
    // Функция для обновления позиций подсказок
    function updateTooltipPositions() {
        tooltips.forEach(tooltip => {
            const rect = tooltip.getBoundingClientRect();
            const instance = bootstrap.Tooltip.getInstance(tooltip);
            
            if (instance && rect) {
                let placement = 'top';
                
                if (rect.left < 400) {
                    placement = 'right';
                } else if (window.innerWidth - rect.right < 400) {
                    placement = 'left';
                } else if (rect.top < 200) {
                    placement = 'bottom';
                }
                
                if (instance._config.placement !== placement) {
                    instance._config.placement = placement;
                    instance.update();
                }
            }
        });
    }
    
    window.addEventListener('resize', updateTooltipPositions);
    setTimeout(updateTooltipPositions, 100);
});
</script>
@endif

<style>
/* Дополнительные стили для контента */
.btn-group .btn {
    border-radius: 6px !important;
    margin: 0 2px;
    min-width: 100px;
}

.btn-group .btn i {
    font-size: 1.1em;
}

.btn-outline-primary:hover, .btn-outline-success:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

@media (max-width: 992px) {
    .btn-group {
        flex-wrap: wrap;
        justify-content: center;
        margin-top: 10px;
    }
    
    .btn-group .btn {
        margin-bottom: 5px;
        min-width: 90px;
        font-size: 0.9rem;
    }
}

.table-bordered td:hover {
    background-color: rgba(0,0,0,0.05);
    cursor: pointer;
}

.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.progress {
    overflow: visible;
}

.progress-bar {
    border-radius: 4px;
    position: relative;
}

.progress-bar::after {
    content: '';
    position: absolute;
    right: -5px;
    top: 50%;
    transform: translateY(-50%);
    width: 10px;
    height: 10px;
    background: inherit;
    border-radius: 50%;
    opacity: 0.8;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.9rem;
    }
    
    .card-header h6 {
        font-size: 1rem;
    }
}
</style>
@endsection