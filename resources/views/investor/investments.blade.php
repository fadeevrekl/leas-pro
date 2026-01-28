@extends('layouts.app')

@section('content')
<!-- Единая навигация -->
@include('investor.partials.navigation')

<div class="container-fluid">

    <!-- Блок 1: Ключевые показатели -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Загрузка парка</h6>
                    <h2 class="mb-1 {{ $stats['extended_metrics']['utilization_rate'] > 85 ? 'text-danger' : 'text-success' }}">
                        {{ $stats['extended_metrics']['utilization_rate'] }}%
                    </h2>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar {{ $stats['extended_metrics']['utilization_rate'] > 85 ? 'bg-danger' : 'bg-success' }}" 
                             style="width: {{ $stats['extended_metrics']['utilization_rate'] }}%"></div>
                    </div>
                    <small class="text-muted">
                        {{ $stats['extended_metrics']['cars_in_use'] }} из {{ $stats['total_cars'] }} авто
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">ROI (доходность)</h6>
                    <h2 class="mb-1 text-success">{{ $stats['metrics']['roi_percentage'] }}%</h2>
                    <div class="text-muted small">
                        На инвестиции {{ number_format($stats['metrics']['total_investment'], 0, '', ' ') }} ₽
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Срок окупаемости</h6>
                    <h2 class="mb-1 text-warning">{{ $stats['metrics']['payback_months'] }} мес</h2>
                    <div class="text-muted small">
                        ~{{ ceil($stats['metrics']['payback_months'] / 12) }} лет
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Средний срок аренды</h6>
                    <h2 class="mb-1 text-info">{{ $stats['metrics']['avg_deal_duration'] }} мес</h2>
                    <div class="text-muted small">
                        на автомобиль
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Блок 2: Рекомендации -->
    @if(!empty($stats['recommendations']))
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-{{ $stats['recommendations'][0]['type'] ?? 'info' }}">
                <div class="card-header bg-{{ $stats['recommendations'][0]['type'] ?? 'info' }} text-white">
                    <h3 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Рекомендации по портфелю
                    </h3>
                </div>
                <div class="card-body">
                    @foreach($stats['recommendations'] as $recommendation)
                    <div class="alert alert-{{ $recommendation['type'] }} mb-2">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="bi {{ $recommendation['icon'] }} fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h3 class="alert-heading">{{ $recommendation['title'] }}</h3>
                                <p class="mb-1">{{ $recommendation['message'] }}</p>
                                <hr>
                                <p class="mb-0">
                                    <strong>Рекомендуемое действие:</strong> {{ $recommendation['action'] }}
                                    @if(isset($recommendation['timeline']))
                                    <br><small>Срок: {{ $recommendation['timeline'] }}</small>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Блок 3: Графики -->
    <div class="row mb-4">
        <!-- График доходов по месяцам -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-primary-lt">
                    <h3 class="mb-0">
                        <i class="fas fa-calendar me-1"></i>Доход по месяцам
                        <small class="text-muted float-end ms-2"> за последние 12 месяцев</small>
                    </h3>
                </div>
                <div class="card-body">
                    @if(!empty($stats['monthly_income_data']['labels']))
                    <div style="height: 300px;">
                        <canvas id="monthlyIncomeChart"></canvas>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-chart-bar display-4 text-muted"></i>
                        <p class="text-muted mt-3">Нет данных для отображения</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Прогноз выкупа -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-primary-lt">
                    <h3 class="mb-0">
                        <i class="fas fa-calendar-check me-1"></i>Прогноз выкупа автомобилей
                        <small class="text-muted float-end ms-2">ближайшие 12 месяцев</small>
                    </h3>
                </div>
                <div class="card-body">
                    @if(!empty($stats['buyout_forecast']['detailed']))
                    <div style="height: 300px; overflow-y: auto;">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Автомобиль</th>
                                    <th>Госномер</th>
                                    <th>Сделка</th>
                                    <th>Выкуп через</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['buyout_forecast']['detailed'] as $forecast)
                                <tr>
                                    <td>
                                        <small>{{ $forecast['car_name'] }}</small>
                                    </td>
                                    <td>
                                        <code>{{ $forecast['car_plate'] ?? '—' }}</code>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $forecast['deal_number'] }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $forecast['months_to_buyout'] <= 3 ? 'danger' : ($forecast['months_to_buyout'] <= 6 ? 'warning' : 'deal-end') }}">
                                            {{ $forecast['months_to_buyout'] }} мес
                                        </span>
                                        <br>
                                        <small class="text-muted">{{ $forecast['expected_buyout_date'] }}</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle display-4 text-muted"></i>
                        <p class="text-muted mt-3">В ближайший год выкупов не планируется</p>
                    </div>
                    @endif
                    
                    @if($stats['buyout_forecast']['total_near_buyout'] > 0)
                    <div class="mt-3 p-3 bg-light rounded">
                        <strong>Итого:</strong> 
                        {{ $stats['buyout_forecast']['total_near_buyout'] }} авто будет выкуплено в ближайшие 12 месяцев
                        ({{ $stats['total_cars'] > 0 ? round(($stats['buyout_forecast']['total_near_buyout'] / $stats['total_cars']) * 100, 1) : 0 }}% парка)
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Блок 4: Прогноз доходов -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary-lt">
                    <h3 class="mb-0">
                        <i class="fas fa-line-chart me-1"></i>Прогноз доходов на 12 месяцев
                        <small class="text-muted float-end ms-2">с учетом выкупа автомобилей</small>
                    </h3>
                </div>
                <div class="card-body">
                    @if(!empty($stats['income_forecast']))
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Месяц</th>
                                    <th>Прогноз дохода</th>
                                    <th>Изменение</th>
                                    <th>Прогресс</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $previousIncome = null;
                                    $maxIncome = max($stats['income_forecast']);
                                @endphp
                                @foreach($stats['income_forecast'] as $month => $income)
                                @php
                                    $change = $previousIncome !== null ? 
                                        ($income - $previousIncome) / max($previousIncome, 1) * 100 : 0;
                                    $percentage = $maxIncome > 0 ? ($income / $maxIncome) * 100 : 0;
                                    $previousIncome = $income;
                                @endphp
                                <tr>
                                    <td><strong>{{ $month }}</strong></td>
                                    <td class="{{ $income > 0 ? 'text-success' : 'text-muted' }}">
                                        {{ $income > 0 ? number_format($income, 0, '', ' ') . ' ₽' : '—' }}
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
                                                    {{ number_format($income, 0, '', ' ') }} ₽
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
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
    
    <!-- Блок 5: Детальная статистика (старый блок) -->
    @include('investor.partials.detailed_stats', ['stats' => $stats, 'user' => $user])
</div>

@if(!empty($stats['monthly_income_data']['labels']))
<script>
document.addEventListener('DOMContentLoaded', function() {
    // График доходов по месяцам
    const ctx = document.getElementById('monthlyIncomeChart').getContext('2d');
    const monthlyIncomeChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($stats['monthly_income_data']['labels']),
            datasets: [{
                label: 'Доход, ₽',
                data: @json($stats['monthly_income_data']['data']),
                backgroundColor: 'rgba(40, 167, 69, 0.2)',
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 1,
                borderRadius: 5,
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
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += new Intl.NumberFormat('ru-RU').format(context.raw) + ' ₽';
                            return label;
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
});
</script>
@endif
@endsection