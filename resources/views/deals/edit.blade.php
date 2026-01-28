@extends('layouts.app')

@section('title', 'Редактировать сделку')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-red-lt">
                    <h2 class="m-0"><i class="fas fa-edit me-2"></i>Редактирование сделки: {{ $deal->deal_number }}</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('deals.update', $deal) }}" method="POST" id="dealForm">
                        @csrf
    @method('PUT')
    
    @if($deal->status === 'completed')
        <fieldset disabled>
    @endif
                        @if($deal->status === 'completed')
<div class="alert alert-danger">
    <h5><i class="fas fa-exclamation-triangle-fill"></i> Внимание!</h5>
    <p class="mb-0">Вы пытаетесь редактировать завершенную сделку. Для внесения изменений обратитесь к администратору.</p>
</div>
@endif
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-dark-lt">
                                        <h3 class="m-0"> <i class="fas fa-users"></i> Участники сделки</h3>
                                    </div>
                                    <div class="card-body">
                                        <!-- Клиент -->
                                        <div class="mb-3">
                                            <label for="client_id" class="form-label">Клиент *</label>
                                            <select class="form-select @error('client_id') is-invalid @enderror" 
                                                    id="client_id" name="client_id" required>
                                                <option value="">Выберите клиента</option>
                                                @foreach($clients as $client)
                                                    <option value="{{ $client->id }}" {{ old('client_id', $deal->client_id) == $client->id ? 'selected' : '' }}>
                                                        {{ $client->last_name }} {{ $client->first_name }} {{ $client->middle_name }}
                                                        (тел: {{ $client->phone }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('client_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <!-- Автомобиль -->
                                        <div class="mb-3">
                                            <label for="car_id" class="form-label">Автомобиль *</label>
                                            <select class="form-select @error('car_id') is-invalid @enderror" 
                                                    id="car_id" name="car_id" required>
                                                <option value="">Выберите автомобиль</option>
                                                @foreach($cars as $car)
                                                    @php
                                                        $carInfo = "{$car->brand} {$car->model} ({$car->license_plate}) - {$car->price} ₽";
                                                        $selected = old('car_id', $deal->car_id) == $car->id;
                                                    @endphp
                                                    <option value="{{ $car->id }}" 
                                                            data-price="{{ $car->price }}"
                                                            {{ $selected ? 'selected' : '' }}>
                                                        {{ $carInfo }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('car_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div id="car-details" class="mt-2 p-2 bg-light rounded" style="display: none;">
                                                <!-- Информация об авто будет загружена через JS -->
                                            </div>
                                        </div>
                                        
                                     <!-- Менеджер -->
<div class="mb-3">
    <label for="manager_id" class="form-label">Ответственный менеджер *</label>
    
    @if(auth()->user()->isManager() && $deal->manager_id != auth()->id())
        <!-- Менеджер не может менять менеджера сделки, если она не его -->
        <div class="input-group">
            <input type="text" 
                   class="form-control" 
                   value="{{ $deal->manager->name ?? 'Не указан' }} 
                          @if($deal->manager->email)({{ $deal->manager->email }})@endif" 
                   disabled 
                   readonly
                   style="background-color: #f8f9fa;">
            <span class="input-group-text">
                <i class="fas fa-user-x"></i>
            </span>
        </div>
        <small class="text-danger mt-1 d-block">
            <i class="fas fa-exclamation-triangle"></i> Вы не можете изменить менеджера этой сделки
        </small>
        <input type="hidden" name="manager_id" value="{{ $deal->manager_id }}">
        
    @elseif(auth()->user()->isManager())
        <!-- Менеджер редактирует свою сделку -->
        <input type="hidden" name="manager_id" value="{{ auth()->id() }}">
        <div class="input-group">
            <input type="text" 
                   class="form-control" 
                   value="{{ auth()->user()->name }} ({{ auth()->user()->email }})" 
                   disabled 
                   readonly
                   style="background-color: #e9ecef;">
            <span class="input-group-text">
                <i class="fas fa-user-check"></i>
            </span>
        </div>
        <small class="text-muted mt-1 d-block">
            <i class="fas fa-info-circle"></i> Вы отвечаете за эту сделку
        </small>
        
    @elseif(auth()->user()->isAdmin())
        <!-- Админ может менять менеджера -->
        <select class="form-select @error('manager_id') is-invalid @enderror" 
                id="manager_id" name="manager_id" required>
            <option value="">Выберите менеджера</option>
            @foreach($managers as $manager)
                <option value="{{ $manager->id }}" 
                        {{ old('manager_id', $deal->manager_id) == $manager->id ? 'selected' : '' }}>
                    {{ $manager->name }}
                    @if($manager->email)
                        ({{ $manager->email }})
                    @endif
                </option>
            @endforeach
        </select>
        @error('manager_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted mt-1 d-block">
            <i class="fas fa-info-circle"></i> Вы можете изменить ответственного менеджера
        </small>
        
    @else
        <!-- Для инвестора или других ролей -->
        <input type="hidden" name="manager_id" value="{{ $deal->manager_id }}">
        <div class="input-group">
            <input type="text" 
                   class="form-control" 
                   value="{{ $deal->manager->name ?? 'Не указан' }}" 
                   disabled 
                   readonly>
            <span class="input-group-text">
                <i class="fas fa-user"></i>
            </span>
        </div>
    @endif
</div>
                                        
                                        <!-- Статус сделки -->
                                        <div class="mb-3">
                                            <label for="status" class="form-label">Статус сделки</label>
                                            <select class="form-select @error('status') is-invalid @enderror" 
                                                    id="status" name="status">
                                                @foreach(App\Models\Deal::getStatuses() as $key => $label)
                                                    <option value="{{ $key }}" {{ old('status', $deal->status) == $key ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('status')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-dark-lt">
                                        <h3 class="m-0"><i class="fas fa-cog"></i> Параметры сделки</h3>
                                    </div>
                                    <div class="card-body">
                                        <!-- Тип сделки -->
                                        <div class="mb-3">
                                            <label for="deal_type" class="form-label">Тип сделки *</label>
                                            <select class="form-select @error('deal_type') is-invalid @enderror" 
                                                    id="deal_type" name="deal_type" required>
                                                @foreach(App\Models\Deal::getDealTypes() as $key => $label)
                                                    <option value="{{ $key }}" {{ old('deal_type', $deal->deal_type) == $key ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('deal_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <!-- Общая сумма -->
                                        <div class="mb-3">
                                            <label for="total_amount" class="form-label">Общая сумма сделки (₽) *</label>
                                            <input type="number" class="form-control @error('total_amount') is-invalid @enderror" 
                                                   id="total_amount" name="total_amount" step="0.01" min="0"
                                                   value="{{ old('total_amount', $deal->total_amount) }}" required>
                                            @error('total_amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <!-- Первоначальный взнос -->
                                        <div class="mb-3">
                                            <label for="initial_payment" class="form-label">Первоначальный взнос (₽)</label>
                                            <input type="number" class="form-control @error('initial_payment') is-invalid @enderror" 
                                                   id="initial_payment" name="initial_payment" step="0.01" min="0"
                                                   value="{{ old('initial_payment', $deal->initial_payment) }}">
                                            @error('initial_payment')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <!-- SMS уведомления -->
                                        <div class="mb-3 form-check">
                                            <input type="checkbox" class="form-check-input" 
                                                   id="sms_notifications" name="sms_notifications" value="1" 
                                                   {{ old('sms_notifications', $deal->sms_notifications) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="sms_notifications">
                                                Отправлять SMS напоминания за день до оплаты
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-dark-lt">
                                        <h3 class="m-0"><i class="fas fa-calendar"></i> График платежей</h3>
                                    </div>
                                    <div class="card-body">
                                        <!-- Количество платежей -->
                                        <div class="mb-3">
                                            <label for="payment_count" class="form-label">Количество платежей *</label>
                                            <input type="number" class="form-control @error('payment_count') is-invalid @enderror" 
                                                   id="payment_count" name="payment_count" min="1" max="365"
                                                   value="{{ old('payment_count', $deal->payment_count) }}" required>
                                            <small class="text-muted">Внимание: изменение пересоздаст график платежей!</small>
                                            @error('payment_count')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <!-- Сумма платежа -->
                                        <div class="mb-3">
                                            <label for="payment_amount" class="form-label">Сумма регулярного платежа (₽) *</label>
                                            <input type="number" class="form-control @error('payment_amount') is-invalid @enderror" 
                                                   id="payment_amount" name="payment_amount" step="0.01" min="0"
                                                   value="{{ old('payment_amount', $deal->payment_amount) }}" required>
                                            @error('payment_amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <!-- Дата начала -->
                                        <div class="mb-3">
                                            <label for="start_date" class="form-label">Дата начала сделки *</label>
                                            <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                                   id="start_date" name="start_date" 
                                                   value="{{ old('start_date', $deal->start_date->format('Y-m-d')) }}" required>
                                            @error('start_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-dark-lt">
                                       <h3 class="m-0"><i class="fas fa-calculator"></i> Настройки оплаты</h3>
                                    </div>
                                    <div class="card-body">
                                        <!-- Период оплаты -->
                                        <div class="mb-3">
                                            <label for="payment_period" class="form-label">Период оплаты *</label>
                                            <select class="form-select @error('payment_period') is-invalid @enderror" 
                                                    id="payment_period" name="payment_period" required>
                                                @foreach(App\Models\Deal::getPaymentPeriods() as $key => $label)
                                                    <option value="{{ $key }}" {{ old('payment_period', $deal->payment_period) == $key ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('payment_period')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <!-- День оплаты (для месячного периода) -->
                                        <div class="mb-3" id="payment_day_container">
                                            <label for="payment_day" class="form-label">День оплаты (ежемесячно)</label>
                                            <select class="form-select @error('payment_day') is-invalid @enderror" 
                                                    id="payment_day" name="payment_day">
                                                <option value="">Любой день</option>
                                                @for($i = 1; $i <= 31; $i++)
                                                    <option value="{{ $i }}" {{ old('payment_day', $deal->payment_day) == $i ? 'selected' : '' }}>
                                                        {{ $i }} число
                                                    </option>
                                                @endfor
                                            </select>
                                            @error('payment_day')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <!-- День недели (для недельного периода) -->
                                        <div class="mb-3" id="payment_weekday_container" style="display: none;">
                                            <label for="payment_weekday" class="form-label">День недели оплаты</label>
                                            <select class="form-select" id="payment_weekday">
                                                @foreach(App\Models\Deal::getWeekDays() as $key => $label)
                                                    <option value="{{ $key }}" {{ $deal->payment_day == $key ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <!-- Расчетная информация -->
                                        <div class="alert alert-info">
                                            <h6>Текущий расчет:</h6>
                                            <div id="calculation_result">
                                                Заполните сумму и количество платежей для расчета
                                            </div>
                                        </div>
                                        
                                        <!-- Информация о текущем прогрессе -->
                                        @if($deal->payments()->count() > 0)
                                        <div class="alert alert-warning">
                                            <h6>Внимание!</h6>
                                            <p>Изменение параметров платежей пересоздаст график платежей.</p>
                                            <p><strong>Оплачено:</strong> {{ number_format($deal->total_paid, 0, '', ' ') }} ₽</p>
                                            <p><strong>Осталось платежей:</strong> {{ $deal->payments()->where('status', 'pending')->count() }}</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Заметки -->
                        <div class="card mb-4">
                            <div class="card-header bg-dark-lt">
                                <h3 class="m-0"><i class="fas fa-edit me-1"></i> Дополнительная информация</h3>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Заметки менеджера</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                              id="notes" name="notes" rows="3">{{ old('notes', $deal->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Кнопки -->
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check-circle me-1"></i>Сохранить изменения
                            </button>
                            <a href="{{ route('deals.show', $deal) }}" class="btn btn-secondary">
                                <i class="fas fa-times-circle me-1"></i>Отмена
                            </a>
                            
                            @if($deal->status === 'draft')
                                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                    <i class="bi bi-x-octagon me-1"></i>Отменить сделку
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно отмены сделки -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Отмена сделки</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите отменить эту сделку?</p>
                <p class="text-danger"><strong>Внимание:</strong> Это действие вернет автомобиль в список доступных.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Нет</button>
                <form action="{{ route('deals.destroy', $deal) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Да, отменить сделку</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const carSelect = document.getElementById('car_id');
    const carDetails = document.getElementById('car-details');
    const totalAmountInput = document.getElementById('total_amount');
    const paymentCountInput = document.getElementById('payment_count');
    const paymentAmountInput = document.getElementById('payment_amount');
    const initialPaymentInput = document.getElementById('initial_payment');
    const paymentPeriodSelect = document.getElementById('payment_period');
    const paymentDayContainer = document.getElementById('payment_day_container');
    const paymentWeekdayContainer = document.getElementById('payment_weekday_container');
    const calculationResult = document.getElementById('calculation_result');
    
    // Загрузка информации об автомобиле
    carSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const carPrice = selectedOption.dataset.price;
        
        if (carPrice) {
            carDetails.innerHTML = `
                <h6>Информация об автомобиле:</h6>
                <p><strong>Цена:</strong> ${parseFloat(carPrice).toLocaleString('ru-RU')} ₽</p>
                <p><small>Рекомендуемая сумма сделки: ${(parseFloat(carPrice) * 1.1).toLocaleString('ru-RU')} ₽</small></p>
            `;
            carDetails.style.display = 'block';
        } else {
            carDetails.style.display = 'none';
        }
    });
    
    // Изменение периода оплаты
    paymentPeriodSelect.addEventListener('change', function() {
        if (this.value === 'month') {
            paymentDayContainer.style.display = 'block';
            paymentWeekdayContainer.style.display = 'none';
        } else if (this.value === 'week') {
            paymentDayContainer.style.display = 'none';
            paymentWeekdayContainer.style.display = 'block';
        } else {
            paymentDayContainer.style.display = 'none';
            paymentWeekdayContainer.style.display = 'none';
        }
    });
    
    // Расчет суммы платежа
    function calculatePaymentAmount() {
        const totalAmount = parseFloat(totalAmountInput.value) || 0;
        const initialPayment = parseFloat(initialPaymentInput.value) || 0;
        const paymentCount = parseInt(paymentCountInput.value) || 1;
        
        if (totalAmount > 0 && paymentCount > 0) {
            const remainingAmount = totalAmount - initialPayment;
            const paymentAmount = remainingAmount / paymentCount;
            
            paymentAmountInput.value = paymentAmount.toFixed(2);
            updateCalculationInfo(totalAmount, initialPayment, paymentCount, paymentAmount);
        }
    }
    
    // Обновление информации о расчете
    function updateCalculationInfo(total, initial, count, payment) {
        const totalFormatted = total.toLocaleString('ru-RU', {minimumFractionDigits: 2});
        const initialFormatted = initial.toLocaleString('ru-RU', {minimumFractionDigits: 2});
        const paymentFormatted = payment.toLocaleString('ru-RU', {minimumFractionDigits: 2});
        const remaining = total - initial;
        const remainingFormatted = remaining.toLocaleString('ru-RU', {minimumFractionDigits: 2});
        
        calculationResult.innerHTML = `
            <p><strong>Общая сумма:</strong> ${totalFormatted} ₽</p>
            <p><strong>Первоначальный взнос:</strong> ${initialFormatted} ₽</p>
            <p><strong>Остаток к оплате:</strong> ${remainingFormatted} ₽</p>
            <p><strong>Количество платежей:</strong> ${count}</p>
            <p><strong>Сумма платежа:</strong> ${paymentFormatted} ₽</p>
            <p><strong>Итого к выплате:</strong> ${(initial + (payment * count)).toLocaleString('ru-RU', {minimumFractionDigits: 2})} ₽</p>
        `;
    }
    
    // Слушатели изменений
    totalAmountInput.addEventListener('input', calculatePaymentAmount);
    initialPaymentInput.addEventListener('input', calculatePaymentAmount);
    paymentCountInput.addEventListener('input', calculatePaymentAmount);
    
    // Инициализация
    if (carSelect.value) {
        carSelect.dispatchEvent(new Event('change'));
    }
    paymentPeriodSelect.dispatchEvent(new Event('change'));
    calculatePaymentAmount();
    
    // Валидация формы
    document.getElementById('dealForm').addEventListener('submit', function(e) {
        const total = parseFloat(totalAmountInput.value);
        const initial = parseFloat(initialPaymentInput.value) || 0;
        const payment = parseFloat(paymentAmountInput.value);
        const count = parseInt(paymentCountInput.value);
        
        if (initial > total) {
            e.preventDefault();
            alert('Первоначальный взнос не может превышать общую сумму сделки!');
            initialPaymentInput.focus();
            return false;
        }
        
        if (payment <= 0) {
            e.preventDefault();
            alert('Сумма платежа должна быть больше нуля!');
            paymentAmountInput.focus();
            return false;
        }
        
        const calculatedTotal = initial + (payment * count);
        if (Math.abs(calculatedTotal - total) > 1) {
            e.preventDefault();
            if (!confirm(`Расчетная сумма (${calculatedTotal.toLocaleString('ru-RU')} ₽) не совпадает с общей суммой сделки (${total.toLocaleString('ru-RU')} ₽). Продолжить?`)) {
                return false;
            }
        }
        
        // Предупреждение о пересоздании платежей
        if (@json($deal->payments()->count() > 0)) {
            if (!confirm('Изменение параметров пересоздаст график платежей. Продолжить?')) {
                e.preventDefault();
                return false;
            }
        }
    });
});
</script>
@endsection