@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header bg-red-lt">
        <h3 class="mb-0">Редактирование пользователя: {{ $user->name }}</h3>
    </div>
    
    <div class="card-body">
        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Имя пользователя *</label>
                    <input type="text" class="form-control" id="name" name="name" 
                           value="{{ old('name', $user->name) }}" required>
                    @error('name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Новый пароль</label>
                    <input type="password" class="form-control" id="password" name="password" 
                           minlength="6">
                    @error('password')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Оставьте пустым, если не нужно менять</small>
                </div>
                
     <div class="col-md-6 mb-3">
    <label class="form-label">Роль</label>
    <div class="form-control bg-light">
        @if($user->role === 'manager')
            <span class="badge bg-deal-draw">Менеджер</span>
        @elseif($user->role === 'investor')
            <span class="badge bg-deal-end">Инвестор</span>
        @endif
    </div>
    <!-- Скрытое поле для сохранения роли -->
    <input type="hidden" name="role" value="{{ $user->role }}">
</div>
                
                
            </div>
            
            
            @if($user->role === 'investor')
<div class="col-md-6 mb-3">
    <label for="commission_percent" class="form-label">Процент комиссии *</label>
    <div class="input-group">
<input type="number" min="0" max="100" 
       class="form-control" id="commission_percent" 
       name="commission_percent" 
       value="{{ old('commission_percent', $user->commission_percent ?? 10) }}">
        <span class="input-group-text">%</span>
    </div>
    @error('commission_percent')
        <div class="text-danger">{{ $message }}</div>
    @enderror
    <small class="form-text text-muted">
        Процент, который система удерживает от дохода инвестора
    </small>
</div>
@endif
            
            
            <div class="mb-3">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" 
                           value="1" {{ $user->is_active ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        Активный аккаунт
                    </label>
                </div>
                @error('is_active')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Сохранить изменения
                </button>
                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-secondary">
                    <i class="fas fa-times-circle me-1"></i> Отмена
                </a>
            </div>
        </form>
    </div>
</div>
@endsection