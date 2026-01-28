@extends('layouts.app')

@section('title', 'Клиенты')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Клиенты</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('clients.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Добавить клиента
            </a>
        </div>
    </div>

    <!-- Фильтры -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('clients.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Поиск по ФИО</label>
                    <input type="text" 
                           class="form-control" 
                           id="search" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Введите фамилию, имя или отчество">
                </div>
                
                <div class="col-md-3">
                    <label for="status" class="form-label">Статус</label>
                    <select class="form-select" id="status" name="status">
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" {{ request('status', 'all') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>Применить
                    </button>
                    <a href="{{ route('clients.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo me-1"></i>Сбросить
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Таблица клиентов -->
    <div class="card">
        <div class="card-body">
            @if($clients->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>ФИО</th>
                                <th>Телефон</th>
                                <th>Статус</th>
                                
                                <th class="text-center">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clients as $client)
                            <tr>
                                <td>{{ $client->id }}</td>
                                <td>
                                    <strong>{{ $client->last_name }} {{ $client->first_name }} {{ $client->middle_name }}</strong>
                                </td>
                                <td>
                                    <div>{{ $client->phone }}</div>
                                    @if($client->additional_phone)
                                        <small class="text-muted">{{ $client->additional_phone }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $client->status_color }}">
                                        {{ $client->status_text }}
                                    </span>
                                </td>
    
                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        <!-- Кнопка просмотра -->
                                        <a href="{{ route('clients.show', $client) }}" 
                                           class="btn btn-md btn-outline-info"
                                           title="Просмотр"
                                           data-bs-toggle="tooltip">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <!-- Кнопка редактирования -->
                                        <a href="{{ route('clients.edit', $client) }}" 
                                           class="btn btn-md btn-outline-warning"
                                           title="Редактировать"
                                           data-bs-toggle="tooltip">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <!-- Кнопка удаления (опционально) -->
                                        @if($client->canBeDeleted())
                                            <button type="button" 
                                                    class="btn btn-md btn-outline-danger"
                                                    title="Удалить"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal{{ $client->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            
                                            <!-- Модальное окно удаления -->
                                            <div class="modal fade" id="deleteModal{{ $client->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Подтверждение удаления</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form action="{{ route('clients.destroy', $client) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <div class="modal-body">
                                                                <p>Вы действительно хотите удалить клиента <strong>{{ $client->full_name }}</strong>?</p>
                                                                <p class="text-danger">Это действие нельзя отменить.</p>
                                                                
                                                                <div class="mb-3">
                                                                    <label for="confirmation_text{{ $client->id }}" class="form-label">
                                                                        Для подтверждения введите <strong>УДАЛИТЬ</strong>:
                                                                    </label>
                                                                    <input type="text" 
                                                                           class="form-control" 
                                                                           id="confirmation_text{{ $client->id }}" 
                                                                           name="confirmation_text" 
                                                                           required>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                                    <i class="fas fa-times"></i> Отмена
                                                                </button>
                                                                <button type="submit" class="btn btn-danger">
                                                                    <i class="fas fa-trash"></i> Удалить
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Пагинация -->
                <div class="mt-3">
                    {{ $clients->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <p class="text-muted">Клиентов пока нет</p>
                    <a href="{{ route('clients.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Добавить первого клиента
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Активация подсказок Bootstrap
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush

@endsection