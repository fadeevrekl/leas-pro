@extends('layouts.app')

@section('title', 'Просмотр автомобиля')

@section('content')

@if(session('error') && session('show_delete_modal'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Показываем модальное окно при ошибке
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteCarModal'));
            deleteModal.show();
            
            // Показываем сообщение об ошибке
            setTimeout(function() {
                alert('{{ session('error') }}');
            }, 500);
        });
    </script>
@endif


<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2><i class="fas fa-car me-2"></i>Карточка автомобиля</h2>
        </div>
        <div class="col-md-6 text-end">
    


            <a href="{{ route('cars.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Назад к списку
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary-lt">
                    <h3 class="m-0"><i class="fas fa-car me-2"></i> Основная информация</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h3 class="m-0">Марка и модель</h3>
                            <p class="fs-3">
                                <strong>{{ $car->brand }} {{ $car->model }}</strong>
                            </p>
                        </div>
                        
                        <div class="col-md-4">
                            <h3 class="m-0">Госномер</h3>
                            <div class="license-plate">
                                @php
                                    $plate = $car->license_plate;
                                    $cleanPlate = preg_replace('/\s+/', '', strtoupper($plate));
                                    
                                    if (preg_match('/^([А-ЯA-Z])(\d{3})([А-ЯA-Z]{2})(\d{2,3})$/', $cleanPlate, $matches)) {
                                        $letter1 = $matches[1];
                                        $numbers = $matches[2];
                                        $letters2 = $matches[3];
                                        $region = $matches[4];
                                    } else {
                                        $letter1 = '';
                                        $numbers = $plate;
                                        $letters2 = '';
                                        $region = '';
                                    }
                                @endphp
                                
                                @if($letter1)
                                    <span class="plate-letter">{{ $letter1 }}</span>
                                    <span class="plate-digit">{{ $numbers }}</span>
                                    <span class="plate-letter">{{ $letters2 }}</span>
                                    <div class="plate-region">
                                        {{ $region }}<span class="plate-rus">rus</span>
                                    </div>
                                @else
                                    <span class="plate-simple">{{ $plate }}</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <h3 class="m-0">Статус</h3>
                            @php
                                $statusColors = [
                                    'available' => 'free',
                                    'in_deal' => 'deal-active',
                                    'maintenance' => 'deal-overdue',
                                    'sold' => 'deal-draw'
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$car->status] ?? 'secondary' }} fs-6">
                                {{ $car->status_text }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <h3 class="m-0">VIN номер</h3>
                            <p><code>{{ $car->vin }}</code></p>
                        </div>
                        
                        <div class="col-md-3">
                            <h3 class="m-0">Цвет</h3>
                            <p>{{ $car->color }}</p>
                        </div>
                        
                        <div class="col-md-3">
        <h3 class="m-0">Год выпуска</h3>
        <p>{{ $car->year ?? 'Не указан' }}</p>
    </div>
                        
                        <div class="col-md-3">
                            <h3 class="m-0">Пробег</h3>
                            <p>{{ number_format($car->mileage, 0, '', ' ') }} км</p>
                        </div>
                        
                        <div class="col-md-3">
                            <h3 class="m-0">Топливо</h3>
                            <p>{{ $car->fuel_type }}</p>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h3 class="m-0">Цена</h3>
                            <p class="fs-4 text-primary">
                                <strong>{{ number_format($car->price, 0, '', ' ') }} ₽</i></strong>
                            </p>
                        </div>
                        
                        <div class="col-md-6">
                            <h3 class="m-0">Количество сделок</h3>
                            <p class="fs-4">{{ $car->deal_count }}</p>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
    <h3 class="m-0">Инвестор</h3>
    <p>
        @if(is_numeric($car->investor_id))
            {{-- Если investor_id это число, ищем инвестора в БД --}}
            @php
                $investor = App\Models\User::find($car->investor_id);
            @endphp
            @if($investor)
                {{ $investor->name }}
                @if($investor->commission_percent)
                    <br><small class="text-muted">Комиссия: {{ $investor->commission_percent }}%</small>
                @endif
            @else
                <span class="text-muted">Инвестор не найден (ID: {{ $car->investor_id }})</span>
            @endif
        @elseif(is_string($car->investor_id) && json_decode($car->investor_id, true))
            {{-- Если это JSON строка --}}
            @php
                $investorData = json_decode($car->investor_id, true);
            @endphp
            {{ $investorData['name'] ?? 'Неизвестно' }}
            @if(isset($investorData['commission_percent']))
                <br><small class="text-muted">Комиссия: {{ $investorData['commission_percent'] }}%</small>
            @endif
        @else
            {{-- Просто текст --}}
            {{ $car->investor_id ?? 'Не назначен' }}
        @endif
    </p>
</div>
                        
                        <div class="col-md-6">
                            <h3 class="m-0">Ответственный менеджер</h3>
                            <p>
                                @if($car->manager)
                                    {{ $car->manager->name }}
                                @else
                                    <span class="text-muted">Не назначен</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    @if($car->gps_tracker_id)
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h3 class="m-0">GPS трекер</h3>
                            <p>{{ $car->gps_tracker_id }}</p>
                        </div>
                    </div>
                    @endif
                    
                    @if($car->notes)
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <h3 class="m-0">Заметки</h3>
                            <div class="bg-light p-3 rounded">
                                {{ $car->notes }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Вкладки для документов и расходов -->
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs bg-primary-lt" id="carTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="documents-tab" data-bs-toggle="tab" 
                                    data-bs-target="#documents" type="button" role="tab">
                                <i class="bi bi-files me-1"></i>Документы
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="expenses-tab" data-bs-toggle="tab" 
                                    data-bs-target="#expenses" type="button" role="tab">
                                <i class="fas fa-money-bill-stack me-1"></i>Расходы
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="carTabsContent">
                        <!-- Вкладка документов -->
                        <div class="tab-pane fade show active" id="documents" role="tabpanel">
                            <h3 class="m-0">Документы автомобиля</h3>
                            
                            @if($car->documents->count() > 0)
                          <div class="table-responsive">
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Тип</th>
                <th>Номер</th>
                <th>Дата выдачи</th>
                <th>Срок действия</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            @foreach($car->documents as $document)
            <tr>
                <td>
                    <i class="bi {{ $document->file_icon }} me-1"></i>
                    {{ $document->type_text }}
                </td>
                <td>{{ $document->document_number ?? '-' }}</td>
                <td>{{ $document->issue_date ? $document->issue_date->format('d.m.Y') : '-' }}</td>
                <td>
                    @if($document->expiry_date)
                        {{ $document->expiry_date->format('d.m.Y') }}
                        @if($document->expiry_date->isPast())
                            <span class="badge bg-deal-overdue">Просрочен</span>
                        @endif
                    @else
                        -
                    @endif
                </td>
                <td>
				<div class="d-flex">
                    @if($document->file_path)
                        @if($document->canViewInBrowser())
                            <a href="{{ $document->view_url }}" 
                               target="_blank" 
                               class="btn btn-md btn-outline-info me-1"
                               title="Просмотреть">
                                <i class="fas fa-eye"></i>
                            </a>
                        @endif
                        
                        <a href="{{ $document->download_url }}" 
                           class="btn btn-md btn-outline-primary me-1"
                           title="Скачать">
                            <i class="fas fa-download"></i>
                        </a>
                        
                         <!-- Кнопка удаления (для админов и менеджеров) -->
        @if(auth()->user()->isAdmin() || auth()->user()->isManager())
            <form action="{{ route('cars.documents.destroy', ['car' => $car->id, 'carDocument' => $document->id]) }}"  
                  method="POST" 
                  class="m-0 p-0"
                  onsubmit="return confirm('Вы действительно хотите удалить документ \"{{ $document->type_text }}\"?')">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="btn btn-outline-danger action-btn"
                        title="Удалить документ">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        @endif
                        
                    @else
                        <span class="text-muted">Нет файла</span>
                    @endif
					<div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
                            @else
                                <p class="text-muted">Документы не добавлены</p>
                            @endif
                            
                            @if($car->canAddDocuments())
    <button class="btn btn-sm btn-outline-primary mt-2" data-bs-toggle="modal" 
            data-bs-target="#addDocumentModal">
        <i class="fas fa-plus-circle me-1"></i>Добавить документ
    </button>
@else
    <button class="btn btn-sm btn-outline-secondary mt-2" disabled
            title="Добавление документов недоступно. Статус: {{ $car->status_text }}">
        <i class="fas fa-plus-circle me-1"></i>Добавить документ
    </button>
@endif
                        </div>
                        
                        <!-- Вкладка расходов -->
                  <!-- Вкладка расходов -->
<div class="tab-pane fade" id="expenses" role="tabpanel">
    <h3 class="m-0">Расходы на автомобиль</h3>
    
    @if($car->expenses->count() > 0)
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th width="40px"></th>
                        <th>Тип расхода</th>
                        <th>Сумма</th>
                        <th>Дата</th>
                        <th width="150px">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($car->expenses as $expense)
                    <tr>
                        <td>
                            <i class="{{ $expense->expense_icon }}"></i>
                        </td>
                        <td>{{ $expense->expense_type_text }}</td>
                        <td><strong>{{ number_format($expense->amount, 0, '', ' ') }} ₽</strong></td>
                        <td>{{ $expense->expense_date->format('d.m.Y') }}</td>
<td>
    <div class="d-flex gap-1">
        <!-- Кнопка информации (если есть описание) -->
        @if($expense->description)
            <button type="button" 
                    class="btn btn-outline-info"
                    data-bs-toggle="modal" 
                    data-bs-target="#expenseInfoModal{{ $expense->id }}"
                    title="Показать описание">
                <i class="fas fa-info-circle"></i>
            </button>
        @endif
        
        <!-- Кнопки для документа -->
        @if($expense->document_path)
            @if($expense->canViewInBrowser())
                <a href="{{ $expense->view_url }}" 
                   target="_blank" 
                   class="btn btn-outline-info"
                   title="Просмотреть чек">
                    <i class="fas fa-eye"></i>
                </a>
            @endif
            
            <a href="{{ $expense->download_url }}" 
               class="btn btn-outline-primary"
               title="Скачать чек">
                <i class="fas fa-download"></i>
            </a>
        @else
            <button type="button" 
                    class="btn btn-outline-secondary"
                    data-bs-toggle="tooltip" 
                    title="Нет прикрепленного файла"
                    disabled>
                <i class="fas fa-file"></i>
            </button>
        @endif
        
        <!-- Кнопка удаления (только для админа/менеджера) -->
        @if(auth()->user()->isAdmin() || auth()->user()->isManager())
            <form action="{{ route('cars.expenses.destroy', ['car' => $car, 'expense' => $expense]) }}" 
                  method="POST" 
                  class="m-0 p-0"
                  onsubmit="return confirm('Вы действительно хотите удалить расход \"{{ $expense->expense_type_text }}\"?')">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="btn btn-outline-danger"
                        title="Удалить расход">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        @endif
    </div>
</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-3 p-3 bg-light rounded">
            <strong>Итого расходов:</strong> 
            <span class="fs-5 text-danger">
                {{ number_format($car->expenses->sum('amount'), 0, '', ' ') }} ₽
            </span>
        </div>
        
        <!-- Модальные окна для описания расходов -->
        @foreach($car->expenses as $expense)
            @if($expense->description)
                <div class="modal fade" id="expenseInfoModal{{ $expense->id }}" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-primary-lt">
                                <h5 class="modal-title">
                                    <i class="bi {{ $expense->expense_icon }} me-2"></i>
                                    {{ $expense->expense_type_text }}
                                </h3>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <small class="text-muted">Сумма</small>
                                            <div class="fs-5 text-primary">
                                                <strong>{{ number_format($expense->amount, 2, '.', ' ') }} ₽</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <small class="text-muted">Дата расхода</small>
                                            <div>{{ $expense->expense_date->format('d.m.Y') }}</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <small class="text-muted">Описание</small>
                                    <div class="p-3 bg-light rounded">
                                        {{ $expense->description }}
                                    </div>
                                </div>
                                
                                @if($expense->document_path)
                                <div class="mb-3">
                                    <small class="text-muted">Прикрепленный документ</small>
                                    <div class="d-flex align-items-center mt-1">
                                        <i class="bi bi-paperclip me-2"></i>
                                        <span class="me-3">{{ $expense->filename }}</span>
                                        <a href="{{ $expense->download_url }}" 
                                           class="btn btn-sm btn-outline-primary me-1">
                                            <i class="fas fa-download"></i> Скачать
                                        </a>
                                        @if($expense->canViewInBrowser())
                                            <a href="{{ $expense->view_url }}" 
                                               target="_blank" 
                                               class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-eye"></i> Просмотреть
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                @endif
                                
                                <hr>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    Добавлено: {{ $expense->created_at->format('d.m.Y H:i') }}
                                    @if($expense->created_at != $expense->updated_at)
                                        <br>
                                        <i class="bi bi-arrow-clockwise me-1"></i>
                                        Обновлено: {{ $expense->updated_at->format('d.m.Y H:i') }}
                                    @endif
                                </small>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    @else
        <div class="text-center py-4">
            <i class="fas fa-money-bill-stack display-4 text-muted"></i>
            <p class="text-muted mt-2">Расходы не добавлены</p>
        </div>
    @endif
    
    @if($car->canAddExpenses())
    <button class="btn btn-sm btn-outline-primary mt-2" data-bs-toggle="modal" 
            data-bs-target="#addExpenseModal">
        <i class="fas fa-plus-circle me-1"></i>Добавить расход
    </button>
@else
    <button class="btn btn-sm btn-outline-secondary mt-2" disabled
            title="Добавление расходов недоступно. Статус: {{ $car->status_text }}">
        <i class="fas fa-plus-circle me-1"></i>Добавить расход
    </button>
@endif
</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary-lt">
                    <h3 class="m-0"><i class="fas fa-bar-chart"></i> Статистика</h3>
                </div>
                <div class="card-body">
                    <p><i class="fas fa-calendar me-2"></i>Добавлен: {{ $car->created_at->format('d.m.Y H:i') }}</p>
                    <p><i class="bi bi-arrow-clockwise me-2"></i>Обновлен: {{ $car->updated_at->format('d.m.Y H:i') }}</p>
                    
               <div class="mt-4">
    <h3>Сделки автомобиля</h3>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary btn-sm" 
                data-bs-toggle="modal" 
                data-bs-target="#dealHistoryModal">
            <i class="fas fa-file-contract me-1"></i>История сделок
           
        </button>
    </div>
    </div>
                </div>
            </div>
            
    <div class="card">
    <div class="card-header bg-primary-lt">
        <h3 class="m-0"><i class="fas fa-handshake"></i> Быстрые действия</h3>
    </div>
    <div class="card-body">
        @if(auth()->user()->isAdmin() || auth()->user()->isManager())
            @if($car->canCreateDeals())
                <a href="{{ route('deals.create', ['car_id' => $car->id]) }}" class="btn btn-outline-success w-100 mb-2">
                    <i class="fas fa-file-plus me-1"></i>Создать сделку
                </a>
            @else
                <button class="btn btn-outline-success w-100 mb-2" disabled 
                        title="Создание сделки невозможно. Автомобиль имеет статус: {{ $car->status_text }}">
                    <i class="fas fa-file-plus me-1"></i>Сделка невозможна, автомобиль  
                    
                    {{ $car->status_text }}
                </button>
            @endif
        @else
            <button class="btn btn-outline-success w-100 mb-2" disabled 
                    title="Доступно только для менеджеров и администраторов">
                <i class="fas fa-file-plus me-1"></i>Создать сделку
            </button>
        @endif

        @if(auth()->user()->isAdmin() && $car->status !== 'sold')
            <a href="{{ route('cars.edit', $car) }}" class="btn btn-outline-warning w-100 mb-2">
                <i class="fas fa-edit me-1"></i>Редактировать данные
            </a>
        @elseif(auth()->user()->isAdmin())
            <button class="btn btn-outline-warning w-100 mb-2" disabled
                    title="Редактирование проданных автомобилей недоступно">
                <i class="fas fa-edit me-1"></i>Редактировать данные
            </button>
        @endif

        @if(auth()->user()->isAdmin())
            <button type="button" 
                    class="btn btn-outline-danger w-100 mb-2" 
                    data-bs-toggle="modal" 
                    data-bs-target="#deleteCarModal">
                <i class="fas fa-trash3 me-1"></i>Удалить автомобиль
            </button>
        @endif
    </div>
</div>
            
            <!-- Информация о следующем ТО -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="m-0">Обслуживание</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Информация о ТО будет отображаться здесь</p>
                    <button class="btn btn-outline-info btn-sm w-100" disabled>
                        <i class="fas fa-cog me-1"></i>Запланировать ТО
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для добавления документа -->
<div class="modal fade" id="addDocumentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавить документ</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('cars.documents.store', $car) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Тип документа *</label>
                        <select name="type" class="form-select" required>
                            @foreach(App\Models\CarDocument::getDocumentTypes() as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Номер документа</label>
                        <input type="text" name="document_number" class="form-control">
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Дата выдачи</label>
                            <input type="date" name="issue_date" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Срок действия</label>
                            <input type="date" name="expiry_date" class="form-control">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Файл документа</label>
                        <input type="file" name="document_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        <div class="form-text text-danger">
        <i class="fas fa-exclamation-circle me-1"></i>
        Файл обязателен для заполнения
    </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Заметки</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальное окно для добавления расхода -->
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавить расход</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('cars.expenses.store', $car) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Тип расхода *</label>
                        <select name="expense_type" class="form-select" required>
                            @foreach(App\Models\CarExpense::getExpenseTypes() as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Сумма (₽) *</label>
                        <input type="number" name="amount" step="0.01" min="0" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Дата расхода *</label>
                        <input type="date" name="expense_date" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Чек/документ</label>
                        <input type="file" name="expense_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Описание</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
  
    
    
    /* Стили для таблицы расходов */
.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.btn-group-sm > .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

/* Иконки расходов */
.fa-wrench { color: #6c757d; }         /* Серый */
.fa-tools { color: #dc3545; }          /* Красный */
.fa-droplet { color: #0dcaf0; }        /* Голубой */
.fa-gas-pump { color: #fd7e14; }       /* Оранжевый */
.fa-shield-check { color: #198754; }   /* Зеленый */
.fa-calculator { color: #6f42c1; }     /* Фиолетовый */
.fa-money-bill-wave { color: #20c997; } /* Бирюзовый */

/* Анимация для иконок */
.fa {
    transition: transform 0.2s;
}
.btn:hover .fa {
    transform: scale(1.2);
}

/* Стили для модального окна */
.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}
    
 /* Стили для кнопок действий */
.btn-group-sm > .btn, 
.btn-group-sm > .btn-outline-secondary[disabled] {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
}

/* Стили для отключенной кнопки "нет файла" */
.btn-outline-secondary[disabled] {
    opacity: 0.6;
    cursor: default;
}

.btn-outline-secondary[disabled]:hover {
    background-color: transparent;
    border-color: #6c757d;
    color: #6c757d;
}

/* Эффекты наведения для всех кнопок кроме отключенных */
.btn-group-sm > .btn:not(:disabled):not(.disabled):hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Анимация иконок */
.btn i {
    transition: transform 0.2s;
}

.btn:not(:disabled):not(.disabled):hover i {
    transform: scale(1.2);
}



/* Убираем стандартный стиль формы */
form.d-inline {
    display: inline-block;
    margin: 0;
    padding: 0;
}   

/* Стили для модального окна удаления */
#deleteCarModal .modal-header {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
}

#deleteCarModal .alert-warning {
    background-color: #fff3cd;
    border-color: #ffeaa7;
}

#deleteCarModal .alert-danger {
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

#deleteCarModal .alert-danger h6 {
    color: #721c24;
}

#deleteCarModal .alert-danger ul {
    padding-left: 1.5rem;
}

#deleteCarModal .alert-danger li {
    margin-bottom: 0.25rem;
}

/* Стили для поля подтверждения */
#confirmationText.is-valid {
    border-color: #198754;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

#confirmationText.is-invalid {
    border-color: #dc3545;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}



.btn-danger:hover {
    animation: none;
}
/* Стили для статусов автомобилей */
.status-badge-available {
    background-color: #28a745;
    color: white;
}

.status-badge-in_deal {
    background-color: #ffc107;
    color: black;
}

.status-badge-maintenance {
    background-color: #17a2b8;
    color: white;
}

.status-badge-sold {
    background-color: #6c757d;
    color: white;
}

/* Стили для отключенных кнопок с пояснением */
.btn-success:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.btn-success:disabled:hover::after {
    content: attr(title);
    position: absolute;
    background: rgba(0,0,0,0.8);
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 14px;
    z-index: 1000;
    white-space: nowrap;
    margin-top: -40px;
    margin-left: -20px;
}

</style>
<script>
// Функция для подтверждения удаления расхода
function confirmDeleteExpense(expenseId, expenseType) {
    if (confirm('Вы уверены, что хотите удалить расход "' + expenseType + '"?')) {
        // Находим форму удаления и отправляем
        fetch(`/cars/{{ $car->id }}/expenses/${expenseId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        })
        .then(response => {
            if (response.ok) {
                location.reload();
            } else {
                alert('Ошибка при удалении расхода');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ошибка при удалении расхода');
        });
    }
}

// Инициализация всплывающих подсказок
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>


<!-- Модальное окно для подтверждения удаления автомобиля -->
<div class="modal fade" id="deleteCarModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle-fill me-2"></i>
                    Подтверждение удаления автомобиля
                </h3>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('cars.destroy', $car) }}" method="POST" id="deleteCarForm">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-octagon-fill me-2"></i>
                        <strong>Внимание! Это действие необратимо.</strong>
                    </div>
                    
                    <p>Вы собираетесь удалить автомобиль:</p>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">{{ $car->brand }} {{ $car->model }}</h3>
                            <p class="card-text mb-1">
                                <small class="text-muted">VIN:</small> {{ $car->vin }}<br>
                                <small class="text-muted">Госномер:</small> {{ $car->license_plate }}<br>
                                <small class="text-muted">Статус:</small> {{ $car->status_text }}
                            </p>
                        </div>
                    </div>
                    
                    <!-- ДОБАВЬТЕ ЭТОТ БЛОК ДЛЯ ПРОДАННЫХ АВТОМОБИЛЕЙ -->
                    @if($car->status === 'sold')
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle-fill me-2"></i>
                            <strong>Внимание!</strong> Вы собираетесь удалить <strong>ПРОДАННЫЙ</strong> автомобиль. 
                            Это может нарушить историческую отчетность. Удаляйте только тестовые данные.
                        </div>
                    @endif
                    
                    <div class="alert alert-danger">
                        <h3 class="m-0"><i class="fas fa-trash3 me-2"></i>При удалении автомобиля будут безвозвратно удалены:</h3>
                        <ul class="mb-0">
                            <li>Все документы автомобиля ({{ $car->documents->count() }} шт.)</li>
                            <li>Все расходы по автомобилю ({{ $car->expenses->count() }} шт.)</li>
                            <li>Вся история обслуживания</li>
                            @if($car->deal_count > 0)
                                <li><strong class="text-danger">Внимание!</strong> Связанные сделки ({{ $car->deal_count }} шт.) - они могут стать недействительными!</li>
                            @endif
                        </ul>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <label for="confirmationText" class="form-label">
                            Для подтверждения удаления введите слово <strong>"УДАЛИТЬ"</strong> в поле ниже:
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="confirmationText" 
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
                    <button type="submit" class="btn btn-danger" id="confirmDeleteBtn" disabled>
                        <i class="fas fa-trash3 me-1"></i>Удалить автомобиль
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>





<script>
document.addEventListener('DOMContentLoaded', function() {
    const confirmationInput = document.getElementById('confirmationText');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const deleteCarForm = document.getElementById('deleteCarForm');
    
    if (confirmationInput && confirmDeleteBtn) {
        // Проверка ввода
        confirmationInput.addEventListener('input', function() {
            const inputValue = this.value.trim().toUpperCase();
            
            if (inputValue === 'УДАЛИТЬ') {
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
        
        // Обработка отправки формы
        deleteCarForm.addEventListener('submit', function(e) {
            const inputValue = confirmationInput.value.trim().toUpperCase();
            
            if (inputValue !== 'УДАЛИТЬ') {
                e.preventDefault();
                confirmationInput.classList.add('is-invalid');
                confirmationInput.focus();
                
                // Показываем сообщение об ошибке
                if (!document.getElementById('confirmationError')) {
                    const errorDiv = document.createElement('div');
                    errorDiv.id = 'confirmationError';
                    errorDiv.className = 'invalid-feedback d-block mt-2';
                    errorDiv.innerHTML = '<i class="bi bi-exclamation-circle me-1"></i>Пожалуйста, введите слово "УДАЛИТЬ" для подтверждения';
                    confirmationInput.parentNode.appendChild(errorDiv);
                }
                
                return false;
            }
            
            // Дополнительное подтверждение
            if (!confirm('Вы уверены, что хотите окончательно удалить этот автомобиль и все связанные данные?')) {
                e.preventDefault();
                return false;
            }
            
            // Блокируем кнопку, чтобы избежать двойного нажатия
            confirmDeleteBtn.disabled = true;
            confirmDeleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Удаление...';
        });
    }
    
    // Сброс состояния модального окна при закрытии
    const deleteModal = document.getElementById('deleteCarModal');
    if (deleteModal) {
        deleteModal.addEventListener('hidden.bs.modal', function() {
            if (confirmationInput) {
                confirmationInput.value = '';
                confirmationInput.classList.remove('is-valid', 'is-invalid');
            }
            if (confirmDeleteBtn) {
                confirmDeleteBtn.disabled = true;
                confirmDeleteBtn.innerHTML = '<i class="fas fa-trash3 me-1"></i>Удалить автомобиль';
            }
            
            // Удаляем сообщение об ошибке
            const errorDiv = document.getElementById('confirmationError');
            if (errorDiv) {
                errorDiv.remove();
            }
        });
    }
});

</script>


<!-- Модальное окно истории сделок -->
@include('cars.partials.deal-history')

<script>
// JavaScript для модального окна сделок автомобиля
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация тултипов Bootstrap внутри модального окна
    const dealHistoryModal = document.getElementById('dealHistoryModal');
    if (dealHistoryModal) {
        dealHistoryModal.addEventListener('shown.bs.modal', function() {
            const tooltipTriggerList = [].slice.call(
                dealHistoryModal.querySelectorAll('[data-bs-toggle="tooltip"]')
            );
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
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