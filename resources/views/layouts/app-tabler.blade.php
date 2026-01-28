<!DOCTYPE html>
<html lang="ru" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'CRM Лизинг')</title>
    
    <!-- Tabler Core CSS -->
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler-vendors.min.css" rel="stylesheet">
    
    <!-- Font Awesome (для иконок) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Select2 CSS (если используется) -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    
    <!-- Кастомные стили -->
    <style>
        /* Ваши текущие стили */
        .cursor-pointer { cursor: pointer; }
        .action-buttons { white-space: nowrap; }
        .action-buttons .btn { margin-right: 3px; }
        
        /* Адаптация для Tabler */
        .table th { font-weight: 600; }
        .badge { font-weight: 500; }
        
        /* Стили для статусов */
        .status-available { background-color: #2fb344; }
        .status-in_deal { background-color: #f59f00; }
        .status-maintenance { background-color: #4299e1; }
        .status-sold { background-color: #a0aec0; }
        
        /* Стили для прогресс-баров в сделках */
        .time-progress { height: 10px; border-radius: 5px; }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Header -->
    <header class="navbar navbar-expand-md navbar-light d-print-none">
        <div class="container-xl">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
                <a href="{{ url('/') }}" class="text-decoration-none">
                    <i class="fas fa-car me-2"></i>
                    <span class="fw-bold">CRM Лизинг</span>
                </a>
            </h1>
            
            <!-- Навигация -->
            <div class="navbar-nav flex-row order-md-last">
                @auth
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown">
                        <div class="d-none d-xl-block ps-2">
                            <div>{{ auth()->user()->name }}</div>
                            <div class="mt-1 small text-muted">{{ auth()->user()->role_text }}</div>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="#">
                            <i class="fas fa-user me-2"></i>Профиль
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="fas fa-sign-out-alt me-2"></i>Выйти
                            </button>
                        </form>
                    </div>
                </div>
                @endauth
            </div>
        </div>
    </header>
    
    <!-- Основная навигация -->
    <div class="navbar-expand-md">
        <div class="collapse navbar-collapse" id="navbar-menu">
            <div class="navbar navbar-light">
                <div class="container-xl">
                    <ul class="navbar-nav">
                        <!-- Сделки -->
                        <li class="nav-item {{ request()->routeIs('deals.*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('deals.index') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <i class="fas fa-file-contract"></i>
                                </span>
                                <span class="nav-link-title">Сделки</span>
                            </a>
                        </li>
                        
                        <!-- Клиенты -->
                        <li class="nav-item {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('clients.index') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <i class="fas fa-users"></i>
                                </span>
                                <span class="nav-link-title">Клиенты</span>
                            </a>
                        </li>
                        
                        <!-- Автомобили -->
                        <li class="nav-item {{ request()->routeIs('cars.*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('cars.index') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <i class="fas fa-car"></i>
                                </span>
                                <span class="nav-link-title">Автомобили</span>
                            </a>
                        </li>
                        
                        <!-- Для админа -->
                        @if(auth()->check() && auth()->user()->isAdmin())
                        <li class="nav-item {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('admin.users.index') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <i class="fas fa-cog"></i>
                                </span>
                                <span class="nav-link-title">Администрирование</span>
                            </a>
                        </li>
                        @endif
                        
                        <!-- Для инвестора -->
                        @if(auth()->check() && auth()->user()->isInvestor())
                        <li class="nav-item {{ request()->routeIs('investor.*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('investor.dashboard') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <i class="fas fa-chart-line"></i>
                                </span>
                                <span class="nav-link-title">Инвестиции</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Основной контент -->
    <div class="page-wrapper">
        <div class="container-xl">
           <!-- Хлебные крошки - упрощенная версия -->
@hasSection('title')
<div class="page-header d-print-none">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title">@yield('title', '')</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
            @yield('header-actions', '')
        </div>
    </div>
</div>
@endif
            
            <!-- Сообщения -->
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            
            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            
            <!-- Основной контент -->
            <div class="page-body">
                @yield('content')
            </div>
        </div>
        
        <!-- Футер -->
        <footer class="footer footer-transparent d-print-none">
            <div class="container-xl">
                <div class="row text-center align-items-center flex-row-reverse">
                    <div class="col-lg-auto ms-lg-auto">
                        <ul class="list-inline list-inline-dots mb-0">
                            <li class="list-inline-item">© {{ date('Y') }} CRM Лизинг</li>
                            <li class="list-inline-item">v1.0.0</li>
                        </ul>
                    </div>
                    <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                        <ul class="list-inline list-inline-dots mb-0">
                            <li class="list-inline-item">
                                <a href="#" class="link-secondary">Помощь</a>
                            </li>
                            <li class="list-inline-item">
                                <a href="#" class="link-secondary">Конфиденциальность</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    
    <!-- Скрипты -->
    <!-- jQuery (для Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Tabler Core JS -->
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/js/tabler.min.js"></script>
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/ru.js"></script>
    
    <!-- Кастомные скрипты -->
    <script>
        // Инициализация Select2
        document.addEventListener('DOMContentLoaded', function() {
            // Автоматическая инициализация Select2
            $('select[class*="select2"]').each(function() {
                $(this).select2({
                    theme: 'bootstrap-5',
                    language: 'ru',
                    width: '100%',
                    dropdownParent: $(this).closest('.modal, .card-body, .page-body')
                });
            });
            
            // Инициализация tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Инициализация popovers
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>