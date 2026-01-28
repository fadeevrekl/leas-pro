@if($client->deals->count() > 0)
    @foreach($deals as $deal)
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
            <span class="badge bg-secondary">
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
                    'draft' => 'info',
                    'active' => 'success',
                    'completed' => 'warning',
                    'cancelled' => 'danger',
                    'overdue' => 'danger'
                ];
                $color = $statusColors[$deal->status] ?? 'secondary';
            @endphp
            <span class="badge bg-{{ $color }}">
                {{ $deal->status_text }}
            </span>
        </td>
        <td>
            <div class="btn-group btn-group-sm">
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
            </div>
        </td>
    </tr>
    @endforeach
    
    <!-- Пагинация -->
    @if($deals->hasPages())
    <tr>
        <td colspan="8" class="text-center">
            <nav class="d-flex justify-content-center">
                <ul class="pagination pagination-sm mb-0">
                    @if($deals->onFirstPage())
                        <li class="page-item disabled">
                            <span class="page-link">‹</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="#" onclick="loadDealHistoryPage({{ $deals->currentPage() - 1 }})">‹</a>
                        </li>
                    @endif

                    @foreach(range(1, $deals->lastPage()) as $page)
                        @if($page == $deals->currentPage())
                            <li class="page-item active">
                                <span class="page-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="#" onclick="loadDealHistoryPage({{ $page }})">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach

                    @if($deals->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="#" onclick="loadDealHistoryPage({{ $deals->currentPage() + 1 }})">›</a>
                        </li>
                    @else
                        <li class="page-item disabled">
                            <span class="page-link">›</span>
                        </li>
                    @endif
                </ul>
            </nav>
        </td>
    </tr>
    @endif
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