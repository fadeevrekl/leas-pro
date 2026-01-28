<!-- resources/views/investor/partials/detailed_stats.blade.php -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary-lt">
                <h3 class="mb-0"><i class="fas fa-chart-bar me-1"></i>Детальная статистика</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Общая статистика -->
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h4>Автомобилей</h4>
                                <h3>{{ $stats['total_cars'] }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h4>Активных сделок</h4>
                                <h3>{{ $stats['active_deals'] }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h4>Общий доход</h4>
                                <h3>{{ number_format($stats['total_income'], 0, '', ' ') }} ₽</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h4>Чистый доход</h4>
                                <h3>{{ number_format($stats['net_income'], 0, '', ' ') }} ₽</h3>
                                <small>за вычетом комиссии</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Доход по месяцам -->
                @if(count($stats['income_by_month']) > 0)
                <div class="mt-4">
                    <h3 class="mb-3"><i class="fas fa-calendar-month me-1"></i>Доход по месяцам</h3>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Месяц</th>
                                    <th>Доход</th>
                                    <th>Комиссия</th>
                                    <th>Чистый доход</th>
                                    <th>График</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['income_by_month'] as $month => $income)
                                @php
                                    $commission = $income * (($user->commission_percent ?? 0) / 100);
                                    $net = $income - $commission;
                                    $maxIncome = max($stats['income_by_month']);
                                    $width = $maxIncome > 0 ? ($income / $maxIncome) * 100 : 0;
                                @endphp
                                <tr>
                                    <td><strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y') }}</strong></td>
                                    <td class="text-success">{{ number_format($income, 0, '', ' ') }} ₽</td>
                                    <td class="text-danger">-{{ number_format($commission, 0, '', ' ') }} ₽</td>
                                    <td class="text-primary">{{ number_format($net, 0, '', ' ') }} ₽</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" style="width: {{ $width }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
                
                <!-- Итоговая информация -->
                <div class="alert alert-info mt-4">
                    <h3><i class="bi bi-calculator"></i> Расчет доходов</h3>
                    <p class="mb-1"><strong>Общий доход:</strong> {{ number_format($stats['total_income'], 0, '', ' ') }} ₽</p>
                    <p class="mb-1"><strong>Комиссия ({{ $user->commission_percent }}%):</strong> -{{ number_format($stats['commission'], 0, '', ' ') }} ₽</p>
                    <p class="mb-0"><strong>Чистый доход:</strong> {{ number_format($stats['net_income'], 0, '', ' ') }} ₽</p>
                </div>
                
                @if($stats['total_income'] == 0)
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    Нет данных о доходах. Доходы появятся после создания сделок с вашими автомобилями.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>