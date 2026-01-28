@extends('layouts.app')

@section('title', 'Сделки')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2><i class="fas fa-file-contract me-2"></i>Сделки</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('deals.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle me-1"></i>Создать сделку
            </a>
        </div>
    </div>

    <!-- Панель фильтров и поиска -->
    <div class="card mb-4">
        <div class="card-header bg-light cursor-pointer" 
             data-bs-toggle="collapse" 
             data-bs-target="#filterPanel"
             aria-expanded="false" 
             aria-controls="filterPanel">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-filter me-2"></i>Фильтры и поиск
                </h5>
                <span class="badge bg-primary">
                    <i class="fas fa-chevron-down"></i>
                </span>
            </div>
        </div>
        
        <div class="collapse" id="filterPanel">
            <div class="card-body">
                <form method="GET" action="{{ route('deals.index') }}" id="filterForm">
                    
                    <!-- Строка 1: Основные фильтры -->
                    <div class="row">
                        <!-- Поиск -->
                        <div class="col-md-4 mb-3">
                            <label for="search" class="form-label">Поиск</label>
                            <div class="input-group">
                                <input type="text" 
                                       class="form-control" 
                                       id="search" 
                                       name="search" 
                                       placeholder="Номер, клиент, авто, VIN..."
                                       value="{{ request('search') }}">
                                <button class="btn btn-outline-secondary" type="button" onclick="clearSearch()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Статус -->
                        <div class="col-md-4 mb-3">
                            <label for="status" class="form-label">Статус</label>
                            <select class="form-select" id="status" name="status">
                                @foreach($statuses as $value => $label)
                                    <option value="{{ $value }}" 
                                        {{ request('status') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Тип сделки -->
                        <div class="col-md-4 mb-3">
                            <label for="deal_type" class="form-label">Тип сделки</label>
                            <select class="form-select" id="deal_type" name="deal_type">
                                @foreach($dealTypes as $value => $label)
                                    <option value="{{ $value }}" 
                                        {{ request('deal_type') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <!-- Строка 2: Дополнительные фильтры -->
                    <div class="row">
                        <!-- Период оплаты -->
                        <div class="col-md-4 mb-3">
                            <label for="payment_period" class="form-label">Период оплаты</label>
                            <select class="form-select" id="payment_period" name="payment_period">
                                @foreach($paymentPeriods as $value => $label)
                                    <option value="{{ $value }}" 
                                        {{ request('payment_period') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Фильтр по менеджеру - только для админа -->
                        @if(auth()->user()->isAdmin())
                        <div class="col-md-4 mb-3">
                            <label for="manager_id" class="form-label">Менеджер</label>
                            <select class="form-select" id="manager_id" name="manager_id">
                                <option value="">Все менеджеры</option>
                                @foreach($managers as $manager)
                                    <option value="{{ $manager->id }}" 
                                        {{ request('manager_id') == $manager->id ? 'selected' : '' }}>
                                        {{ $manager->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @else
                            <input type="hidden" name="manager_id" value="{{ auth()->user()->isManager() ? auth()->id() : '' }}">
                        @endif
                        
                        <!-- Инвестор -->
                        <div class="col-md-4 mb-3">
                            <label for="investor_id" class="form-label">Инвестор</label>
                            <select class="form-select" id="investor_id" name="investor_id">
                                <option value="">Все инвесторы</option>
                                @foreach($investors as $investor)
                                    <option value="{{ $investor->id }}" 
                                        {{ request('investor_id') == $investor->id ? 'selected' : '' }}>
                                        {{ $investor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <!-- Строка 3: Фильтр по дате -->
                    <div class="row">
                        <!-- Начальная дата -->
                        <div class="col-md-3 mb-3">
                            <label for="start_date" class="form-label">Дата создания с</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="start_date" 
                                   name="start_date" 
                                   value="{{ request('start_date', date('Y-m-01')) }}">
                        </div>
                        
                        <!-- Конечная дата -->
                        <div class="col-md-3 mb-3">
                            <label for="end_date" class="form-label">по</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="end_date" 
                                   name="end_date" 
                                   value="{{ request('end_date', date('Y-m-d')) }}">
                        </div>
                        
                        <!-- Количество на странице -->
                        <div class="col-md-3 mb-3">
                            <label for="per_page" class="form-label">На странице</label>
                            <select class="form-select" id="per_page" name="per_page">
                                <option value="10" {{ request('per_page', 20) == 10 ? 'selected' : '' }}>10</option>
                                <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                                <option value="50" {{ request('per_page', 20) == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page', 20) == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>
                        
                        <!-- Кнопки действий -->
                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            <div class="btn-group w-100" role="group">
                                <button type="button" class="btn btn-primary" onclick="submitFilterForm()">
                                    <i class="fas fa-search me-1"></i>Применить
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="resetFilters()">
                                    <i class="fas fa-redo me-1"></i>Сбросить
                                </button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- Активные фильтры -->
    @if(request()->anyFilled(['status', 'deal_type', 'payment_period', 'manager_id', 'investor_id', 'start_date', 'end_date', 'search']))
    <div class="alert alert-info mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <strong>Активные фильтры:</strong>
                @php
                    $activeFilters = [];
                    if (request('status')) $activeFilters[] = 'Статус: ' . ($statuses[request('status')] ?? request('status'));
                    if (request('deal_type')) $activeFilters[] = 'Тип: ' . ($dealTypes[request('deal_type')] ?? request('deal_type'));
                    if (request('payment_period')) $activeFilters[] = 'Период: ' . ($paymentPeriods[request('payment_period')] ?? request('payment_period'));
                    if (request('manager_id')) {
                        $manager = $managers->firstWhere('id', request('manager_id'));
                        $activeFilters[] = 'Менеджер: ' . ($manager->name ?? request('manager_id'));
                    }
                    if (request('investor_id')) {
                        $investor = $investors->firstWhere('id', request('investor_id'));
                        $activeFilters[] = 'Инвестор: ' . ($investor->name ?? request('investor_id'));
                    }
                    if (request('start_date')) $activeFilters[] = 'С ' . request('start_date');
                    if (request('end_date')) $activeFilters[] = 'По ' . request('end_date');
                    if (request('search')) $activeFilters[] = 'Поиск: "' . request('search') . '"';
                @endphp
                {{ implode(', ', $activeFilters) }}
            </div>
            <div>
                <a href="{{ route('deals.index') }}" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-times me-1"></i>Очистить
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Таблица сделок -->
    <div class="card">
        <div class="card-body">
            @if($deals->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>№ сделки</th>
                                <th>Клиент</th>
                                <th>Автомобиль</th>
                                <th>Сумма</th>
                                <th>Статус</th>
                                <th>Дата оплаты</th>
                                <th class="text-center">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deals as $deal)
                            <tr>
                                <td>
                                    <strong>{{ $deal->deal_number }}</strong><br>
                                    <small class="text-muted">{{ $deal->deal_type_text }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('clients.show', $deal->client) }}" class="text-decoration-none">
                                        {{ $deal->client->last_name }} {{ $deal->client->first_name }}
                                    </a>
                                    @if($deal->client->middle_name)
                                        <br><small>{{ $deal->client->middle_name }}</small>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('cars.show', $deal->car) }}" class="text-decoration-none">
                                        {{ $deal->car->brand }} {{ $deal->car->model }}
                                    </a><br>
                                    <small class="text-muted">{{ $deal->car->license_plate }}</small>
                                </td>
                                <td>
                                    {{ number_format($deal->total_amount, 0, '', ' ') }} ₽<br>
                                    <small class="text-success">
                                        {{ number_format($deal->total_paid, 0, '', ' ') }} ₽ оплачено
                                    </small>
                                </td>
                                <td>
                                    @php
                                        // Определяем цвет статуса
                                        $statusColors = [
                                            'draft' => 'deal-draw',    // Серый для черновика
                                            'active' => 'deal-active',     // Зеленый для активной
                                            'completed' => 'deal-end',     // Синий для завершенной
                                            'cancelled' => 'danger',   // Красный для отмененной
                                            'overdue' => 'warning'     // Желтый для просроченной
                                        ];
                                        $statusColor = $statusColors[$deal->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $statusColor }}">
                                        {{ $deal->status_text }}
                                    </span>
                                    @if($deal->status === 'draft')
                                        <br><small class="text-muted">Договор не подписан</small>
                                    @endif
                                </td>
                                <td>
                                    @if($deal->status === 'active' && $deal->next_payment_due_date)
                                        <div class="mb-2">
                                            <strong>{{ $deal->next_payment_due_date->format('d.m.Y') }}</strong>
                                            <small class="text-muted ms-2">
                                                ({{ $deal->payment_period_text }})
                                            </small>
                                        </div>
                                        
                                        <!-- Градиентная полоска времени до платежа -->
                                        <div class="progress time-progress" 
                                             style="height: 10px; border-radius: 5px;"
                                             data-bs-toggle="tooltip" 
                                             title="{{ $deal->time_progress_tooltip }}">
                                            <div class="progress-bar {{ $deal->time_progress_color }}" 
                                                 role="progressbar"
                                                 style="width: {{ $deal->next_payment_progress }}%;
                                                        background: {{ $deal->time_progress_gradient }};
                                                        border-radius: 5px;"
                                                 aria-valuenow="{{ $deal->next_payment_progress }}"
                                                 aria-valuemin="0"
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                        
                                        <!-- Информация под полоской -->
                                        <div class="mt-1 d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                @if($deal->days_to_next_payment !== null)
                                                    @if($deal->days_to_next_payment > 0)
                                                        через {{ $deal->days_to_next_payment }} дн.
                                                    @elseif($deal->days_to_next_payment == 0)
                                                        <span class="text-warning fw-bold">сегодня</span>
                                                    @else
                                                        <span class="text-danger fw-bold">
                                                            просрочено на {{ abs($deal->days_to_next_payment) }} дн.
                                                        </span>
                                                    @endif
                                                @endif
                                            </small>
                                            
                                            <small class="text-muted">
                                                {{ round($deal->next_payment_progress, 0) }}%
                                            </small>
                                        </div>
                                        
                                        @if($deal->next_payment && $deal->days_to_next_payment !== null && $deal->days_to_next_payment <= 3)
                                            <div class="mt-1">
                                                <small class="text-danger">
                                                    <i class="fas fa-exclamation-triangle"></i> Скоро срок оплаты
                                                </small>
                                            </div>
                                        @endif
                                    @elseif($deal->status === 'overdue')
                                        <div class="mb-2">
                                            <strong class="text-danger">ПРОСРОЧКА</strong>
                                        </div>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar bg-danger"
                                                 role="progressbar"
                                                 style="width: 100%"
                                                 aria-valuenow="100"
                                                 aria-valuemin="0"
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                        <small class="text-danger">
                                            <i class="fas fa-exclamation-triangle-fill"></i> Требуется срочное внимание
                                        </small>
                                    @else
                                        <span class="text-muted">
                                            @if($deal->status === 'draft')
                                                Ожидается договор
                                            @elseif($deal->status === 'completed')
                                                Сделка завершена
                                            @elseif($deal->status === 'cancelled')
                                                Сделка отменена
                                            @else
                                                Нет активных платежей
                                            @endif
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <!-- Кнопка просмотра -->
                                        <a href="{{ route('deals.show', $deal) }}" 
                                           class="btn btn-md btn-outline-info"
                                           title="Просмотр"
                                           data-bs-toggle="tooltip">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <!-- Кнопка редактирования (скрыта для завершенных сделок) -->
                                        @if($deal->status !== 'completed')
                                            <a href="{{ route('deals.edit', $deal) }}" 
                                               class="btn btn-md btn-outline-warning"
                                               title="Редактировать"
                                               data-bs-toggle="tooltip">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        
                                        <!-- Кнопка удаления -->
                                        @if(auth()->user()->isAdmin())
                                            <button class="btn btn-md btn-outline-danger delete-deal-btn"
                                                    data-deal-id="{{ $deal->id }}"
                                                    data-deal-number="{{ $deal->deal_number }}"
                                                    data-client-name="{{ $deal->client->full_name }}"
                                                    data-deal-status="{{ $deal->status }}"
                                                    title="Удалить сделку"
                                                    data-bs-toggle="tooltip"
                                                    @if($deal->status === 'active' || $deal->status === 'overdue')
                                                        disabled
                                                        title="Невозможно удалить активную или просроченную сделку"
                                                    @endif>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                        
                                        <!-- Кнопка напоминания (только для активных сделок с ближайшим платежом) -->
                                        @if($deal->status === 'active' && $deal->next_payment && $deal->days_to_next_payment !== null && $deal->days_to_next_payment <= 2)
                                            <button class="btn btn-md btn-success" 
                                                    title="Напомнить о платеже"
                                                    data-bs-toggle="tooltip">
                                                <i class="fas fa-bell"></i>
                                            </button>
                                        @endif
                                        
                                        <!-- Кнопка загрузки договора (только для черновиков) -->
                                        @if($deal->status === 'draft')
                                            <a href="{{ route('deals.show', $deal) }}#contract-section" 
                                               class="btn btn-md btn-primary"
                                               title="Загрузить договор"
                                               data-bs-toggle="tooltip">
                                                <i class="fas fa-file-arrow-up"></i>
                                            </a>
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
                    {{ $deals->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-file-contract" style="font-size: 3rem; color: #6c757d;"></i>
                    </div>
                    <p class="text-muted">Сделок пока нет</p>
                    <a href="{{ route('deals.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-1"></i>Создать первую сделку
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Модальное окно для подтверждения удаления сделки -->
<div class="modal fade" id="deleteDealModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle-fill me-2"></i>
                    Подтверждение удаления сделки
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST" id="deleteDealForm">
                @csrf
                @method('DELETE')
                
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-octagon-fill me-2"></i>
                        <strong>Внимание! Это действие необратимо.</strong>
                    </div>
                    
                    <p>Вы собираетесь удалить сделку:</p>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title" id="dealNumberDisplay"></h5>
                            <p class="card-text mb-1" id="clientNameDisplay"></p>
                            <p class="card-text mb-0 text-muted small" id="dealStatusDisplay"></p>
                        </div>
                    </div>
                    
                    <!-- Предупреждение для активных сделок -->
                    <div class="alert alert-danger d-none" id="activeDealWarning">
                        <i class="fas fa-exclamation-triangle-fill me-2"></i>
                        <strong>Невозможно удалить активную сделку!</strong>
                        <p class="mb-0">Сначала завершите или отмените сделку.</p>
                    </div>
                    
                    <div class="alert alert-danger">
                        <h6><i class="fas fa-trash3 me-2"></i>При удалении сделки будут безвозвратно удалены:</h6>
                        <ul class="mb-0">
                            <li>Все связанные платежи</li>
                            <li>Загруженные документы</li>
                            <li>История взаимодействия</li>
                        </ul>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <label for="dealConfirmationText" class="form-label">
                            Для подтверждения удаления введите слово <strong>"УДАЛИТЬ"</strong> в поле ниже:
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="dealConfirmationText" 
                               name="confirmation_text"
                               placeholder="Введите УДАЛИТЬ"
                               required>
                        <div class="form-text text-danger">
                            <i class="bi bi-shield-exclamation me-1"></i>
                            Это действие невозможно отменить
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times-circle me-1"></i>Отмена
                    </button>
                    <button type="submit" class="btn btn-danger" id="confirmDeleteDealBtn" disabled>
                        <i class="fas fa-trash3 me-1"></i>Удалить сделку
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.cursor-pointer {
    cursor: pointer;
}

.card-header {
    transition: background-color 0.2s;
}

.card-header:hover {
    background-color: #f8f9fa !important;
}

.badge {
    transition: transform 0.3s;
}

[aria-expanded="true"] .badge {
    transform: rotate(180deg);
}

.btn-group .btn {
    padding: 0.375rem 0.75rem;
}

/* Стили для градиентной полоски */
.payment-progress {
    background-color: #e9ecef;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
}

.payment-progress .progress-bar {
    transition: width 0.6s ease, background 0.6s ease;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

/* Анимация для критических сроков */
@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

.progress-bar.bg-danger {
    animation: pulse 2s infinite;
}

/* Стили для подсказок */
.tooltip-inner {
    max-width: 300px;
    padding: 8px 12px;
    font-size: 12px;
}

/* Стили для разных периодов */
.progress-indicator {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-right: 5px;
}

.period-day { background-color: #dc3545; }
.period-week { background-color: #ffc107; }
.period-month { background-color: #28a745; }
</style>
@endpush

@push('scripts')
<script>
// Функции для работы с localStorage
function getFilterPanelState() {
    try {
        return localStorage.getItem('filterPanelState') || 'closed';
    } catch (e) {
        return 'closed';
    }
}

function setFilterPanelState(state) {
    try {
        localStorage.setItem('filterPanelState', state);
    } catch (e) {
        // Игнорируем ошибки localStorage
    }
}

// Основная инициализация
document.addEventListener('DOMContentLoaded', function() {
    const filterPanel = document.getElementById('filterPanel');
    const filterHeader = document.querySelector('[data-bs-target="#filterPanel"]');
    const filterIcon = filterHeader.querySelector('.fa-chevron-down');
    
    // Получаем сохраненное состояние
    const savedState = getFilterPanelState();
    
    // Если состояние 'open' и нет активных фильтров, открываем панель
    if (savedState === 'open') {
        // Небольшая задержка для плавности
        setTimeout(() => {
            const bsCollapse = new bootstrap.Collapse(filterPanel, {
                toggle: true
            });
            bsCollapse.show();
            
            // Обновляем иконку через небольшой таймаут
            setTimeout(() => {
                if (filterIcon) {
                    filterIcon.classList.replace('fa-chevron-down', 'fa-chevron-up');
                }
            }, 150);
        }, 50);
    }
    
    // Обработчик клика на заголовок
    filterHeader.addEventListener('click', function() {
        setTimeout(() => {
            const isExpanded = filterPanel.classList.contains('show');
            
            // Сохраняем состояние
            setFilterPanelState(isExpanded ? 'open' : 'closed');
            
            // Обновляем иконку
            if (filterIcon) {
                filterIcon.classList.toggle('fa-chevron-down', !isExpanded);
                filterIcon.classList.toggle('fa-chevron-up', isExpanded);
            }
        }, 100);
    });
    
    // Показываем индикатор активных фильтров на кнопке
    updateFilterIndicator();
    
    // Обработчики для кнопок удаления
    const deleteButtons = document.querySelectorAll('.delete-deal-btn');
    const deleteModal = document.getElementById('deleteDealModal');
    const deleteForm = document.getElementById('deleteDealForm');
    const dealNumberDisplay = document.getElementById('dealNumberDisplay');
    const clientNameDisplay = document.getElementById('clientNameDisplay');
    const dealStatusDisplay = document.getElementById('dealStatusDisplay');
    const activeDealWarning = document.getElementById('activeDealWarning');
    const confirmationInput = document.getElementById('dealConfirmationText');
    const confirmDeleteBtn = document.getElementById('confirmDeleteDealBtn');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const dealId = this.getAttribute('data-deal-id');
            const dealNumber = this.getAttribute('data-deal-number');
            const clientName = this.getAttribute('data-client-name');
            const dealStatus = this.getAttribute('data-deal-status');
            const isActive = this.disabled;
            
            // Устанавливаем данные в модальное окно
            dealNumberDisplay.textContent = `Сделка № ${dealNumber}`;
            clientNameDisplay.textContent = `Клиент: ${clientName}`;
            dealStatusDisplay.textContent = `Статус: ${dealStatus}`;
            
            // Устанавливаем action формы
deleteForm.action = `{{ url('deals') }}/${dealId}`;
            
            // Показываем/скрываем предупреждение для активных сделок
            if (isActive) {
                activeDealWarning.classList.remove('d-none');
                confirmDeleteBtn.disabled = true;
                confirmDeleteBtn.innerHTML = '<i class="bi bi-ban me-1"></i>Невозможно удалить';
            } else {
                activeDealWarning.classList.add('d-none');
                confirmDeleteBtn.disabled = true;
                confirmDeleteBtn.innerHTML = '<i class="fas fa-trash3 me-1"></i>Удалить сделку';
            }
            
            // Сбрасываем поле подтверждения
            if (confirmationInput) {
                confirmationInput.value = '';
                confirmationInput.classList.remove('is-valid', 'is-invalid');
            }
            
            // Открываем модальное окно
            const modal = new bootstrap.Modal(deleteModal);
            modal.show();
        });
    });
    
    // Проверка ввода подтверждения
    if (confirmationInput && confirmDeleteBtn) {
        confirmationInput.addEventListener('input', function() {
            const inputValue = this.value.trim().toUpperCase();
            const isActive = activeDealWarning.classList.contains('d-none') === false;
            
            if (inputValue === 'УДАЛИТЬ' && !isActive) {
                confirmDeleteBtn.disabled = false;
                confirmationInput.classList.remove('is-invalid');
                confirmationInput.classList.add('is-valid');
            } else {
                confirmDeleteBtn.disabled = true;
                confirmationInput.classList.remove('is-valid');
                
                if (inputValue !== '') {
                    confirmationInput.classList.add('is-invalid');
                } else {
                    confirmationInput.classList.remove('is-invalid');
                }
            }
        });
        
        // Предотвращение отправки формы по Enter
        confirmationInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });
    }
    
    // Обработка отправки формы
    if (deleteForm) {
        deleteForm.addEventListener('submit', function(e) {
            const inputValue = confirmationInput.value.trim().toUpperCase();
            
            if (inputValue !== 'УДАЛИТЬ') {
                e.preventDefault();
                confirmationInput.classList.add('is-invalid');
                confirmationInput.focus();
                
                // Показываем сообщение об ошибке
                if (!document.getElementById('confirmationDealError')) {
                    const errorDiv = document.createElement('div');
                    errorDiv.id = 'confirmationDealError';
                    errorDiv.className = 'invalid-feedback d-block mt-2';
                    errorDiv.innerHTML = '<i class="bi bi-exclamation-circle me-1"></i>Пожалуйста, введите слово "УДАЛИТЬ" для подтверждения';
                    confirmationInput.parentNode.appendChild(errorDiv);
                }
                
                return false;
            }
            
            // Дополнительное подтверждение
            if (!confirm('Вы уверены, что хотите окончательно удалить эту сделку и все связанные данные?')) {
                e.preventDefault();
                return false;
            }
            
            // Блокируем кнопку, чтобы избежать двойного нажатия
            confirmDeleteBtn.disabled = true;
            confirmDeleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Удаление...';
        });
    }
    
    // Сброс состояния модального окна при закрытии
    if (deleteModal) {
        deleteModal.addEventListener('hidden.bs.modal', function() {
            if (confirmationInput) {
                confirmationInput.value = '';
                confirmationInput.classList.remove('is-valid', 'is-invalid');
            }
            if (confirmDeleteBtn) {
                confirmDeleteBtn.disabled = true;
                confirmDeleteBtn.innerHTML = '<i class="fas fa-trash3 me-1"></i>Удалить сделку';
            }
            
            // Удаляем сообщение об ошибке
            const errorDiv = document.getElementById('confirmationDealError');
            if (errorDiv) {
                errorDiv.remove();
            }
        });
    }
    
    // Инициализация тултипов
    const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Функция для обновления индикатора активных фильтров
function updateFilterIndicator() {
    const urlParams = new URLSearchParams(window.location.search);
    const filterParams = ['status', 'deal_type', 'payment_period', 'manager_id', 'investor_id', 'search'];
    
    let activeCount = 0;
    filterParams.forEach(param => {
        if (urlParams.has(param) && urlParams.get(param)) {
            activeCount++;
        }
    });
    
    // Обновляем бейдж если есть активные фильтры
    const filterBtn = document.querySelector('[data-bs-target="#filterPanel"] .badge');
    if (filterBtn) {
        if (activeCount > 0) {
            filterBtn.textContent = activeCount;
            filterBtn.style.display = 'inline-block';
        } else {
            filterBtn.style.display = 'none';
        }
    }
}

// Функция для отправки формы с сохранением состояния
function submitFilterForm() {
    const filterPanel = document.getElementById('filterPanel');
    const isExpanded = filterPanel.classList.contains('show');
    
    // Сохраняем текущее состояние панели
    setFilterPanelState(isExpanded ? 'open' : 'closed');
    
    // Отправляем форму
    document.getElementById('filterForm').submit();
}

// Очистка поля поиска
function clearSearch() {
    document.getElementById('search').value = '';
    submitFilterForm();
}

// Сброс всех фильтров
function resetFilters() {
    // Сбросить все поля формы
    document.getElementById('filterForm').reset();
    
    // Установить значения по умолчанию для дат
    document.getElementById('start_date').value = '{{ date("Y-m-01") }}';
    document.getElementById('end_date').value = '{{ date("Y-m-d") }}';
    document.getElementById('per_page').value = '20';
    
    // Принудительно закрываем панель при сбросе
    setFilterPanelState('closed');
    
    // Отправить форму
    document.getElementById('filterForm').submit();
}

// Быстрое применение фильтра
function quickFilter(param, value) {
    // Устанавливаем значение фильтра
    document.querySelector(`[name="${param}"]`).value = value;
    
    // Сохраняем текущее состояние панели
    const filterPanel = document.getElementById('filterPanel');
    const isExpanded = filterPanel.classList.contains('show');
    setFilterPanelState(isExpanded ? 'open' : 'closed');
    
    // Отправляем форму
    document.getElementById('filterForm').submit();
}
</script>
@endpush