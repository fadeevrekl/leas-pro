@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header bg-primary-lt d-flex justify-content-between align-items-center">
        <h3 class="m-0">Управление пользователями</h3>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Добавить пользователя
        </a>
    </div>
    
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Имя</th>
                        <th>Email</th>
                        <th>Роль</th>
                        <th>Статус</th>
                        <th>Дата создания</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->role === 'manager')
                                <span class="badge bg-deal-draw">Менеджер</span>
                            @elseif($user->role === 'investor')
                                <span class="badge bg-deal-end">Инвестор</span>
                            @else
                                <span class="badge bg-deal-active">{{ $user->role }}</span>
                            @endif
                        </td>
                        <td>
                            @if($user->is_active)
                                <span class="badge bg-free">Активен</span>
                            @else
                                <span class="badge deal-overdue">Неактивен</span>
                            @endif
                        </td>
                        <td>{{ $user->created_at->format('d.m.Y H:i') }}</td>
                        <td>
                         
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-md btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-md btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-md btn-danger" 
                                            onclick="return confirm('Удалить пользователя?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                         
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">Пользователи не найдены</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
