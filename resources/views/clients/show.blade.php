@extends('layouts.app')

@section('title', 'Просмотр клиента')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2><i class="fas fa-user me-2"></i>Карточка клиента</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('clients.edit', $client) }}" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i>Редактировать
            </a>
            <a href="{{ route('clients.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Назад к списку
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary-lt">
                     <h3 class="m-0"> <i class="fas fa-users"></i> Основная информация</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h4>ФИО</h4>
                            <p class="fs-5">
                                <strong>{{ $client->last_name }} {{ $client->first_name }} {{ $client->middle_name }}</strong>
                            </p>
                        </div>
                        
                        <div class="col-md-4">
                            <h4>Паспорт</h4>
                            <p>
                                {{ $client->passport_series }} {{ $client->passport_number }}<br>
                                Выдан: {{ $client->passport_issued_by }}<br>
                                Дата: {{ $client->passport_issued_date->format('d.m.Y') }}<br>
                                Код: {{ $client->passport_division_code }}
                            </p>
                        </div>
                        
                        <div class="col-md-4">
                            <h4>Контакты</h4>
                            <p>
                                <i class="fas fa-phone me-1"></i> {{ $client->phone }}<br>
                                @if($client->additional_phone)
                                    <i class="fas fa-phone-plus me-1"></i> {{ $client->additional_phone }}<br>
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
    <h4>Статус</h4>
    <span class="badge bg-{{ $client->status_color }} fs-6">
        {{ $client->status_text }}
    </span>
</div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h4>Адрес регистрации</h4>
                            <p>{{ $client->registration_address }}</p>
                        </div>
                        
                        <div class="col-md-6">
                            <h4>Адрес проживания</h4>
                            <p>{{ $client->residential_address }}</p>
                        </div>
                    </div>
                    
                    @if($client->drivers_license)
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h4>Водительское удостоверение</h4>
                            <p>{{ $client->drivers_license }}</p>
                        </div>
                    </div>
                    @endif
                    
                    @if($client->guarantor)
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h4>Поручитель</h4>
                            <p>{{ $client->guarantor }}</p>
                        </div>
                    </div>
                    @endif
                    
                    @if($client->notes)
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <h4>Заметки</h4>
                            <div class="bg-light p-3 rounded">
                                {{ $client->notes }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary-lt">
                     <h3 class="m-0"><i class="fas fa-bar-chart"></i> Статистика</h3>
                </div>
                <div class="card-body">
                    <p><i class="fas fa-calendar me-2"></i>Создан: {{ $client->created_at->format('d.m.Y H:i') }}</p>
                    <p><i class="bi bi-arrow-clockwise me-2"></i>Обновлен: {{ $client->updated_at->format('d.m.Y H:i') }}</p>
                    
                    
                    
                    
                    
                    
                   <div class="mt-4">
    <h4>Сделки клиента</h4>
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
        <!-- Кнопка создания сделки -->
        @if(auth()->user()->isAdmin() || auth()->user()->isManager())
            @if($client->canCreateDeals())
                <a href="{{ route('deals.create', ['client_id' => $client->id]) }}" class="btn btn-outline-success w-100 mb-2">
                    <i class="fas fa-file-plus me-1"></i>Создать сделку
                </a>
            @else
                <button class="btn btn-success w-100 mb-2" disabled 
                        title="Невозможно создать сделку. У клиента уже есть активная сделка.">
                    <i class="fas fa-file-plus me-1"></i>Создать сделку
                    <br>
                    <small class="text-warning">Клиент уже в сделке</small>
                </button>
            @endif
        @endif

        <!-- Кнопка редактирования -->
        <a href="{{ route('clients.edit', $client) }}" class="btn btn-outline-warning w-100 mb-2">
            <i class="fas fa-edit me-1"></i>Редактировать данные
        </a>

      <!-- Кнопка удаления -->
<button type="button" 
        class="btn btn-outline-danger w-100 mb-2" 
        data-bs-toggle="modal" 
        data-bs-target="#deleteClientModal">
    <i class="fas fa-trash3 me-1"></i>Удалить клиента
</button>
    </div>
</div>
        </div>
		
		
		
		
		
<!-- Документы клиента -->
<div class="card mt-4  p-0">
    <div class="card-header bg-primary-lt">
       <h3 class="m-0"><i class="fas fa-folder-open"></i> Документы клиента</h3>
    </div>
    <div class="card-body">
        @if($client->documents->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Тип документа</th>
                            <th>Название</th>
                            <th>Номер</th>
                            <th>Дата выдачи</th>
                            <th>Срок действия</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($client->documents as $document)
                        <tr>
                            <td>
                                <i class="bi {{ $document->icon }} me-1"></i>
                                {{ $document->type_text }}
                            </td>
                            <td>{{ $document->name }}</td>
                            <td>{{ $document->document_number ?? '-' }}</td>
                            <td>{{ $document->issue_date ? $document->issue_date->format('d.m.Y') : '-' }}</td>
                            <td>
                                @if($document->expiry_date)
                                    {{ $document->expiry_date->format('d.m.Y') }}
                                    @if($document->isExpired())
                                        <span class="badge bg-deal-overdue ms-2">Просрочен</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                          <td>
   
        @if($document->file_path)
            <a href="{{ route('client.documents.show', basename($document->file_path)) }}" 
               target="_blank" 
               class="btn btn-outline-info btn-md"
               title="Просмотреть">
                <i class="fas fa-eye"></i>
            </a>
            
            <a href="{{ route('client.documents.download', basename($document->file_path)) }}" 
               class="btn btn-outline-primary"
               title="Скачать">
                <i class="fas fa-download"></i>
            </a>
        @endif
        
        <!-- Кнопка удаления документа -->
        @if(auth()->id() == $document->uploaded_by || auth()->user()->isAdmin())
            <form action="{{ route('clients.documents.destroy', ['client' => $client, 'document' => $document]) }}" 
                  method="POST" 
                  class="d-inline"
                  onsubmit="return confirm('Удалить документ \"{{ $document->name }}\"?')">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="btn btn-outline-danger"
                        title="Удалить документ">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        @endif
   
</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted">Документы не загружены</p>
        @endif
        
        <!-- Кнопка добавления документов -->
        <button class="btn btn-sm btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addDocumentModal">
            <i class="fas fa-plus-circle me-1"></i>Добавить документ
        </button>
    </div>
</div>

<!-- Модальное окно добавления документа -->
<div class="modal fade" id="addDocumentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary-lt">
                <h5 class="modal-title">Добавить документ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('clients.documents.store', $client) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Тип документа *</label>
                        <select name="type" class="form-select" required>
                            @foreach(App\Models\ClientDocument::getTypes() as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Название документа *</label>
                        <input type="text" name="name" class="form-control" required>
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
                        <label class="form-label">Файл документа *</label>
                        <input type="file" name="document_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Описание</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Загрузить</button>
                </div>
            </form>
        </div>
    </div>
</div>
		
		
		
		
		
		
		
		
		
		
		
		
    </div>
</div>


<!-- Модальное окно для подтверждения удаления клиента -->
<div class="modal fade" id="deleteClientModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle-fill me-2"></i>
                    Подтверждение удаления клиента
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('clients.destroy', $client) }}" method="POST" id="deleteClientForm">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-octagon-fill me-2"></i>
                        <strong>Внимание! Это действие необратимо.</strong>
                    </div>
                    
                    <p>Вы собираетесь удалить клиента:</p>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">{{ $client->full_name }}</h5>
                            <p class="card-text mb-1">
                                <small class="text-muted">Паспорт:</small> {{ $client->passport_series }} {{ $client->passport_number }}<br>
                                <small class="text-muted">Телефон:</small> {{ $client->phone }}<br>
                                <small class="text-muted">Статус:</small> {{ $client->status_text }}
                            </p>
                        </div>
                    </div>
                    
                    <!-- Предупреждение, если клиент в сделке -->
                    @if(!$client->canBeDeleted())
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle-fill me-2"></i>
                            <strong>Невозможно удалить клиента!</strong>
                            <p class="mb-0">У клиента есть активные сделки. Сначала завершите или отмените все сделки.</p>
                        </div>
                    @else
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-trash3 me-2"></i>При удалении клиента будут безвозвратно удалены:</h6>
                            <ul class="mb-0">
                                <li>Все документы клиента ({{ $client->documents->count() }} шт.)</li>
                                <li>Вся история взаимодействия</li>
                                <li>Все связанные данные</li>
                            </ul>
                        </div>
                        
                        <hr>
                        
                        <div class="mb-3">
                            <label for="clientConfirmationText" class="form-label">
                                Для подтверждения удаления введите слово <strong>"УДАЛИТЬ"</strong> в поле ниже:
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="clientConfirmationText" 
                                   name="confirmation_text"
                                   placeholder="Введите УДАЛИТЬ"
                                   required>
                            <div class="form-text text-danger">
                                <i class="bi bi-shield-exclamation me-1"></i>
                                Это действие невозможно отменить
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times-circle me-1"></i>Отмена
                    </button>
                    @if($client->canBeDeleted())
                        <button type="submit" class="btn btn-danger" id="confirmDeleteClientBtn" disabled>
                            <i class="fas fa-trash3 me-1"></i>Удалить клиента
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const confirmationInput = document.getElementById('clientConfirmationText');
    const confirmDeleteBtn = document.getElementById('confirmDeleteClientBtn');
    const deleteClientForm = document.getElementById('deleteClientForm');
    
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
        if (deleteClientForm) {
            deleteClientForm.addEventListener('submit', function(e) {
                const inputValue = confirmationInput.value.trim().toUpperCase();
                
                if (inputValue !== 'УДАЛИТЬ') {
                    e.preventDefault();
                    confirmationInput.classList.add('is-invalid');
                    confirmationInput.focus();
                    
                    // Показываем сообщение об ошибке
                    if (!document.getElementById('confirmationClientError')) {
                        const errorDiv = document.createElement('div');
                        errorDiv.id = 'confirmationClientError';
                        errorDiv.className = 'invalid-feedback d-block mt-2';
                        errorDiv.innerHTML = '<i class="bi bi-exclamation-circle me-1"></i>Пожалуйста, введите слово "УДАЛИТЬ" для подтверждения';
                        confirmationInput.parentNode.appendChild(errorDiv);
                    }
                    
                    return false;
                }
                
                // Дополнительное подтверждение
                if (!confirm('Вы уверены, что хотите окончательно удалить этого клиента и все связанные данные?')) {
                    e.preventDefault();
                    return false;
                }
                
                // Блокируем кнопку, чтобы избежать двойного нажатия
                confirmDeleteBtn.disabled = true;
                confirmDeleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Удаление...';
            });
        }
        
        // Сброс состояния модального окна при закрытии
        const deleteModal = document.getElementById('deleteClientModal');
        if (deleteModal) {
            deleteModal.addEventListener('hidden.bs.modal', function() {
                if (confirmationInput) {
                    confirmationInput.value = '';
                    confirmationInput.classList.remove('is-valid', 'is-invalid');
                }
                if (confirmDeleteBtn) {
                    confirmDeleteBtn.disabled = true;
                    confirmDeleteBtn.innerHTML = '<i class="fas fa-trash3 me-1"></i>Удалить клиента';
                }
                
                // Удаляем сообщение об ошибке
                const errorDiv = document.getElementById('confirmationClientError');
                if (errorDiv) {
                    errorDiv.remove();
                }
            });
        }
    }
});
</script>
<!-- Подключаем модальное окно истории сделок -->
@include('clients.partials.deal-history')

<!-- JavaScript для модального окна -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация модального окна
    const dealHistoryModal = document.getElementById('dealHistoryModal');
    
    if (dealHistoryModal) {
        // При открытии модального окна можно загрузить данные через AJAX
        dealHistoryModal.addEventListener('show.bs.modal', function(event) {
            // Если нужно загружать через AJAX при каждом открытии
            // loadDealHistory(1);
        });
        
        // Инициализация тултипов Bootstrap внутри модального окна
        dealHistoryModal.addEventListener('shown.bs.modal', function() {
            const tooltipTriggerList = [].slice.call(
                dealHistoryModal.querySelectorAll('[data-bs-toggle="tooltip"]')
            );
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    }
    
    // Функция для загрузки истории сделок через AJAX (если нужно)
    function loadDealHistory(page = 1) {
        const clientId = {{ $client->id }};
        const url = `/clients/${clientId}/deal-history?page=${page}`;
        
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            document.getElementById('dealHistoryTableBody').innerHTML = html;
            
            // Обновляем URL пагинации без перезагрузки страницы
            history.pushState(null, '', `?page=${page}`);
        })
        .catch(error => {
            console.error('Ошибка загрузки истории сделок:', error);
            showAlert('Ошибка загрузки данных', 'danger');
        });
    }
    
    // Функция для отображения сообщений
    function showAlert(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show mt-3`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const modalBody = document.querySelector('#dealHistoryModal .modal-body');
        const firstChild = modalBody.firstChild;
        modalBody.insertBefore(alertDiv, firstChild);
        
        // Автоматическое скрытие через 5 секунд
        setTimeout(() => {
            if (alertDiv.parentNode) {
                const bsAlert = new bootstrap.Alert(alertDiv);
                bsAlert.close();
            }
        }, 5000);
    }
});

// Функция для загрузки конкретной страницы
function loadDealHistoryPage(page) {
    event.preventDefault();
    loadDealHistory(page);
}
</script>

<!-- Дополнительные стили -->
<style>
/* Стили для модального окна внутри страницы */
#dealHistoryModal .modal-header {
    background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
}

#dealHistoryModal .badge {
    font-size: 0.75em;
    padding: 0.25em 0.6em;
}

#dealHistoryModal .table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

#dealHistoryModal .progress {
    height: 6px;
    margin-top: 5px;
}

#dealHistoryModal .btn-group .btn {
    border-radius: 4px;
}

#dealHistoryModal .modal-footer {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
}
</style>

@endsection