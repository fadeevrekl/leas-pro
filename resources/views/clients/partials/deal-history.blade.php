<!-- Модальное окно истории сделок -->
<div class="modal fade" id="dealHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="bg-primary-lt p-3">
                <h3 class="m-0">
                    <i class="fas fa-file-contract me-2"></i>
                    История сделок клиента: {{ $client->full_name }}
                </h3>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Информация о клиенте -->
                <div class="p-3 bg-light">
                    <div class="row">
                        <div class="col-md-4">
                            <strong>ФИО:</strong> {{ $client->full_name }}
                        </div>
                        <div class="col-md-4">
                            <strong>Телефон:</strong> {{ $client->phone }}
                        </div>
                        <div class="col-md-4">
                            <strong>Статус:</strong> 
                            <span class="badge bg-{{ $client->status_color }}">
                                {{ $client->status_text }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Статистика -->
                <div class="p-3">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-muted mb-1">Всего сделок</h6>
                                    <h4 class="mb-0">{{ $client->deals->count() }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-success mb-1">Активные</h6>
                                    <h4 class="mb-0 text-success">
                                        {{ $client->deals()->where('status', \App\Models\Deal::STATUS_ACTIVE)->count() }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-info mb-1">Черновики</h6>
                                    <h4 class="mb-0 text-info">
                                        {{ $client->deals()->where('status', \App\Models\Deal::STATUS_DRAFT)->count() }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-warning mb-1">Завершённые</h6>
                                    <h4 class="mb-0 text-warning">
                                        {{ $client->deals()->where('status', \App\Models\Deal::STATUS_COMPLETED)->count() }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Таблица сделок -->
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>№ сделки</th>
                                <th>Дата создания</th>
                                <th>Автомобиль</th>
                                <th>Менеджер</th>
                                <th>Тип</th>
                                <th>Сумма</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody id="dealHistoryTableBody">
                            @if($client->deals->count() > 0)
                                @foreach($client->deals as $deal)
                                <tr>
                                    <td>
                                        <strong>{{ $deal->deal_number }}</strong>
                                    </td>
                                    <td>
                                        {{ $deal->created_at->format('d.m.Y') }}
                                    </td>
                                    <td>
                                        @if($deal->car)
                                            {{ $deal->car->brand }} {{ $deal->car->model }}
                                            @if($deal->car->license_plate)
                                                <br><small class="text-muted">{{ $deal->car->license_plate }}</small>
                                            @endif
                                        @else
                                            <span class="text-danger">Авто удалён</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $deal->manager->name ?? 'Не указан' }}
                                    </td>
                                    <td>
                                        <span class="badge bg-deal-draw">
                                            {{ $deal->deal_type_text }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ number_format($deal->total_amount, 2, '.', ' ') }} ₽</strong>
                                        <br>
                                        <small class="text-muted">
                                            Оплачено: {{ number_format($deal->total_paid, 2, '.', ' ') }} ₽
                                        </small>
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'draft' => 'deal-draw',
                                                'active' => 'free',
                                                'completed' => 'deal-end',
                                                'cancelled' => 'deal-overdue',
                                                'overdue' => 'deal-overdue'
                                            ];
                                            $color = $statusColors[$deal->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $color }}">
                                            {{ $deal->status_text }}
                                        </span>
                                    </td>
                                    <td>
                                        
                                            <a href="{{ route('deals.show', $deal) }}" 
                                               class="btn btn-outline-primary"
                                               title="Просмотр сделки"
                                               target="_blank">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if(auth()->user()->isAdmin() || (auth()->user()->isManager() && $deal->manager_id == auth()->id()))
                                                <a href="{{ route('deals.edit', $deal) }}" 
                                                   class="btn btn-outline-warning"
                                                   title="Редактировать"
                                                   target="_blank">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                       
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-folder-x display-6"></i>
                                            <h5 class="mt-3">Сделок не найдено</h5>
                                            <p>У этого клиента ещё нет сделок.</p>
                                            @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                                                <a href="{{ route('deals.create', ['client_id' => $client->id]) }}" 
                                                   class="btn btn-primary mt-2">
                                                    <i class="fas fa-plus-circle me-1"></i>Создать первую сделку
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <div class="d-flex justify-content-between w-100">
                    <div>
                        <small class="text-muted">
                            Последнее обновление: {{ now()->format('d.m.Y H:i') }}
                        </small>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times-circle me-1"></i>Закрыть
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Стили для модального окна -->
<style>
#dealHistoryModal .modal-xl {
    max-width: 1200px;
}

#dealHistoryModal .table th {
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    color: #6c757d;
    border-bottom: 2px solid #dee2e6;
}

#dealHistoryModal .table td {
    vertical-align: middle;
    font-size: 0.9rem;
}

#dealHistoryModal .badge {
    font-size: 0.75rem;
    padding: 0.35em 0.65em;
}

#dealHistoryModal .btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}

#dealHistoryModal .table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}
</style>