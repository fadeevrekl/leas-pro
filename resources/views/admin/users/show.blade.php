@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Просмотр пользователя</h5>
        <div>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Редактировать
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Назад
            </a>
        </div>
    </div>
    
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Основная информация</h6>
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">ID:</th>
                        <td>{{ $user->id }}</td>
                    </tr>
                    <tr>
                        <th>Имя:</th>
                        <td>{{ $user->name }}</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <th>Роль:</th>
                        <td>
                            @if($user->role === 'manager')
                                <span class="badge bg-primary">Менеджер</span>
                            @elseif($user->role === 'investor')
                                <span class="badge bg-success">Инвестор</span>
                            @endif
                        </td>
                    </tr>
                    @if($user->role === 'investor')
<tr>
    <th>Процент комиссии:</th>
    <td>{{ $user->commission_percent ?? 'не указан' }}%</td>
</tr>
@endif
                    <tr>
                        <th>Статус:</th>
                        <td>
                            @if($user->is_active)
                                <span class="badge bg-success">Активен</span>
                            @else
                                <span class="badge bg-danger">Неактивен</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="col-md-6">
                <h6>Дополнительная информация</h6>
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">Дата создания:</th>
                        <td>{{ $user->created_at->format('d.m.Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Последнее обновление:</th>
                        <td>{{ $user->updated_at->format('d.m.Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="mt-4">
            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" 
                  onsubmit="return confirm('Удалить пользователя?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Удалить пользователя
                </button>
            </form>
        </div>
    </div>
</div>
@endsection