@extends('layouts.app')

@section('content')
<!-- Единая навигация -->
@include('investor.partials.navigation')
<div class="card">
    <div class="card-body">
        @if($cars->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Автомобиль</th>
                        <th>Госномер</th>
                        <th>Статус</th>
                        <th>Цена</th>
                        <th>Тип сделки</th>
                        <th>Дата завершения</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cars as $car)
                    @php
                        $activeDeal = $car->deals->first();
                        $carStatus = $car->status ?? 'unknown';
                        $dealType = $activeDeal ? ($activeDeal->deal_type_text ?? 'Не указан') : 'Нет сделки';
             $endDate = $activeDeal && $activeDeal->end_date ? 
    $activeDeal->end_date->format('d.m.Y') : '—';
                    @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-car fs-4 text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>{{ $car->brand }} {{ $car->model }}</strong>
                                    <div class="text-muted small">
                                        VIN: {{ $car->vin ?? 'Не указан' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        
                        <td>
                            <span class="badge license-plate fs-6">
                                {{ $car->license_plate ?? 'Не указан' }}
                            </span>
                        </td>
                        
                      <td>
    @if($carStatus == 'available')
        <span class="badge bg-free">Доступен</span>
    @elseif($carStatus == 'in_deal')
        <span class="badge bg-deal-active">В сделке</span>
        @if($activeDeal)
            
        @endif
    @elseif($carStatus == 'maintenance')
        <span class="badge bg-deal-overdue">Обслуживание</span>
    @elseif($carStatus == 'sold')
        <span class="badge bg-deal-draw">Продан</span>
    @else
        <span class="badge bg-deal-draw">{{ $carStatus }}</span>
    @endif
</td>
                        
                        <td>
                            @if($car->price)
                                <strong>{{ number_format($car->price, 0, '', ' ') }} ₽</strong>
                            @else
                                <span class="text-muted">Не указана</span>
                            @endif
                        </td>
                        
                      <td>
    @if($activeDeal && $carStatus == 'in_deal')
        @php
            $dealType = $activeDeal->deal_type_text ?? 'Не указан';
        @endphp
        
        @if($activeDeal->deal_type == 'rental')
            <span class="badge bg-deal-end">Аренда</span>
        @elseif($activeDeal->deal_type == 'lease')
            <span class="badge bg-deal-draw">Выкуп</span>
        @else
            <span class="badge bg-deal-draw">{{ $dealType }}</span>
        @endif
    @else
        <span class="text-muted">—</span>
    @endif
</td>
                        
                     <td>
    @if($activeDeal && $activeDeal->end_date && $carStatus == 'in_deal')
        <div class="text-center">
            <div class="fw-bold">{{ $endDate }}</div>
            @php
                $daysLeft = $activeDeal->end_date->diffInDays(now(), false);
            @endphp
            @if($daysLeft > 0)
                <small class="text-success">
                    Завершена {{ abs($daysLeft) }} дн. назад
                </small>
            @else
                <small class="text-warning">
                    Осталось {{ abs($daysLeft) }} дн.
                </small>
            @endif
        </div>
    @else
        <span class="text-muted">—</span>
    @endif
</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Статистика -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h4>Всего автомобилей</h46>
                        <h3>{{ $cars->count() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h4>В сделке</h4>
                        <h3>{{ $cars->where('status', 'in_deal')->count() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h4>Доступно</h4>
                        <h3>{{ $cars->where('status', 'available')->count() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h4>Ваша комиссия</h4>
                        <h3>{{ $user->commission_percent ?? '0' }}%</h3>
                        
                    </div>
                </div>
            </div>
        </div>
        
        @else
        <div class="alert alert-info">
            <div class="d-flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle fs-4"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h6>У вас пока нет автомобилей</h6>
                    <p class="mb-0">Обратитесь к администратору для добавления автомобилей в систему.</p>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Информация -->
        <div class="alert alert-secondary mt-4">
            <h6><i class="fas fa-info-circle"></i> Информация</h6>
            <p class="mb-2">Этот раздел предназначен только для просмотра информации о ваших автомобилях.</p>
            <p class="mb-0">Для добавления автомобилей или изменения информации обращайтесь к администратору системы.</p>
        </div>
    </div>
</div>
@endsection