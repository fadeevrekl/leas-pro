@extends('layouts.app')

@section('title', 'Редактировать клиента')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary-lt">
                    <h3 class="m-0"><i class="fas fa-user-gear me-2"></i>Редактировать клиента: {{ $client->last_name }} {{ $client->first_name }} {{ $client->middle_name }}</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('clients.update', $client) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="last_name" class="form-label">Фамилия *</label>
                                    <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                           id="last_name" name="last_name" 
                                           value="{{ old('last_name', $client->last_name) }}" required>
                                    @error('last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="first_name" class="form-label">Имя *</label>
                                    <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                           id="first_name" name="first_name" 
                                           value="{{ old('first_name', $client->first_name) }}" required>
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="middle_name" class="form-label">Отчество</label>
                                    <input type="text" class="form-control @error('middle_name') is-invalid @enderror" 
                                           id="middle_name" name="middle_name" 
                                           value="{{ old('middle_name', $client->middle_name) }}">
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
                                           value="{{ old('passport_series', $client->passport_series) }}" required>
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
                                           value="{{ old('passport_number', $client->passport_number) }}" required>
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
                                           value="{{ old('passport_issued_by', $client->passport_issued_by) }}" required>
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
                                           value="{{ old('passport_issued_date', $client->passport_issued_date->format('Y-m-d')) }}" required>
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
                                           value="{{ old('passport_division_code', $client->passport_division_code) }}" required>
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
                                           value="{{ old('drivers_license', $client->drivers_license) }}">
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
                                           value="{{ old('phone', $client->phone) }}" required>
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
                                           value="{{ old('additional_phone', $client->additional_phone) }}">
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
                                      id="registration_address" name="registration_address" rows="2" required>{{ old('registration_address', $client->registration_address) }}</textarea>
                            @error('registration_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="same_as_registration">
                            <label class="form-check-label" for="same_as_registration">
                                Совпадает с местом регистрации
                            </label>
                        </div>
                        
                        <div class="mb-3">
                            <label for="residential_address" class="form-label">Адрес проживания *</label>
                            <textarea class="form-control @error('residential_address') is-invalid @enderror" 
                                      id="residential_address" name="residential_address" rows="2" required>{{ old('residential_address', $client->residential_address) }}</textarea>
                            @error('residential_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="guarantor" class="form-label">Поручитель</label>
                            <input type="text" class="form-control @error('guarantor') is-invalid @enderror" 
                                   id="guarantor" name="guarantor" 
                                   value="{{ old('guarantor', $client->guarantor) }}">
                            @error('guarantor')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Заметки</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3">{{ old('notes', $client->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check-circle me-1"></i>Сохранить изменения
                            </button>
                            <a href="{{ route('clients.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times-circle me-1"></i>Отмена
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection