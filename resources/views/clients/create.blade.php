
@extends('layouts.app')

@section('title', 'Добавить клиента')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-green-lt">
                    <h2 class="m-0"><i class="fas fa-user-plus me-2"></i>Добавить нового клиента</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('clients.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="last_name" class="form-label">Фамилия *</label>
                                    <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                           id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                                    @error('last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="first_name" class="form-label">Имя *</label>
                                    <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                           id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="middle_name" class="form-label">Отчество</label>
                                    <input type="text" class="form-control @error('middle_name') is-invalid @enderror" 
                                           id="middle_name" name="middle_name" value="{{ old('middle_name') }}">
                                    @error('middle_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="passport_series" class="form-label">Серия паспорта *</label>
                                    <input type="text" class="form-control @error('passport_series') is-invalid @enderror" 
                                           id="passport_series" name="passport_series" maxlength="4" 
                                           value="{{ old('passport_series') }}" required>
                                    @error('passport_series')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="passport_number" class="form-label">Номер паспорта *</label>
                                    <input type="text" class="form-control @error('passport_number') is-invalid @enderror" 
                                           id="passport_number" name="passport_number" maxlength="6" 
                                           value="{{ old('passport_number') }}" required>
                                    @error('passport_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="passport_issued_by" class="form-label">Кем выдан *</label>
                                    <input type="text" class="form-control @error('passport_issued_by') is-invalid @enderror" 
                                           id="passport_issued_by" name="passport_issued_by" 
                                           value="{{ old('passport_issued_by') }}" required>
                                    @error('passport_issued_by')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="passport_issued_date" class="form-label">Дата выдачи *</label>
                                    <input type="date" class="form-control @error('passport_issued_date') is-invalid @enderror" 
                                           id="passport_issued_date" name="passport_issued_date" 
                                           value="{{ old('passport_issued_date') }}" required>
                                    @error('passport_issued_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                         <div class="col-md-3">
    <div class="mb-3">
        <label for="passport_division_code" class="form-label">Код подразделения *</label>
        <input type="text" class="form-control @error('passport_division_code') is-invalid @enderror" 
               id="passport_division_code" name="passport_division_code" 
               placeholder="123-456"
               value="{{ old('passport_division_code') }}" required>
        <small class="form-text text-muted">Формат: XXX-XXX</small>
        @error('passport_division_code')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="drivers_license" class="form-label">Водительское удостоверение</label>
                                    <input type="text" class="form-control @error('drivers_license') is-invalid @enderror" 
                                           id="drivers_license" name="drivers_license" 
                                           value="{{ old('drivers_license') }}">
                                    @error('drivers_license')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                       <div class="col-md-6">
    <div class="mb-3">
        <label for="phone" class="form-label">Телефон *</label>
        <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
               id="phone" name="phone" placeholder="+7 (___) ___-__-__"
               value="{{ old('phone') }}" required>
        <small class="form-text text-muted">Формат: +7 XXX XXX-XX-XX</small>
        @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="col-md-6">
    <div class="mb-3">
        <label for="additional_phone" class="form-label">Дополнительный телефон</label>
        <input type="tel" class="form-control @error('additional_phone') is-invalid @enderror" 
               id="additional_phone" name="additional_phone" placeholder="+7 (___) ___-__-__"
               value="{{ old('additional_phone') }}">
        <small class="form-text text-muted">Формат: +7 XXX XXX-XX-XX</small>
        @error('additional_phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="registration_address" class="form-label">Адрес регистрации *</label>
                            <textarea class="form-control @error('registration_address') is-invalid @enderror" 
                                      id="registration_address" name="registration_address" rows="2" required>{{ old('registration_address') }}</textarea>
                            @error('registration_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="residential_address" class="form-label">Адрес проживания *</label>
                                                    <div class="mb-3 form-check">
    <input type="checkbox" class="form-check-input" id="same_as_registration">
    <label class="form-check-label" for="same_as_registration">
        Совпадает с местом регистрации
    </label>
</div>
                            <textarea class="form-control @error('residential_address') is-invalid @enderror" 
                                      id="residential_address" name="residential_address" rows="2" required>{{ old('residential_address') }}</textarea>
                            @error('residential_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="guarantor" class="form-label">Поручитель</label>
                            <input type="text" class="form-control @error('guarantor') is-invalid @enderror" 
                                   id="guarantor" name="guarantor" value="{{ old('guarantor') }}">
                            @error('guarantor')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
						
						
												<!-- Документы клиента -->
<div class="card mb-4">
    <div class="card-header bg-primary-lt">
         <h2 class="m-0"><i class="fas fa-folder-open me-2"></i>Документы клиента</h2>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label">Паспорт (основная страница) *</label>
            <input type="file" name="passport_main" class="form-control" accept="image/*,.pdf" required>
            <small class="text-muted">Фото или скан страницы с фото</small>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Паспорт (прописка) *</label>
            <input type="file" name="passport_registration" class="form-control" accept="image/*,.pdf" required>
            <small class="text-muted">Фото или скан страницы с регистрацией</small>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Водительское удостоверение</label>
            <input type="file" name="drivers_license_file" class="form-control" accept="image/*,.pdf">
            <small class="text-muted">Фото или скан ВУ (если есть)</small>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Дополнительные документы</label>
            <input type="file" name="additional_documents[]" class="form-control" accept="image/*,.pdf" multiple>
            <small class="text-muted">Можно выбрать несколько файлов</small>
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
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Сохранить</button>
                            <a href="{{ route('clients.index') }}" class="btn btn-secondary">Отмена</a>
                        </div>
						
						
						

						
						
						
						
						
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Проверяем, совпадают ли адреса при загрузке формы редактирования
        const regAddress = document.getElementById('registration_address').value;
        const resAddress = document.getElementById('residential_address').value;
        const sameCheckbox = document.getElementById('same_as_registration');
        
        if (regAddress === resAddress && regAddress !== '') {
            sameCheckbox.checked = true;
            document.getElementById('residential_address').readOnly = true;
            document.getElementById('residential_address').classList.add('bg-light');
        }
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. МАСКА ДЛЯ КОДА ПОДРАЗДЕЛЕНИЯ (XXX-XXX)
    const divisionCodeInput = document.getElementById('passport_division_code');
    if (divisionCodeInput) {
        divisionCodeInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // Удаляем все не-цифры
            let formatted = '';
            
            if (value.length > 0) {
                formatted = value.substring(0, 3);
                if (value.length > 3) {
                    formatted += '-' + value.substring(3, 6);
                }
            }
            
            e.target.value = formatted;
        });
        
        // Добавляем placeholder при фокусе
        divisionCodeInput.addEventListener('focus', function(e) {
            if (!e.target.value) {
                e.target.placeholder = '123-456';
            }
        });
        
        divisionCodeInput.addEventListener('blur', function(e) {
            if (!e.target.value) {
                e.target.placeholder = '';
            }
        });
    }

    // 2. МАСКА ДЛЯ ТЕЛЕФОНОВ
    function phoneMask(input) {
        if (!input) return;
        
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            let formatted = '';
            
            if (value.length > 0) {
                formatted = '+7 ';
                if (value.length > 1) {
                    formatted += '(' + value.substring(1, 4);
                }
                if (value.length > 4) {
                    formatted += ') ' + value.substring(4, 7);
                }
                if (value.length > 7) {
                    formatted += '-' + value.substring(7, 9);
                }
                if (value.length > 9) {
                    formatted += '-' + value.substring(9, 11);
                }
            }
            
            e.target.value = formatted;
        });
    }
    
    // Применяем маску к обоим полям телефона
    phoneMask(document.getElementById('phone'));
    phoneMask(document.getElementById('additional_phone'));

    // 3. ЧЕКБОКС "СОВПАДАЕТ С АДРЕСОМ РЕГИСТРАЦИИ"
    const sameAddressCheckbox = document.getElementById('same_as_registration');
    const registrationAddress = document.getElementById('registration_address');
    const residentialAddress = document.getElementById('residential_address');
    
    if (sameAddressCheckbox && registrationAddress && residentialAddress) {
        // Функция для синхронизации адресов
        function syncAddresses() {
            if (sameAddressCheckbox.checked) {
                residentialAddress.value = registrationAddress.value;
                residentialAddress.readOnly = true;
                residentialAddress.classList.add('bg-light', 'text-muted');
            } else {
                residentialAddress.readOnly = false;
                residentialAddress.classList.remove('bg-light', 'text-muted');
            }
        }
        
        // Обработчик чекбокса
        sameAddressCheckbox.addEventListener('change', function() {
            syncAddresses();
        });
        
        // Синхронизируем при изменении адреса регистрации
        registrationAddress.addEventListener('input', function() {
            if (sameAddressCheckbox.checked) {
                residentialAddress.value = this.value;
            }
        });
        
        // Проверяем при загрузке, совпадают ли адреса
        const regAddress = registrationAddress.value;
        const resAddress = residentialAddress.value;
        
        if (regAddress === resAddress && regAddress !== '') {
            sameAddressCheckbox.checked = true;
            residentialAddress.readOnly = true;
            residentialAddress.classList.add('bg-light', 'text-muted');
        } else {
            // Если адреса разные, снимаем галочку
            sameAddressCheckbox.checked = false;
            syncAddresses(); // Вызываем для установки правильного состояния
        }
        
        // Инициализируем состояние
        syncAddresses();
    }

    // 4. ВАЛИДАЦИЯ СЕРИИ И НОМЕРА ПАСПОРТА
    const passportSeries = document.getElementById('passport_series');
    const passportNumber = document.getElementById('passport_number');
    
    if (passportSeries) {
        passportSeries.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '').substring(0, 4);
        });
    }
    
    if (passportNumber) {
        passportNumber.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '').substring(0, 6);
        });
    }

    // 5. ПРЕДВАРИТЕЛЬНЫЙ ПРОСМОТР ФАЙЛОВ (опционально)
    function setupFilePreview(inputName, previewId) {
        const input = document.querySelector(`input[name="${inputName}"]`);
        const preview = document.getElementById(previewId);
        
        if (input && preview) {
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                }
            });
        }
    }
});
</script>

@endsection
