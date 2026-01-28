@extends('layouts.app')

@section('title', 'Создать сделку')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-green-lt">
                    <h2 class="m-0"><i class="fas fa-handshake me-2"></i>Создание новой сделки</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('deals.store') }}" method="POST" id="dealForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-dark-lt">
                                        <h3 class="m-0"><i class="fas fa-users"></i> Участники сделки</h3>
                                    </div>
                                    <div class="card-body">
                                      
                                      
                                      
                                      <!-- Клиент -->
<div class="mb-3">
    <label for="client_id" class="form-label">Клиент *</label>
    <select class="form-select select2-clients @error('client_id') is-invalid @enderror" 
            id="client_id" name="client_id" required
            data-placeholder="Начните вводить фамилию, имя или телефон клиента...">
        <option value=""></option>
        @foreach($clients as $clientOption)
            <option value="{{ $clientOption->id }}" 
                    {{ (old('client_id', $client->id ?? '') == $clientOption->id) ? 'selected' : '' }}
                    data-phone="{{ $clientOption->phone }}"
                    data-status="{{ $clientOption->status }}">
                {{ $clientOption->last_name }} {{ $clientOption->first_name }} {{ $clientOption->middle_name }}
                (тел: {{ $clientOption->phone }})
                @if($clientOption->status === 'in_deal')
                    - <span class="text-warning">Уже в сделке!</span>
                @endif
            </option>
        @endforeach
    </select>
    <div class="form-text">
        <i class="fas fa-info-circle"></i> Начните вводить фамилию, имя или телефон для поиска
        @if($client)
            <span class="text-success">
                <i class="fas fa-check-circle"></i> Автоматически выбран клиент со страницы
            </span>
        @endif
    </div>
    @error('client_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<!-- Автомобиль -->
<div class="mb-3">
    <label for="car_id" class="form-label">Автомобиль *</label>
    <select class="form-select select2-cars @error('car_id') is-invalid @enderror" 
            id="car_id" name="car_id" required
            data-placeholder="Начните вводить марку, модель или госномер...">
        <option value=""></option>
        @foreach($cars as $carOption)
            <option value="{{ $carOption->id }}" 
                    {{ (old('car_id', $car->id ?? '') == $carOption->id) ? 'selected' : '' }}
                    data-price="{{ $carOption->price }}"
                    data-license="{{ $carOption->license_plate }}"
                    data-status="{{ $carOption->status }}">
                {{ $carOption->brand }} {{ $carOption->model }} 
                ({{ $carOption->license_plate }}) 
                - {{ number_format($carOption->price, 0, '', ' ') }} ₽
                @if($carOption->status !== 'available')
                    - <span class="text-warning">Занят</span>
                @endif
            </option>
        @endforeach
    </select>
    <div class="form-text">
        <i class="fas fa-info-circle"></i> Начните вводить марку, модель или госномер для поиска
        @if($car)
            <span class="text-success">
                <i class="fas fa-check-circle"></i> Автоматически выбран автомобиль со страницы
            </span>
        @endif
    </div>
    @error('car_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
                                      
                                      
                                      
                                      

                                        
                                 <!-- Менеджер -->
<div class="mb-3">
    <label for="manager_id" class="form-label">Ответственный менеджер *</label>
    
    @if(auth()->user()->isManager())
        <!-- Для менеджера - скрытое поле с его ID -->
        <input type="hidden" name="manager_id" value="{{ auth()->id() }}">
        <div class="input-group">
            <input type="text" 
                   class="form-control" 
                   value="{{ auth()->user()->name }} ({{ auth()->user()->email }})" 
                   disabled 
                   readonly
                   style="background-color: #e9ecef; cursor: not-allowed;">
            <span class="input-group-text">
                <i class="fas fa-user-check"></i>
            </span>
        </div>
        <small class="text-muted mt-1 d-block">
            <i class="fas fa-info-circle"></i> Вы создаете сделку от своего имени
        </small>
        
    @elseif(auth()->user()->isAdmin())
        <!-- Для администратора - выпадающий список -->
        <select class="form-select @error('manager_id') is-invalid @enderror" 
                id="manager_id" name="manager_id" required>
            <option value="">Выберите менеджера</option>
            @foreach($managers as $manager)
                <option value="{{ $manager->id }}" 
                        {{ old('manager_id') == $manager->id ? 'selected' : '' }}>
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
            <i class="fas fa-info-circle"></i> Выберите менеджера, который будет вести сделку
        </small>
        
    @else
        <!-- Для инвестора или других ролей - скрытое поле с ID текущего пользователя -->
        <input type="hidden" name="manager_id" value="{{ auth()->id() }}">
        <div class="input-group">
            <input type="text" 
                   class="form-control" 
                   value="{{ auth()->user()->name }}" 
                   disabled 
                   readonly
                   style="background-color: #e9ecef;">
            <span class="input-group-text">
                <i class="fas fa-user"></i>
            </span>
        </div>
    @endif
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
                                                    <option value="{{ $key }}" {{ old('deal_type') == $key ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('deal_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <!-- Общая сумма -->
                 <!-- Общая сумма сделки (рассчитывается автоматически) -->
<div class="col-md-6">
    <div class="form-group">
        <label for="total_amount">Общая сумма сделки *</label>
        <div class="input-group">
            <input type="number" 
                   class="form-control @error('total_amount') is-invalid @enderror" 
                   id="total_amount" 
                   name="total_amount" 
                   value="{{ old('total_amount', $deal->total_amount ?? '') }}"
                   step="0.01" 
                   min="0" 
                   max="999999999.99" 
                   readonly> <!-- УБРАЛИ required, ДОБАВИЛИ readonly -->
            <span class="input-group-text">₽</span>
        </div>
        @error('total_amount')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="form-text text-muted">
            Рассчитывается автоматически: (Сумма платежа × Количество платежей) + Первоначальный взнос
        </small>
    </div>
</div>
                                        
                                        <!-- Первоначальный взнос -->
                                        <div class="mb-3">
                                            <label for="initial_payment" class="form-label">Первоначальный взнос (₽)</label>
                                            <input type="number" class="form-control @error('initial_payment') is-invalid @enderror" 
                                                   id="initial_payment" name="initial_payment" step="0.01" min="0"
                                                   value="{{ old('initial_payment', 0) }}">
                                            @error('initial_payment')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
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
                                                   value="{{ old('payment_count', 12) }}" required>
                                            @error('payment_count')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <!-- Сумма платежа -->
                                        <div class="mb-3">
                                            <label for="payment_amount" class="form-label">Сумма регулярного платежа (₽) *</label>
                                            <input type="number" class="form-control @error('payment_amount') is-invalid @enderror" 
                                                   id="payment_amount" name="payment_amount" step="0.01" min="0"
                                                   value="{{ old('payment_amount') }}" required>
                                            @error('payment_amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <!-- Дата начала -->
                                        <div class="mb-3">
                                            <label for="start_date" class="form-label">Дата начала сделки *</label>
                                            <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                                   id="start_date" name="start_date" 
                                                   value="{{ old('start_date', date('Y-m-d')) }}" required>
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
                                                    <option value="{{ $key }}" {{ old('payment_period', 'month') == $key ? 'selected' : '' }}>
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
                                                    <option value="{{ $i }}" {{ old('payment_day') == $i ? 'selected' : '' }}>
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
                                                    <option value="{{ $key }}">
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <!-- SMS уведомления -->
                                        <div class="mb-3 form-check">
                                            <input type="checkbox" class="form-check-input" 
                                                   id="sms_notifications" name="sms_notifications" value="1" 
                                                   {{ old('sms_notifications', true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="sms_notifications">
                                                Отправлять SMS напоминания за день до оплаты
                                            </label>
                                        </div>
                                        
                                        <!-- Расчетная информация -->
                                        <div class="alert alert-info">
                                            <h3>Расчет:</h3>
                                            <div id="calculation_result">
                                                Заполните сумму и количество платежей для расчета
                                            </div>
                                        </div>
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
                                              id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Кнопки -->
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check-circle me-1"></i>Создать сделку
                            </button>
                            <a href="{{ route('deals.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times-circle me-1"></i>Отмена
                            </a>
                        </div>
                    </form>
                </div>
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
            
            // Устанавливаем цену автомобиля как общую сумму сделки
            if (!totalAmountInput.value) {
                totalAmountInput.value = carPrice;
                calculatePaymentAmount();
            }
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
    });
});
</script>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Настройки для клиентов
    $('.select2-clients').select2({
        theme: 'bootstrap-5',
        language: 'ru',
        width: '100%',
        placeholder: 'Начните вводить фамилию...',
        allowClear: true,
        minimumInputLength: 2, // Начинать поиск после 2 символов
        dropdownParent: $('#client_id').closest('.card-body'), // Для корректного отображения в модалках
        escapeMarkup: function (markup) { return markup; }, // Разрешаем HTML
        templateResult: formatClientResult,
        templateSelection: formatClientSelection
    });
    
    // Настройки для автомобилей
    $('.select2-cars').select2({
        theme: 'bootstrap-5',
        language: 'ru',
        width: '100%',
        placeholder: 'Начните вводить марку или госномер...',
        allowClear: true,
        minimumInputLength: 2,
        dropdownParent: $('#car_id').closest('.card-body'),
        escapeMarkup: function (markup) { return markup; },
        templateResult: formatCarResult,
        templateSelection: formatCarSelection
    });
    
    // Форматирование клиента в результатах поиска
    function formatClientResult(client) {
        if (!client.id) {
            return client.text;
        }
        
        var $result = $('<span class="d-flex align-items-center"></span>');
        var $text = $('<span class="me-2"></span>').text(
            client.text.replace(/<span[^>]*>.*?<\/span>/g, '') // Убираем HTML для текста
        );
        $result.append($text);
        
        // Добавляем предупреждение если клиент уже в сделке
        if (client.element && $(client.element).data('status') === 'in_deal') {
            $result.append('<span class="badge bg-warning ms-auto">Уже в сделке</span>');
        }
        
        return $result;
    }
    
    // Форматирование выбранного клиента
    function formatClientSelection(client) {
        if (!client.id) {
            return client.text;
        }
        
        // Показываем только ФИО и телефон для выбранного элемента
        var text = client.text;
        var cleanText = text.replace(/<span[^>]*>.*?<\/span>/g, ''); // Убираем HTML
        return cleanText;
    }
    
    // Форматирование автомобиля в результатах поиска
    function formatCarResult(car) {
        if (!car.id) {
            return car.text;
        }
        
        var $result = $('<span class="d-flex align-items-center"></span>');
        var $text = $('<span class="me-2"></span>').text(
            car.text.replace(/<span[^>]*>.*?<\/span>/g, '')
        );
        $result.append($text);
        
        // Добавляем предупреждение если автомобиль занят
        if (car.element) {
            var status = $(car.element).data('status');
            if (status && status !== 'available') {
                $result.append('<span class="badge bg-danger ms-auto">Занят</span>');
            }
        }
        
        return $result;
    }
    
    // Форматирование выбранного автомобиля
    function formatCarSelection(car) {
        if (!car.id) {
            return car.text;
        }
        
        var text = car.text;
        var cleanText = text.replace(/<span[^>]*>.*?<\/span>/g, '');
        return cleanText;
    }
    
    // Обновляем информацию о выбранном автомобиле
    $('#car_id').on('change.select2', function() {
        const selectedOption = $(this).find('option:selected');
        const carPrice = selectedOption.data('price');
        
        if (carPrice) {
            const formattedPrice = new Intl.NumberFormat('ru-RU').format(carPrice);
            if ($('#car-details').length === 0) {
                $(this).closest('.mb-3').after(`
                    <div id="car-details" class="alert alert-info mt-2">
                        <h6>Информация об автомобиле:</h6>
                        <p><strong>Цена:</strong> ${formattedPrice} ₽</p>
                        <p><small>Рекомендуемая сумма сделки: ${new Intl.NumberFormat('ru-RU').format(carPrice * 1.1)} ₽</small></p>
                    </div>
                `);
            } else {
                $('#car-details').html(`
                    <h6>Информация об автомобиле:</h6>
                    <p><strong>Цена:</strong> ${formattedPrice} ₽</p>
                    <p><small>Рекомендуемая сумма сделки: ${new Intl.NumberFormat('ru-RU').format(carPrice * 1.1)} ₽</small></p>
                `).show();
            }
            
            // Устанавливаем цену автомобиля как общую сумму сделки
            if (!$('#total_amount').val()) {
                $('#total_amount').val(carPrice).trigger('input');
            }
        } else {
            $('#car-details').hide();
        }
    });
    
    // Если автомобиль уже выбран при загрузке
    if ($('#car_id').val()) {
        setTimeout(() => {
            $('#car_id').trigger('change.select2');
        }, 300);
    }
    
    // Дополнительные стили для Select2
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .select2-container--bootstrap-5 .select2-selection {
                min-height: 38px;
                padding: 5px 12px;
            }
            .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
                line-height: 1.5;
                padding-left: 0;
            }
            .select2-container--bootstrap-5 .select2-dropdown {
                border: 1px solid #dee2e6;
                border-radius: 0.375rem;
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            }
            .select2-container--bootstrap-5 .select2-results__option {
                padding: 8px 12px;
            }
            .select2-container--bootstrap-5 .select2-results__option--highlighted {
                background-color: #0d6efd;
            }
        `)
        .appendTo('head');
});





document.addEventListener('DOMContentLoaded', function() {
    // Находим все нужные поля
    const paymentAmountInput = document.getElementById('payment_amount');
    const paymentCountInput = document.getElementById('payment_count');
    const initialPaymentInput = document.getElementById('initial_payment');
    const totalAmountInput = document.getElementById('total_amount');
    
    // Функция для расчёта общей суммы
    function calculateTotalAmount() {
        // Получаем значения из полей (или 0, если пусто)
        const paymentAmount = parseFloat(paymentAmountInput.value) || 0;
        const paymentCount = parseInt(paymentCountInput.value) || 0;
        const initialPayment = parseFloat(initialPaymentInput.value) || 0;
        
        // Расчёт: (сумма платежа × количество платежей) + первоначальный взнос
        const calculatedTotal = (paymentAmount * paymentCount) + initialPayment;
        
        // Обновляем поле общей суммы
        if (totalAmountInput) {
            totalAmountInput.value = calculatedTotal.toFixed(2);
        }
    }
    
    // Добавляем обработчики событий на изменение значений
    if (paymentAmountInput) {
        paymentAmountInput.addEventListener('input', calculateTotalAmount);
        paymentAmountInput.addEventListener('change', calculateTotalAmount);
    }
    
    if (paymentCountInput) {
        paymentCountInput.addEventListener('input', calculateTotalAmount);
        paymentCountInput.addEventListener('change', calculateTotalAmount);
    }
    
    if (initialPaymentInput) {
        initialPaymentInput.addEventListener('input', calculateTotalAmount);
        initialPaymentInput.addEventListener('change', calculateTotalAmount);
    }
    
    // Выполняем первоначальный расчёт при загрузке страницы
    calculateTotalAmount();
    
    // Также пересчитываем при загрузке, если есть старые значения
    window.addEventListener('load', calculateTotalAmount);
});




</script>


@endpush
@endsection
