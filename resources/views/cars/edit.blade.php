@extends('layouts.app')

@section('title', 'Редактировать автомобиль')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-car me-2"></i>Редактировать автомобиль: {{ $car->brand }} {{ $car->model }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('cars.update', $car) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="brand" class="form-label">Марка *</label>
                                    <input type="text" class="form-control @error('brand') is-invalid @enderror" 
                                           id="brand" name="brand" value="{{ old('brand', $car->brand) }}" required>
                                    @error('brand')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="model" class="form-label">Модель *</label>
                                    <input type="text" class="form-control @error('model') is-invalid @enderror" 
                                           id="model" name="model" value="{{ old('model', $car->model) }}" required>
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
                    $startYear = 1990;
                @endphp
                @for($y = $currentYear; $y >= $startYear; $y--)
                    <option value="{{ $y }}" {{ old('year', $car->year) == $y ? 'selected' : '' }}>
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
                                           id="color" name="color" value="{{ old('color', $car->color) }}" required>
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
                                           value="{{ old('vin', $car->vin) }}" required>
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
                                           value="{{ old('license_plate', $car->license_plate) }}" required>
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
                                           value="{{ old('mileage', $car->mileage) }}" required>
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
                                            <option value="{{ $key }}" {{ old('fuel_type', $car->fuel_type) == $key ? 'selected' : '' }}>
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
                                    <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                           id="price" name="price" min="0" step="0.01" 
                                           value="{{ old('price', $car->price) }}" required>
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
                                            <option value="{{ $key }}" {{ old('status', $car->status) == $key ? 'selected' : '' }}>
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
              <!-- Аналогично заменяем текстовое поле на выпадающий список: -->
<div class="col-md-6">
    <div class="mb-3">
        <label for="investor_id" class="form-label">Инвестор *</label>
        <select class="form-select @error('investor_id') is-invalid @enderror" 
                id="investor_id" name="investor_id" required>
            <option value="">Выберите инвестора</option>
            @foreach($investors as $investor)
                <option value="{{ $investor->id }}" {{ old('investor_id', $car->investor_id) == $investor->id ? 'selected' : '' }}>
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
                <option value="{{ $manager->id }}" {{ old('manager_id', $car->manager_id) == $manager->id ? 'selected' : '' }}>
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
                                           value="{{ old('gps_tracker_id', $car->gps_tracker_id) }}">
                                    @error('gps_tracker_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Заметки</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3">{{ old('notes', $car->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check-circle me-1"></i>Сохранить изменения
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


@if(!$car->canBeEdited())
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle-fill me-2"></i>
        <strong>Внимание!</strong> Этот автомобиль имеет статус "{{ $car->status_text }}".
        Редактирование основных данных недоступно. Можно только просматривать информацию.
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Делаем все поля формы только для чтения
            const form = document.querySelector('form');
            if (form) {
                const inputs = form.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    input.disabled = true;
                });
                
                // Делаем кнопку сохранения неактивной
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="bi bi-ban me-1"></i>Редактирование заблокировано';
                }
            }
        });
    </script>
@endif



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
@endsection