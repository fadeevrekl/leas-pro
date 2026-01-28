@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header bg-green-lt">
        <h3 class="mb-0">Добавить нового пользователя</h3>
    </div>
    
    <div class="card-body">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Имя пользователя *</label>
                    <input type="text" class="form-control" id="name" name="name" 
                           value="{{ old('name') }}" required>
                    @error('name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="{{ old('email') }}" required>
                    @error('email')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Пароль *</label>
                    <input type="password" class="form-control" id="password" name="password" 
                           required minlength="6">
                    @error('password')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Минимум 6 символов</small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="role" class="form-label">Роль *</label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="">Выберите роль</option>
                        <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>
                            Менеджер
                        </option>
                        <option value="investor" {{ old('role') == 'investor' ? 'selected' : '' }}>
                            Инвестор
                        </option>
                    </select>
                    @error('role')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
<!-- Поле для процента комиссии (только для инвесторов) -->
<div class="row">
    <div class="col-md-6 mb-3" id="commission-field" style="display: none;">
        <label for="commission_percent" class="form-label">Процент комиссии *</label>
        <div class="input-group">
<input type="number" min="0" max="100" 
       class="form-control @error('commission_percent') is-invalid @enderror" 
       id="commission_percent" 
       name="commission_percent" 
       value="{{ old('commission_percent', 10) }}" 
       required>
            <span class="input-group-text">%</span>
        </div>
        @error('commission_percent')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
        <small class="form-text text-muted">
            Процент, который система удерживает от дохода инвестора (от 0 до 100)
        </small>
    </div>
</div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Сохранить
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times-circle me-2"></i> Отмена
                </a>
            </div>
        </form>
    </div>
</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const commissionField = document.getElementById('commission-field');
    
    function toggleCommissionField() {
        if (roleSelect.value === 'investor') {
            commissionField.style.display = 'block';
            document.getElementById('commission_percent').required = true;
        } else {
            commissionField.style.display = 'none';
            document.getElementById('commission_percent').required = false;
        }
    }
    
    // При изменении роли
    roleSelect.addEventListener('change', toggleCommissionField);
    
    // При загрузке страницы
    toggleCommissionField();
});
</script>
@endsection
@endsection