{{-- Навигация для кабинета инвестора --}}
<div class="card mb-4">
    <div class="card-body p-2">
        <div class="d-flex justify-content-between align-items-center">
            {{-- Левый блок: Название раздела --}}
            <div class="d-flex align-items-center">
                <div class="me-3">
                    @if(request()->routeIs('investor.dashboard'))
                        <i class="fas fa-car fs-3 text-primary"></i>
                    @elseif(request()->routeIs('investor.investments'))
                        <i class="fas fa-chart-line fs-3 text-success"></i>
                    @elseif(request()->routeIs('investor.investments.advanced'))
                        <i class="fas fa-bar-chart fs-3 text-warning"></i>
                    @endif
                </div>
                <div>
                    <h4 class="mb-0">
                        @if(request()->routeIs('investor.dashboard'))
                            Мои автомобили
                        @elseif(request()->routeIs('investor.investments'))
                            Базовая аналитика
                        @elseif(request()->routeIs('investor.investments.advanced'))
                            Расширенный анализ
                        @endif
                    </h4>
                    <small class="text-muted">
                       
                        | Автомобилей: {{ request()->routeIs('investor.dashboard') ? $cars->count() : $stats['total_cars'] ?? 0 }}
                    </small>
                </div>
            </div>
            
            {{-- Правый блок: Кнопки навигации --}}
            <div class="" role="group" aria-label="Навигация по кабинету">
                {{-- Дашборд --}}
                <a href="{{ route('investor.dashboard') }}" 
                   class="btn btn-{{ request()->routeIs('investor.dashboard') ? 'primary' : 'outline-primary' }}">
                    <i class="fas fa-car me-1"></i>Автомобили
                </a>
                
                {{-- Базовая аналитика --}}
                <a href="{{ route('investor.investments') }}" 
                   class="btn btn-{{ request()->routeIs('investor.investments') ? 'primary' : 'outline-primary' }}">
                    <i class="fas fa-chart-line me-1"></i>Аналитика
                </a>
                
                {{-- Расширенный анализ --}}
                <a href="{{ route('investor.investments.advanced') }}" 
                   class="btn btn-{{ request()->routeIs('investor.investments.advanced') ? 'primary' : 'outline-primary' }}">
                    <i class="fas fa-bar-chart me-1"></i>Расширенный
                </a>
                
                {{-- Печать отчета --}}
                <button type="button" class="btn btn-outline-success" onclick="window.print()">
                    <i class="fas fa-print me-1"></i>Печать
                </button>
            </div>
        </div>
    </div>
</div>