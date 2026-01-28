@extends('layouts.app')

@section('title', 'Сделка ' . $deal->deal_number)

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2><i class="fas fa-file-contract me-2"></i>Сделка: {{ $deal->deal_number }}</h2>
            <p class="text-muted">{{ $deal->deal_type_text }} • Создана: {{ $deal->created_at->format('d.m.Y H:i') }}</p>
        </div>
        <div class="col-md-6 text-end">
            @if($deal->status === 'draft')
                <a href="{{ route('deals.edit', $deal) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-1"></i>Редактировать
                </a>
            @endif
            
            @if($deal->status === 'active' && $deal->next_payment)
                <form action="{{ route('deals.send-reminder', $deal) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success" 
                            onclick="return confirm('Отправить SMS напоминание клиенту?')">
                        <i class="fas fa-bell me-1"></i>Напомнить
                    </button>
                </form>
            @endif
            
            <a href="{{ route('deals.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Назад
            </a>
        </div>
    </div>

    <!-- Статус и прогресс -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            @php
                                $statusColors = [
                                    'draft' => 'deal-draw',
                                    'active' => 'deal-active',
                                    'completed' => 'deal-end',
                                    'cancelled' => 'deal-overdue',
                                    'overdue' => 'deal-overdue'
                                ];
                                $statusColor = $statusColors[$deal->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $statusColor }} fs-3 p-3">
                                {{ $deal->status_text }}
                            </span>
                        </div>
                        <div class="col-md-9">
                            <div class="progress" style="height: 30px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: {{ $deal->payment_progress }}%"
                                     aria-valuenow="{{ $deal->payment_progress }}" 
                                     aria-valuemin="0" aria-valuemax="100">
                                    {{ number_format($deal->payment_progress, 1) }}%
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <small>Оплачено: {{ number_format($deal->total_paid, 0, '', ' ') }} ₽</small>
                                <small>Всего: {{ number_format($deal->total_amount, 0, '', ' ') }} ₽</small>
                                <small>Осталось: {{ number_format($deal->remaining_amount, 0, '', ' ') }} ₽</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        

        
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    @if($deal->next_payment_due_date)
                        <h3 class="m-0">Следующий платеж</h3>
                        <div class="fs-4 fw-bold 
                            {{ $deal->days_to_next_payment <= 3 ? 'text-danger' : 'text-success' }}">
                            {{ $deal->next_payment_due_date->format('d.m.Y') }}
                        </div>
                        <p>
                            @if($deal->days_to_next_payment > 0)
                                Через {{ $deal->days_to_next_payment }} дн.
                            @elseif($deal->days_to_next_payment === 0)
                                Сегодня
                            @else
                                Просрочено на {{ abs($deal->days_to_next_payment) }} дн.
                            @endif
                        </p>
                        <p class="mb-0">
                            <strong>{{ number_format($deal->next_payment->amount, 0, '', ' ') }} ₽</strong>
                        </p>
                    @else
                        <h3 class="mb-3 mt-3">Все платежи выполнены</h3>
                        
                    @endif
                </div>
            </div>
        </div>
    </div>
    

    
    
    

    <div class="row">
        <!-- Основная информация -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary-lt">
                    <h3 class="m-0"><i class="fas fa-users"></i> Участники сделки</h3>
                </div>
                <div class="card-body">
                    <h6 class="m-0">Клиент:</h6>
                    <p>
                        <a href="{{ route('clients.show', $deal->client) }}" class="text-decoration-none">
                            <strong>{{ $deal->client->full_name }}</strong>
                        </a><br>
                        <small class="text-muted">{{ $deal->client->phone }}</small>
                    </p>
                    
                    <h6 class="mt-3 m-0">Автомобиль:</h6>
                    <p>
                        <a href="{{ route('cars.show', $deal->car) }}" class="text-decoration-none">
                            <strong>{{ $deal->car->brand }} {{ $deal->car->model }}</strong>
                        </a><br>
                        <small class="text-muted">{{ $deal->car->license_plate }} • {{ $deal->car->color }}</small>
                    </p>
                    
                    <h6 class="mt-3 m-0">Менеджер:</h6>
                    <p>
                        <strong>{{ $deal->manager->name }}</strong><br>
                        <small class="text-muted">Ответственный</small>
                    </p>
                </div>
            </div>
            
            <!-- Параметры сделки -->
            <div class="card mb-4">
                <div class="card-header bg-primary-lt">
                    <h3 class="m-0"><i class="fas fa-cog"></i> Параметры сделки</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Тип:</strong></td>
                            <td>{{ $deal->deal_type_text }}</td>
                        </tr>
                        <tr>
                            <td><strong>Общая сумма:</strong></td>
                            <td>{{ number_format($deal->total_amount, 0, '', ' ') }} ₽</td>
                        </tr>
                        <tr>
                            <td><strong>Первоначальный взнос:</strong></td>
                            <td>{{ number_format($deal->initial_payment, 0, '', ' ') }} ₽</td>
                        </tr>
                        <tr>
                            <td><strong>Период оплаты:</strong></td>
                            <td>{{ $deal->payment_period_text }}</td>
                        </tr>
                        <tr>
                            <td><strong>Количество платежей:</strong></td>
                            <td>{{ $deal->payment_count }}</td>
                        </tr>
                        <tr>
                            <td><strong>Сумма платежа:</strong></td>
                            <td>{{ number_format($deal->payment_amount, 0, '', ' ') }} ₽</td>
                        </tr>
                        <tr>
                            <td><strong>Дата начала:</strong></td>
                            <td>{{ $deal->start_date->format('d.m.Y') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Дата окончания:</strong></td>
                            <td>{{ $deal->end_date->format('d.m.Y') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Дней осталось:</strong></td>
                            <td class="{{ $deal->days_remaining <= 7 ? 'text-danger fw-bold' : '' }}">
                                {{ $deal->days_remaining ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td><strong>SMS уведомления:</strong></td>
                            <td>
                                @if($deal->sms_notifications)
                                    <span class="badge bg-free">Включены</span>
                                @else
                                    <span class="badge bg-deal-overdue">Отключены</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
      <!-- Договор -->
<div class="card">
    <div class="card-header bg-primary-lt">
        <h3 class="m-0"><i class="fas fa-file"></i> Договор</h3>
    </div>
    <div class="card-body">
        @if($deal->contract_path)
            <!-- Если договор уже загружен (подписанный) -->
            <div class="alert alert-success">
                <i class="fas fa-check-circle-fill me-2"></i>
                <strong>Договор подписан и загружен</strong>
            </div>
            
            <div class="mb-3">
                <p>
                    <i class="bi bi-file-pdf text-danger me-1"></i>
                    <strong>Подписанный договор</strong><br>
                    <small>Загружен: {{ $deal->contract_signed_date->format('d.m.Y') }}</small>
                </p>
                
                <div class="btn-group" role="group">
                    <a href="{{ route('deals.contract.download', $deal) }}" 
                       class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-download me-1"></i>Скачать подписанный договор
                    </a>
                    
                    <a href="{{ route('deals.contract.view', $deal) }}" 
                       target="_blank"
                       class="btn btn-outline-info btn-sm">
                        <i class="fas fa-eye me-1"></i>Просмотреть
                    </a>
                </div>
            </div>
            
            @if($deal->status === 'draft')
                <div class="border-top pt-3">
                    <p class="text-muted">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            Договор загружен, но сделка еще не активирована. 
                            Проверьте правильность договора и нажмите "Активировать сделку".
                        </small>
                    </p>
                </div>
            @endif
            
        @else
            <!-- Если договор еще не загружен -->
            @if($deal->status === 'draft')
                <div class="mb-3">
                    <p class="text-muted">Шаблон договора не сгенерирован</p>
                    
                    <div class="btn-group" role="group">
                        <a href="{{ route('deals.contract.template.generate', $deal) }}" 
                           class="btn btn-outline-success btn-sm">
                            <i class="fas fa-file-plus me-1"></i>Сгенерировать шаблон договора
                        </a>
                        
                        <a href="{{ route('deals.contract.template.preview', $deal) }}" 
                           target="_blank"
                           class="btn btn-outline-info btn-sm">
                            <i class="fas fa-eye me-1"></i>Предпросмотр шаблона
                        </a>
                    </div>
                </div>
                
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Инструкция:</strong>
                    <ol class="mb-0 mt-2">
                        <li>Сгенерируйте шаблон договора</li>
                        <li>Скачайте и отредактируйте при необходимости</li>
                        <li>Распечатайте и подпишите с клиентом</li>
                        <li>Загрузите подписанный договор для активации сделки</li>
                    </ol>
                </div>
            @else
                <p class="text-muted">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Договор не загружен
                </p>
            @endif
        @endif
        
        <!-- Форма загрузки подписанного договора (только для черновиков) -->
        @if($deal->status === 'draft')
            <div class="border-top pt-3 mt-3">
                <h6><i class="fas fa-upload me-1"></i>Загрузить подписанный договор</h6>
                <p class="text-muted small mb-2">После подписания договора с клиентом загрузите его для активации сделки</p>
                
                <form action="{{ route('deals.upload-contract', $deal) }}" method="POST" enctype="multipart/form-data" id="uploadContractForm">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label small">Дата подписания *</label>
                                <input type="date" name="contract_signed_date" class="form-control form-control-sm" 
                                       value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label small">Файл договора *</label>
                                <input type="file" name="contract_file" class="form-control form-control-sm" 
                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning mt-2 mb-2 py-2">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <small>После загрузки договора сделка будет активирована автоматически</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-md mt-1">
                        <i class="fas fa-upload me-1"></i>Загрузить и активировать сделку
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
        </div>
        
        <!-- График платежей -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary-lt">
                    <h3 class="m-0"><i class="fas fa-calendar"></i> График платежей</h3>
                </div>
                <div class="card-body">
                    @if($deal->payments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>№</th>
                                        <th>Дата платежа</th>
                                        <th>Сумма</th>
                                        <th>Статус</th>
                                        <th>Дата оплаты</th>
                                        <th>Способ оплаты</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($deal->payments as $payment)
                                    <tr class="{{ $payment->is_overdue ? 'table-danger' : '' }}">
                                        <td>
                                            @if($payment->payment_number === 0)
                                                <span class="badge bg-deal-end">Первоначальный</span>
                                            @else
                                                {{ $payment->payment_number }}
                                            @endif
                                        </td>
                                        <td>
                                            {{ $payment->due_date->format('d.m.Y') }}
                                            @if($payment->is_overdue)
                                                <br>
                                                <small class="text-danger">
                                                    Просрочено на {{ $payment->days_overdue }} дн.
                                                </small>
                                            @endif
                                        </td>
                                        <td>{{ $payment->formatted_amount }}</td>
                                        <td>
                                            @php
                                                $paymentStatusColors = [
                                                    'pending' => 'deal-active',
                                                    'paid' => 'free',
                                                    'overdue' => 'deal-overdue'
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $paymentStatusColors[$payment->status] ?? 'secondary' }}">
                                                {{ $payment->status_text }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($payment->paid_at)
                                                {{ $payment->paid_at->format('d.m.Y H:i') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($payment->payment_method)
                                                {{ $payment->payment_method_text }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($payment->status === 'pending')
                                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" 
                                                        data-bs-target="#registerPaymentModal" 
                                                        data-payment-id="{{ $payment->id }}"
                                                        data-payment-amount="{{ $payment->amount }}">
                                                    <i class="fas fa-money-bill-coin"></i> Оплатить
                                                </button>
                                            @elseif($payment->status === 'paid')
                                                <button class="btn btn-sm btn-info payment-details-btn" 
                                                        data-payment-id="{{ $payment->id }}"
                                                        title="Детали платежа">
                                                    <i class="fas fa-info-circle me-2"></i> Подробности
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        
                        
                        
                                    <!-- Скачивание графика платежей -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-file-pdf text-danger me-2"></i>
                    График платежей
                </h5>
                <p class="card-text">
                    <small class="text-muted">
                        Скачайте график платежей в формате PDF. Документ является официальным Приложением №2 к договору.
                    </small>
                </p>
                <div class="btn-group" role="group">
                    <a href="{{ route('deals.payment-schedule.download', $deal) }}" 
                       class="btn btn-danger">
                        <i class="fas fa-download me-1"></i> Скачать график платежей (PDF)
                    </a>
                    
                    <a href="{{ route('deals.payment-schedule.preview', $deal) }}" 
                       target="_blank"
                       class="btn btn-outline-secondary">
                        <i class="fas fa-eye me-1"></i> Предварительный просмотр
                    </a>
                </div>
                
                @if($deal->contract_signed_date)
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Документ составлен на основании договора № {{ $deal->deal_number }} 
                            от {{ $deal->contract_signed_date->format('d.m.Y') }}
                        </small>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
                        
                        
                        
                        
                        
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <div class="alert alert-success">
                                    <strong>Оплачено:</strong><br>
                                    {{ number_format($deal->payments->where('status', 'paid')->sum('amount'), 0, '', ' ') }} ₽
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="alert alert-warning">
                                    <strong>Ожидает оплаты:</strong><br>
                                    {{ number_format($deal->payments->where('status', 'pending')->sum('amount'), 0, '', ' ') }} ₽
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="alert alert-danger">
                                    <strong>Просрочено:</strong><br>
                                    {{ number_format($deal->payments->where('status', 'overdue')->sum('amount'), 0, '', ' ') }} ₽
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-muted">График платежей не сформирован</p>
                    @endif
                </div>
            </div>
            
            <!-- История уведомлений -->
            <div class="card">
                <div class="card-header bg-primary-lt">
                    <h3 class="m-0"><i class="fas fa-paper-plane"></i> История уведомлений</h3>
                </div>
                <div class="card-body">
                    @if($deal->notifications->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Дата</th>
                                        <th>Тип</th>
                                        <th>Статус</th>
                                        <th>Сообщение</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($deal->notifications->sortByDesc('created_at')->take(10) as $notification)
                                    <tr>
                                        <td>{{ $notification->created_at->format('d.m.Y H:i') }}</td>
                                        <td>{{ $notification->type_text }}</td>
                                        <td>
                                            <span class="badge bg-{{ $notification->status === 'sent' ? 'success' : ($notification->status === 'failed' ? 'danger' : 'warning') }}">
                                                {{ $notification->status_text }}
                                            </span>
                                        </td>
                                        <td>
                                            <small>{{ Str::limit($notification->message, 50) }}</small>
                                            @if($notification->error)
                                                <br>
                                                <small class="text-danger">{{ $notification->error }}</small>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">Уведомления не отправлялись</p>
                    @endif
                    
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Всего отправлено SMS: {{ $deal->sms_count }}
                            @if($deal->last_sms_sent_at)
                                • Последнее: {{ $deal->last_sms_sent_at->format('d.m.Y H:i') }}
                            @endif
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Заметки -->
    @if($deal->notes)
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Заметки менеджера</h5>
                </div>
                <div class="card-body">
                    <div class="bg-light p-3 rounded">
                        {{ $deal->notes }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Модальное окно загрузки договора -->
<div class="modal fade" id="uploadContractModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Загрузка договора</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('deals.upload-contract', $deal) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Дата подписания договора *</label>
                        <input type="date" name="contract_signed_date" class="form-control" 
                               value="{{ date('Y-m-d') }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Файл договора *</label>
                        <input type="file" name="contract_file" class="form-control" 
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                        <small class="text-muted">Поддерживаемые форматы: PDF, DOC, DOCX, JPG, PNG</small>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-1"></i>
                        После загрузки договора сделка будет активирована.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Загрузить и активировать</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальное окно регистрации платежа -->
<div class="modal fade" id="registerPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Регистрация платежа</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="registerPaymentForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="payment-id" name="payment_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Сумма платежа</label>
                        <input type="text" id="payment-amount-display" class="form-control" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Способ оплаты *</label>
                        <select name="payment_method" class="form-select" required>
                            @foreach(App\Models\DealPayment::getPaymentMethods() as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Номер транзакции/чека</label>
                        <input type="text" name="transaction_id" class="form-control" 
                               placeholder="Необязательно">
                    </div>
                    
                    <!-- Платежный документ -->
                    <div class="mb-3">
                        <label class="form-label">Платежный документ</label>
                        <input type="file" name="payment_document" class="form-control" 
                               accept=".jpg,.jpeg,.png,.pdf">
                        <small class="text-muted">
                            Прикрепите чек, квитанцию или другой документ об оплате (JPG, PNG, PDF, до 5MB)
                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Заметки</label>
                        <textarea name="notes" class="form-control" rows="3" 
                                  placeholder="Дополнительная информация о платеже..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Зарегистрировать платеж</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальное окно деталей платежа -->
<div class="modal fade" id="paymentDetailsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Детали платежа</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="paymentDetailsContent">
                <!-- Контент будет загружен через JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Модальное окно регистрации платежа
    const registerPaymentModal = document.getElementById('registerPaymentModal');
    if (registerPaymentModal) {
        registerPaymentModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const paymentId = button.getAttribute('data-payment-id');
            const paymentAmount = button.getAttribute('data-payment-amount');
            
            const modal = this;
            const amountDisplay = modal.querySelector('#payment-amount-display');
            const paymentIdInput = modal.querySelector('#payment-id');
            const form = modal.querySelector('#registerPaymentForm');
            
            // Форматируем сумму
            amountDisplay.value = parseFloat(paymentAmount).toLocaleString('ru-RU', {
                minimumFractionDigits: 2
            }) + ' ₽';
            
            // Устанавливаем ID платежа
            paymentIdInput.value = paymentId;
            
            // Устанавливаем action формы
            form.action = `{{ route('deals.payments.register', ['deal' => $deal, 'payment' => ':payment_id']) }}`.replace(':payment_id', paymentId);
        });
    }
    
    // Градиент для дней до платежа
    const daysElements = document.querySelectorAll('[class*="days-remaining"]');
    daysElements.forEach(el => {
        const days = parseInt(el.textContent);
        if (!isNaN(days)) {
            if (days <= 0) {
                el.classList.add('text-danger', 'fw-bold');
            } else if (days <= 7) {
                el.classList.add('text-warning', 'fw-bold');
            } else {
                el.classList.add('text-success');
            }
        }
    });
    
    // Модальное окно деталей платежа
    const paymentDetailsModal = document.getElementById('paymentDetailsModal');
    const paymentDetailsContent = document.getElementById('paymentDetailsContent');
    
    // Обработчики для кнопок "Подробности"
    document.querySelectorAll('.payment-details-btn').forEach(button => {
        button.addEventListener('click', function() {
            const paymentId = this.getAttribute('data-payment-id');
            loadPaymentDetails(paymentId);
        });
    });
    
    // Функция загрузки деталей платежа
    function loadPaymentDetails(paymentId) {
        console.log('Загрузка деталей платежа ID:', paymentId);
        
        // Показываем индикатор загрузки
        paymentDetailsContent.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Загрузка...</span>
                </div>
                <p class="mt-2">Загрузка данных...</p>
            </div>
        `;
        
        // Показываем модальное окно
        const modal = new bootstrap.Modal(paymentDetailsModal);
        modal.show();
        
        // Формируем URL
        const url = `{{ route('deals.payments.details', ['deal' => $deal, 'payment' => ':payment_id']) }}`.replace(':payment_id', paymentId);
        console.log('URL запроса:', url);
        
        // Загружаем данные через AJAX
        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log('Статус ответа:', response.status);
            
            if (!response.ok) {
                // Получаем текст ошибки
                return response.text().then(text => {
                    console.error('Текст ошибки:', text);
                    throw new Error(`HTTP error! status: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Полученные данные:', data);
            
            // Проверяем наличие ошибки
            if (data.error) {
                throw new Error(data.error);
            }
            
            // Форматируем дату
            let formattedDate = 'Не указана';
            if (data.paid_at) {
                try {
                    const paidAt = new Date(data.paid_at);
                    formattedDate = paidAt.toLocaleDateString('ru-RU', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                } catch (e) {
                    console.error('Ошибка форматирования даты:', e);
                    formattedDate = data.paid_at;
                }
            }
            
            // Создаем HTML с деталями платежа
            let html = `
                <div class="payment-details">
                    <h3 class="border-bottom pb-2 mb-3">Информация о платеже</h3>
                    
                    <div class="row mb-2">
                        <div class="col-6"><strong>Номер платежа:</strong></div>
                        <div class="col-6">${data.payment_number === 0 ? 'Первоначальный взнос' : '№' + data.payment_number}</div>
                    </div>
                    
                    <div class="row mb-2">
                        <div class="col-6"><strong>Сумма:</strong></div>
                        <div class="col-6">${parseFloat(data.amount).toLocaleString('ru-RU', {minimumFractionDigits: 2})} ₽</div>
                    </div>
                    
                    <div class="row mb-2">
                        <div class="col-6"><strong>Дата оплаты:</strong></div>
                        <div class="col-6">${formattedDate}</div>
                    </div>
                    
                    <div class="row mb-2">
                        <div class="col-6"><strong>Способ оплаты:</strong></div>
                        <div class="col-6">${data.payment_method_text || data.payment_method || 'Не указан'}</div>
                    </div>
            `;
            
            // Добавляем номер транзакции, если есть
            if (data.transaction_id) {
                html += `
                    <div class="row mb-2">
                        <div class="col-6"><strong>Номер транзакции:</strong></div>
                        <div class="col-6">${data.transaction_id}</div>
                    </div>
                `;
            }
            
            // Добавляем заметки, если есть
            if (data.notes) {
                html += `
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong>Заметки:</strong>
                            <div class="bg-light p-3 rounded mt-1">${data.notes}</div>
                        </div>
                    </div>
                `;
            }
            
            // Добавляем платежный документ, если есть
            if (data.payment_document_path) {
                const fileName = data.payment_document_path.split('/').pop();
                const fileExt = fileName.split('.').pop().toLowerCase();
                const fileIcon = fileExt === 'pdf' ? 'bi-file-pdf text-danger' : 
                                (fileExt === 'jpg' || fileExt === 'jpeg' || fileExt === 'png') ? 'bi-file-image text-success' : 'bi-file-text';
                
                // Правильные пути для документов
                const viewUrl = `{{ route('payment.documents.view', ':filename') }}`.replace(':filename', fileName);
                const downloadUrl = `{{ route('payment.documents.download', ':filename') }}`.replace(':filename', fileName);
                
                html += `
                    <div class="row mb-2">
                        <div class="col-12">
                            <strong>Платежный документ:</strong>
                            <div class="mt-2">
                                <div class="d-flex align-items-center">
                                    <i class="bi ${fileIcon} me-2 fs-5"></i>
                                    <div>
                                        <div>${fileName}</div>
                                        <div class="btn-group btn-group-sm mt-1">
                                            <a href="${viewUrl}" target="_blank" class="btn btn-outline-primary">
                                                <i class="fas fa-eye"></i> Просмотреть
                                            </a>
                                            <a href="${downloadUrl}" class="btn btn-outline-secondary">
                                                <i class="fas fa-download"></i> Скачать
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                html += `
                    <div class="row mb-2">
                        <div class="col-12">
                            <strong>Платежный документ:</strong>
                            <div class="mt-2 text-muted">
                                <i class="bi bi-file-excel me-1"></i> Документ не прикреплен
                            </div>
                        </div>
                    </div>
                `;
            }
            
            html += `</div>`;
            
            // Вставляем HTML в модальное окно
            paymentDetailsContent.innerHTML = html;
        })
        .catch(error => {
            console.error('Ошибка загрузки данных:', error);
            paymentDetailsContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Ошибка при загрузке данных платежа.</strong><br>
                    <small>${error.message}</small>
                    <div class="mt-2">
                        <button class="btn btn-sm btn-outline-primary" onclick="loadPaymentDetails(${paymentId})">
                            <i class="bi bi-arrow-clockwise"></i> Попробовать снова
                        </button>
                    </div>
                </div>
            `;
        });
    }
});
</script>
@endpush

<style>
    /* Градиент для прогресс-бара */
    .progress-bar-gradient {
        background: linear-gradient(90deg, #28a745 0%, #ffc107 50%, #dc3545 100%);
    }
    
    /* Стили для таблицы платежей */
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
    
    /* Выделение просроченных платежей */
    .table-danger {
        background-color: rgba(220, 53, 69, 0.1) !important;
    }
</style>
@endsection