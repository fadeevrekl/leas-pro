@extends('layouts.app')

@section('title', 'Автомобили')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2><i class="fas fa-car me-2"></i>Автомобили</h2>
        </div>
        <div class="col-md-6 text-end">
            @if(auth()->user()->isAdmin())
                <a href="{{ route('cars.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-1"></i>Добавить автомобиль
                </a>
            @endif
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
                <span class="badge bg-primary" id="filterBadge" style="display: none;">
                    <i class="fas fa-chevron-down"></i>
                </span>
            </div>
        </div>
        
        <div class="collapse" id="filterPanel">
            <div class="card-body">
                <form method="GET" action="{{ route('cars.index') }}" id="filterForm">
                    
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
                                       placeholder="Марка, модель, госномер, VIN..."
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
                                <option value="">Все статусы</option>
                                @foreach($statuses as $value => $label)
                                    <option value="{{ $value }}" 
                                        {{ request('status') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Тип топлива -->
                        <div class="col-md-4 mb-3">
                            <label for="fuel_type" class="form-label">Тип топлива</label>
                            <select class="form-select" id="fuel_type" name="fuel_type">
                                <option value="">Все типы</option>
                                @foreach($fuelTypes as $value => $label)
                                    <option value="{{ $value }}" 
                                        {{ request('fuel_type') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <!-- Строка 2: Дополнительные фильтры -->
                    <div class="row">
                        <!-- Год выпуска -->
                        <div class="col-md-3 mb-3">
                            <label for="year_from" class="form-label">Год выпуска от</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="year_from" 
                                   name="year_from" 
                                   placeholder="2000"
                                   min="1990" 
                                   max="{{ date('Y') + 1 }}"
                                   value="{{ request('year_from') }}">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="year_to" class="form-label">до</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="year_to" 
                                   name="year_to" 
                                   placeholder="{{ date('Y') + 1 }}"
                                   min="1990" 
                                   max="{{ date('Y') + 1 }}"
                                   value="{{ request('year_to') }}">
                        </div>
                        
                        <!-- Цена -->
                        <div class="col-md-3 mb-3">
                            <label for="price_from" class="form-label">Цена от, ₽</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="price_from" 
                                   name="price_from" 
                                   placeholder="0"
                                   min="0"
                                   step="1000"
                                   value="{{ request('price_from') }}">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="price_to" class="form-label">до, ₽</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="price_to" 
                                   name="price_to" 
                                   placeholder="10000000"
                                   min="0"
                                   step="1000"
                                   value="{{ request('price_to') }}">
                        </div>
                    </div>
                    
                    <!-- Строка 3: Фильтры по пользователям -->
                    <div class="row">
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
                        @elseif(auth()->user()->isManager())
                            <input type="hidden" name="manager_id" value="{{ auth()->id() }}">
                        @endif
                        
                        <!-- Фильтр по инвестору -->
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
                        
                        <!-- Сортировка -->
                        <div class="col-md-2 mb-3">
                            <label for="sort_by" class="form-label">Сортировка</label>
                            <select class="form-select" id="sort_by" name="sort_by">
                                <option value="created_at" {{ request('sort_by', 'created_at') == 'created_at' ? 'selected' : '' }}>По дате</option>
                                <option value="brand" {{ request('sort_by') == 'brand' ? 'selected' : '' }}>По марке</option>
                                <option value="price" {{ request('sort_by') == 'price' ? 'selected' : '' }}>По цене</option>
                                <option value="year" {{ request('sort_by') == 'year' ? 'selected' : '' }}>По году</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2 mb-3">
                            <label for="sort_order" class="form-label">Направление</label>
                            <select class="form-select" id="sort_order" name="sort_order">
                                <option value="desc" {{ request('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>По убыванию</option>
                                <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>По возрастанию</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Строка 4: Фильтр по дате и кнопки -->
                    <div class="row">
                        <!-- Дата создания -->
                        <div class="col-md-3 mb-3">
                            <label for="start_date" class="form-label">Дата добавления с</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="start_date" 
                                   name="start_date" 
                                   value="{{ request('start_date', date('Y-m-01')) }}">
                        </div>
                        
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
    @if(request()->anyFilled(['status', 'fuel_type', 'manager_id', 'investor_id', 'search', 'year_from', 'year_to', 'price_from', 'price_to', 'start_date', 'end_date']))
    <div class="alert alert-info mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <strong>Активные фильтры:</strong>
                @php
                    $activeFilters = [];
                    if (request('status')) $activeFilters[] = 'Статус: ' . ($statuses[request('status')] ?? request('status'));
                    if (request('fuel_type')) $activeFilters[] = 'Топливо: ' . ($fuelTypes[request('fuel_type')] ?? request('fuel_type'));
                    if (request('manager_id')) {
                        $manager = $managers->firstWhere('id', request('manager_id'));
                        $activeFilters[] = 'Менеджер: ' . ($manager->name ?? request('manager_id'));
                    }
                    if (request('investor_id')) {
                        $investor = $investors->firstWhere('id', request('investor_id'));
                        $activeFilters[] = 'Инвестор: ' . ($investor->name ?? request('investor_id'));
                    }
                    if (request('search')) $activeFilters[] = 'Поиск: "' . request('search') . '"';
                    if (request('year_from')) $activeFilters[] = 'Год от: ' . request('year_from');
                    if (request('year_to')) $activeFilters[] = 'Год до: ' . request('year_to');
                    if (request('price_from')) $activeFilters[] = 'Цена от: ' . number_format(request('price_from'), 0, '', ' ') . ' ₽';
                    if (request('price_to')) $activeFilters[] = 'Цена до: ' . number_format(request('price_to'), 0, '', ' ') . ' ₽';
                    if (request('start_date')) $activeFilters[] = 'Добавлены с ' . request('start_date');
                    if (request('end_date')) $activeFilters[] = 'Добавлены по ' . request('end_date');
                @endphp
                {{ implode(', ', $activeFilters) }}
            </div>
            <div>
                <a href="{{ route('cars.index') }}" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-times me-1"></i>Очистить
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Таблица автомобилей -->
    <div class="card">
        <div class="card-body">
            @if($cars->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Автомобиль</th>
                                <th>Госномер</th>
                                <th>VIN</th>
                                <th>Статус</th>
                                <th>Цена</th>
                                {{-- Столбец "Менеджер" показываем только НЕ менеджерам --}}
                                @if(!auth()->user()->isManager())
                                <th>Менеджер</th>
                                @endif
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cars as $car)
                            <tr>
                                <td>{{ $car->id }}</td>
                                <td>
                                    <strong>{{ $car->brand }} {{ $car->model }}</strong><br>
                                    <small class="text-muted">
                                        {{ $car->year }} г., {{ $car->color }}, {{ $car->fuel_type }}
                                    </small>
                                </td>
                                
                                <td>
                                    <span class="badge license-plate fs-6">{{ $car->license_plate }}</span>
                                </td>

                                <td>
                                    <small class="text-muted">{{ $car->vin }}</small>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'available' => 'free',
                                            'in_deal' => 'deal-active',
                                            'maintenance' => 'deal-overdue',
                                            'sold' => 'deal-draw'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$car->status] ?? 'secondary' }}">
                                        {{ $car->status_text }}
                                    </span>
                                </td>
                                <td>{{ number_format($car->price, 0, '', ' ') }} ₽</td>
                                
                                {{-- Ячейку с менеджером показываем только НЕ менеджерам --}}
                                @if(!auth()->user()->isManager())
                                <td>
                                    @if($car->manager)
                                        {{ $car->manager->name }}
                                    @else
                                        <span class="text-muted">Не назначен</span>
                                    @endif
                                </td>
                                @endif
                                
                                <td class="action-buttons">
                                    <a href="{{ route('cars.show', $car) }}" class="btn btn-md btn-outline-info" title="Просмотр">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(auth()->user()->isAdmin())
                                        <a href="{{ route('cars.edit', $car) }}" class="btn btn-md btn-outline-warning" title="Редактировать">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    {{ $cars->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-car" style="font-size: 3rem; color: #6c757d;"></i>
                    </div>
                    <p class="text-muted">Автомобилей не найдено</p>
                    @if(request()->anyFilled(['status', 'fuel_type', 'manager_id', 'investor_id', 'search']))
                        <a href="{{ route('cars.index') }}" class="btn btn-primary">Сбросить фильтры</a>
                    @elseif(auth()->user()->isAdmin())
                        <a href="{{ route('cars.create') }}" class="btn btn-primary">Добавить первый автомобиль</a>
                    @endif
                </div>
            @endif
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

.action-buttons {
    white-space: nowrap;
}

.action-buttons .btn {
    margin-right: 3px;
}
</style>
@endpush

@push('scripts')
<script>
// Функции для работы с localStorage
function getFilterPanelState() {
    try {
        // Проверяем, есть ли активные фильтры
        const hasActiveFilters = checkActiveFilters();
        // Если есть активные фильтры, сохраняем состояние "open"
        // Но при загрузке страницы всегда начинаем с закрытого
        return 'closed';
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

// Проверка активных фильтров
function checkActiveFilters() {
    const urlParams = new URLSearchParams(window.location.search);
    const filterParams = ['status', 'fuel_type', 'manager_id', 'investor_id', 'search', 'year_from', 'year_to', 'price_from', 'price_to', 'start_date', 'end_date'];
    
    for (const param of filterParams) {
        if (urlParams.has(param) && urlParams.get(param)) {
            return true;
        }
    }
    return false;
}

// Основная инициализация
document.addEventListener('DOMContentLoaded', function() {
    const filterPanel = document.getElementById('filterPanel');
    const filterHeader = document.querySelector('[data-bs-target="#filterPanel"]');
    const filterIcon = filterHeader?.querySelector('.fa-chevron-down');
    
    // ВСЕГДА начинаем с закрытого состояния
    // Проверяем только наличие активных фильтров для показа индикатора
    const hasActiveFilters = checkActiveFilters();
    
    // Показываем/скрываем бейдж с количеством фильтров
    updateFilterIndicator();
    
    // Если есть активные фильтры, показываем индикатор на кнопке
    if (hasActiveFilters && filterHeader) {
        const existingBadge = filterHeader.querySelector('.badge');
        if (!existingBadge) {
            const badge = document.createElement('span');
            badge.className = 'badge bg-primary ms-2';
            badge.innerHTML = '<i class="fas fa-filter"></i>';
            filterHeader.querySelector('h5').appendChild(badge);
        }
    }
    
    // Обработчик клика на заголовок - переключаем иконку
    if (filterHeader) {
        filterHeader.addEventListener('click', function() {
            setTimeout(() => {
                const isExpanded = filterPanel.classList.contains('show');
                
                // Обновляем иконку
                if (filterIcon) {
                    filterIcon.classList.toggle('fa-chevron-down', !isExpanded);
                    filterIcon.classList.toggle('fa-chevron-up', isExpanded);
                }
            }, 100);
        });
    }
});

// Функция для обновления индикатора активных фильтров
function updateFilterIndicator() {
    const urlParams = new URLSearchParams(window.location.search);
    const filterParams = ['status', 'fuel_type', 'manager_id', 'investor_id', 'search', 'year_from', 'year_to', 'price_from', 'price_to', 'start_date', 'end_date'];
    
    let activeCount = 0;
    filterParams.forEach(param => {
        if (urlParams.has(param) && urlParams.get(param)) {
            activeCount++;
        }
    });
    
    // Обновляем бейдж в заголовке панели
    const filterBtn = document.querySelector('[data-bs-target="#filterPanel"] .badge');
    const filterHeader = document.querySelector('[data-bs-target="#filterPanel"] h5');
    
    if (filterHeader) {
        // Удаляем старый бейдж, если есть
        const oldBadge = filterHeader.querySelector('.filter-count-badge');
        if (oldBadge) {
            oldBadge.remove();
        }
        
        // Добавляем новый бейдж, если есть активные фильтры
        if (activeCount > 0) {
            const badge = document.createElement('span');
            badge.className = 'badge bg-primary ms-2 filter-count-badge';
            badge.textContent = activeCount;
            badge.title = 'Активных фильтров: ' + activeCount;
            filterHeader.appendChild(badge);
        }
    }
}

// Функция для отправки формы с сохранением состояния
function submitFilterForm() {
    // ПРИНЦИПИАЛЬНО ВАЖНО: не сохраняем состояние панели как "open"
    // Сохраняем как "closed", чтобы при следующей загрузке она была закрыта
    setFilterPanelState('closed');
    
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
    document.getElementById('sort_by').value = 'created_at';
    document.getElementById('sort_order').value = 'desc';
    
    // Очистить поля года и цены
    document.getElementById('year_from').value = '';
    document.getElementById('year_to').value = '';
    document.getElementById('price_from').value = '';
    document.getElementById('price_to').value = '';
    
    // Сохраняем состояние как "closed"
    setFilterPanelState('closed');
    
    // Отправить форму
    document.getElementById('filterForm').submit();
}

// Быстрое применение фильтра
function quickFilter(param, value) {
    // Устанавливаем значение фильтра
    document.querySelector(`[name="${param}"]`).value = value;
    
    // Сохраняем состояние как "closed"
    setFilterPanelState('closed');
    
    // Отправляем форму
    document.getElementById('filterForm').submit();
}
</script>
@endpush
