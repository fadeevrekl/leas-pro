@extends('layouts.app')

@section('title', 'Добавить автомобиль')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-green-lt">
                    <h2 class="m-0"><i class="fas fa-car me-2"></i>Добавить новый автомобиль</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('cars.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="brand" class="form-label">Марка *</label>
                                    <input type="text" class="form-control @error('brand') is-invalid @enderror" 
                                           id="brand" name="brand" value="{{ old('brand') }}" required>
                                    @error('brand')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="model" class="form-label">Модель *</label>
                                    <input type="text" class="form-control @error('model') is-invalid @enderror" 
                                           id="model" name="model" value="{{ old('model') }}" required>
                                    @error('model')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            
                                <div class="col-md-4">
        <div class="mb-3">
            <label for="year" class="form-label">Год выпуска *</label>
            <select class="form-select @error('year') is-invalid @enderror" 
                    id="year" name="year" required>
                <option value="">Выберите год</option>
                @php
                    $currentYear = date('Y');
                    $startYear = 1990; // Можно изменить на более ранний год
                @endphp
                @for($y = $currentYear; $y >= $startYear; $y--)
                    <option value="{{ $y }}" {{ old('year') == $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endfor
            </select>
            @error('year')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
                            
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="color" class="form-label">Цвет *</label>
                                    <input type="text" class="form-control @error('color') is-invalid @enderror" 
                                           id="color" name="color" value="{{ old('color') }}" required>
                                    @error('color')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="vin" class="form-label">VIN номер *</label>
                                    <input type="text" class="form-control @error('vin') is-invalid @enderror" 
                                           id="vin" name="vin" maxlength="17" 
                                           value="{{ old('vin') }}" required>
                                    <small class="form-text text-muted">17 символов</small>
                                    @error('vin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="license_plate" class="form-label">Госномер *</label>
                                    <input type="text" class="form-control @error('license_plate') is-invalid @enderror" 
                                           id="license_plate" name="license_plate" 
                                           value="{{ old('license_plate') }}" required>
                                    @error('license_plate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="mileage" class="form-label">Пробег (км) *</label>
                                    <input type="number" class="form-control @error('mileage') is-invalid @enderror" 
                                           id="mileage" name="mileage" min="0" 
                                           value="{{ old('mileage', 0) }}" required>
                                    @error('mileage')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="fuel_type" class="form-label">Топливо *</label>
                                    <select class="form-select @error('fuel_type') is-invalid @enderror" 
                                            id="fuel_type" name="fuel_type" required>
                                        <option value="">Выберите тип топлива</option>
                                        @foreach(App\Models\Car::getFuelTypes() as $key => $label)
                                            <option value="{{ $key }}" {{ old('fuel_type') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('fuel_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            
                            
                            
                            
                            
<div class="col-md-3">
    <div class="mb-3">
        <label for="price" class="form-label">Цена (₽) *</label>
        <input type="text" 
               class="form-control @error('price') is-invalid @enderror" 
               id="price" 
               name="price_formatted"
               placeholder="Введите цену"
               value="{{ old('price_formatted', old('price') ? number_format(old('price'), 0, '', ' ') : '') }}"
               required
               autocomplete="off">
        <input type="hidden" id="price_original" name="price" value="{{ old('price') }}">
        @error('price')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
       
    </div>
</div>
                            
                            
                            
                            
                            
                            
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Статус *</label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        @foreach(App\Models\Car::getStatuses() as $key => $label)
                                            <option value="{{ $key }}" {{ old('status', 'available') == $key ? 'selected' : '' }}>
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
                        
                        <div class="row">
                <div class="col-md-6">
    <div class="mb-3">
        <label for="investor_id" class="form-label">Инвестор *</label>
        <select class="form-select @error('investor_id') is-invalid @enderror" 
                id="investor_id" name="investor_id" required>
            <option value="">Выберите инвестора</option>
            @foreach($investors as $investor)
                <option value="{{ $investor->id }}" {{ old('investor_id') == $investor->id ? 'selected' : '' }}>
                    {{ $investor->name }}
                    @if($investor->commission_percent)
                        (комиссия: {{ $investor->commission_percent }}%)
                    @endif
                </option>
            @endforeach
        </select>
        @error('investor_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
                            
                   <div class="col-md-6">
    <div class="mb-3">
        <label for="manager_id" class="form-label">Менеджер</label>
        <select class="form-select @error('manager_id') is-invalid @enderror" 
                id="manager_id" name="manager_id">
            <option value="">Не назначен</option>
            @foreach($managers as $manager)
                <option value="{{ $manager->id }}" {{ old('manager_id') == $manager->id ? 'selected' : '' }}>
                    {{ $manager->name }}
                </option>
            @endforeach
        </select>
        @error('manager_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="gps_tracker_id" class="form-label">ID GPS трекера</label>
                                    <input type="text" class="form-control @error('gps_tracker_id') is-invalid @enderror" 
                                           id="gps_tracker_id" name="gps_tracker_id" 
                                           value="{{ old('gps_tracker_id') }}">
                                    @error('gps_tracker_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Заметки</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        
                        
                        
                        
                        {{-- Блок для документов --}}
<div class="card mt-4">
    <div class="card-header bg-primary-lt">
        <h3 class="mb-0">
            <i class="bi bi-files me-2"></i>Документы автомобиля
        </h3>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3">
            Здесь можно загрузить основные документы автомобиля. Все поля необязательны, можно добавить позже.
        </p>
        
        <div class="row">
            {{-- ПТС --}}
            <div class="col-md-6 mb-3">
                <label for="pts_file" class="form-label">ПТС (Паспорт транспортного средства)</label>
                <input type="file" class="form-control @error('documents.pts') is-invalid @enderror" 
                       id="pts_file" name="documents[pts][file]">
                <div class="mt-2">
                    <input type="text" class="form-control form-control-sm" 
                           placeholder="Номер ПТС" name="documents[pts][number]">
                    <small class="form-text text-muted">Опционально</small>
                </div>
                @error('documents.pts')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            {{-- СТС --}}
            <div class="col-md-6 mb-3">
                <label for="sts_file" class="form-label">СТС (Свидетельство о регистрации)</label>
                <input type="file" class="form-control @error('documents.sts') is-invalid @enderror" 
                       id="sts_file" name="documents[sts][file]">
                <div class="mt-2">
                    <input type="text" class="form-control form-control-sm" 
                           placeholder="Номер СТС" name="documents[sts][number]">
                    <small class="form-text text-muted">Опционально</small>
                </div>
                @error('documents.sts')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="row">
            {{-- ОСАГО --}}
            <div class="col-md-6 mb-3">
                <label for="osago_file" class="form-label">ОСАГО</label>
                <input type="file" class="form-control @error('documents.osago') is-invalid @enderror" 
                       id="osago_file" name="documents[osago][file]">
                <div class="mt-2">
                    <div class="row">
                        <div class="col">
                            <input type="date" class="form-control form-control-sm" 
                                   placeholder="Дата выдачи" name="documents[osago][issue_date]">
                        </div>
                        <div class="col">
                            <input type="date" class="form-control form-control-sm" 
                                   placeholder="Срок действия" name="documents[osago][expiry_date]">
                        </div>
                    </div>
                    <small class="form-text text-muted">Даты опциональны</small>
                </div>
                @error('documents.osago')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            {{-- КАСКО --}}
            <div class="col-md-6 mb-3">
                <label for="kasko_file" class="form-label">КАСКО (если есть)</label>
                <input type="file" class="form-control @error('documents.kasko') is-invalid @enderror" 
                       id="kasko_file" name="documents[kasko][file]">
                <div class="mt-2">
                    <div class="row">
                        <div class="col">
                            <input type="date" class="form-control form-control-sm" 
                                   placeholder="Дата выдачи" name="documents[kasko][issue_date]">
                        </div>
                        <div class="col">
                            <input type="date" class="form-control form-control-sm" 
                                   placeholder="Срок действия" name="documents[kasko][expiry_date]">
                        </div>
                    </div>
                    <small class="form-text text-muted">Даты опциональны</small>
                </div>
                @error('documents.kasko')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="row">
            {{-- Доп. страховка --}}
            <div class="col-md-6 mb-3">
                <label for="additional_insurance_file" class="form-label">Дополнительное страхование</label>
                <input type="file" class="form-control" 
                       id="additional_insurance_file" name="documents[additional_insurance][file]">
            </div>
            
            {{-- Автотека --}}
            <div class="col-md-6 mb-3">
                <label for="autoteka_file" class="form-label">Автотека</label>
                <input type="file" class="form-control" 
                       id="autoteka_file" name="documents[autoteka][file]">
            </div>
        </div>
        
        <div class="row">
            {{-- Сервисные документы --}}
            <div class="col-md-6 mb-3">
                <label for="service_docs_file" class="form-label">Сервисные документы</label>
                <input type="file" class="form-control" 
                       id="service_docs_file" name="documents[service_docs][file]">
            </div>
            
            {{-- Другие документы --}}
            <div class="col-md-6 mb-3">
                <label for="other_file" class="form-label">Другие документы</label>
                <input type="file" class="form-control" 
                       id="other_file" name="documents[other][file]">
                <div class="mt-2">
                    <textarea class="form-control form-control-sm" rows="2" 
                              placeholder="Примечания к документу" name="documents[other][notes]"></textarea>
                </div>
            </div>
        </div>
    </div>
</div>
                        
                        
                        
                        
                        
                        
                        
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check-circle me-1"></i>Сохранить
                            </button>
                            <a href="{{ route('cars.index') }}" class="btn btn-secondary">
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
        // Маска для VIN номера (17 символов)
        const vinInput = document.getElementById('vin');
        if (vinInput) {
            vinInput.addEventListener('input', function() {
                this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 17);
            });
        }
        
        // Маска для госномера
        const plateInput = document.getElementById('license_plate');
        if (plateInput) {
            plateInput.addEventListener('input', function() {
                this.value = this.value.toUpperCase().replace(/[^А-ЯA-Z0-9]/g, '');
            });
        }
    });
</script>




<script>
// Форматирование цены с пробелами
const priceInput = document.getElementById('price');
const priceOriginal = document.getElementById('price_original');

if (priceInput) {
    // Функция для форматирования числа с пробелами
    function formatPrice(value) {
        // Удаляем все нецифровые символы, кроме точки и запятой
        let cleanValue = value.replace(/[^\d,.]/g, '');
        
        // Заменяем запятую на точку для десятичных
        cleanValue = cleanValue.replace(',', '.');
        
        // Удаляем все лишние точки, оставляем только первую
        const parts = cleanValue.split('.');
        if (parts.length > 1) {
            cleanValue = parts[0] + '.' + parts.slice(1).join('');
        }
        
        // Разделяем целую часть пробелами
        const [integerPart, decimalPart] = cleanValue.split('.');
        if (!integerPart) return '';
        
        const formattedInteger = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        
        // Собираем обратно с десятичной частью, если она есть
        let result = formattedInteger;
        if (decimalPart !== undefined) {
            result += '.' + decimalPart;
        }
        
        return result;
    }
    
    // Функция для получения числового значения
    function getNumericValue(value) {
        let cleanValue = value.replace(/[^\d,.]/g, '');
        cleanValue = cleanValue.replace(',', '.');
        
        // Удаляем все лишние точки
        const parts = cleanValue.split('.');
        if (parts.length > 1) {
            cleanValue = parts[0] + '.' + parts.slice(1).join('');
        }
        
        // Если значение пустое, возвращаем пустую строку
        if (!cleanValue) return '';
        
        // Преобразуем в число
        const numValue = parseFloat(cleanValue);
        return isNaN(numValue) ? '' : numValue.toString();
    }
    
    // Обработчик ввода
    priceInput.addEventListener('input', function(e) {
        // Сохраняем позицию курсора
        const cursorPosition = e.target.selectionStart;
        const originalValue = e.target.value;
        
        // Форматируем значение
        const formattedValue = formatPrice(originalValue);
        
        // Устанавливаем отформатированное значение
        e.target.value = formattedValue;
        
        // Получаем числовое значение для hidden поля
        const numericValue = getNumericValue(formattedValue);
        if (priceOriginal) {
            priceOriginal.value = numericValue;
        }
        
        // Корректируем позицию курсора
        const formattedLength = formattedValue.length;
        const originalLength = originalValue.length;
        const diff = formattedLength - originalLength;
        
        // Если курсор был не в конце, корректируем его позицию
        if (cursorPosition < originalLength) {
            let newPosition = cursorPosition;
            
            // Ищем новую позицию курсора в отформатированной строке
            let originalIndex = 0;
            let formattedIndex = 0;
            
            while (formattedIndex < formattedLength && originalIndex < cursorPosition) {
                if (formattedValue[formattedIndex] === ' ' && originalValue[originalIndex] !== ' ') {
                    formattedIndex++;
                } else {
                    formattedIndex++;
                    originalIndex++;
                }
            }
            
            newPosition = formattedIndex;
            
            // Устанавливаем курсор
            e.target.setSelectionRange(newPosition, newPosition);
        }
    });
    
    // Обработчик фокуса
    priceInput.addEventListener('focus', function() {
        // При фокусе убираем пробелы для удобства редактирования
        const numericValue = getNumericValue(this.value);
        this.value = numericValue.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        
        // Перемещаем курсор в конец
        setTimeout(() => {
            this.setSelectionRange(this.value.length, this.value.length);
        }, 0);
    });
    
    // Обработчик потери фокуса
    priceInput.addEventListener('blur', function() {
        // При потере фокуса форматируем с пробелами
        const formattedValue = formatPrice(this.value);
        this.value = formattedValue;
        
        // Обновляем hidden поле
        const numericValue = getNumericValue(formattedValue);
        if (priceOriginal) {
            priceOriginal.value = numericValue;
        }
    });
    
    // Обработчик копирования/вставки
    priceInput.addEventListener('paste', function(e) {
        // Даем браузеру вставить текст
        setTimeout(() => {
            const formattedValue = formatPrice(this.value);
            this.value = formattedValue;
            
            const numericValue = getNumericValue(formattedValue);
            if (priceOriginal) {
                priceOriginal.value = numericValue;
            }
        }, 0);
    });
}
</script>



@endsection