<!DOCTYPE html>
<html lang="ru" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'CRM Лизинг Автомобилей')</title>
    
    <!-- Tabler Core CSS -->
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler-vendors.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    
    <!--Стили для тура-->
    <link rel="stylesheet" href="https://unpkg.com/shepherd.js@8.3.1/dist/css/shepherd.css">

    <!-- Кастомные стили для CRM -->
    <link href="{{ asset('css/crm.css') }}" rel="stylesheet">
    

  
</head>
<body class="border-top-wide border-primary d-flex flex-column">
    <!-- Header -->
    <header class="navbar navbar-expand-md navbar-light d-print-none">
        <div class="container-xl">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
                <a href="{{ url('/') }}" class="text-decoration-none">
                    <i class="fas fa-car me-2"></i>
                    <span class="fw-bold">Leas-Pro CRM</span>
                </a>
            </h1>
            
            <!-- Правая часть навигации -->
            <div class="navbar-nav flex-row order-md-last">
                @auth
                
                <!-- Пользователь -->
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown">
                        <span class="avatar avatar-sm" style="background-image: url(https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=1a56db&color=fff)"></span>
                        <div class="d-none d-xl-block ps-2">
                            <div>{{ auth()->user()->name }}</div>
                           
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        
                      
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
                    @php
                        $user = auth()->user();
                        $role = $user ? $user->role : null;
                    @endphp
                    
                    <!-- Главная - для всех -->
                      @if($role && in_array($role, ['admin', 'manager']))
                    <li class="nav-item {{ request()->routeIs('deals.index') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('deals.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="fas fa-home"></i>
                            </span>
                            <span class="nav-link-title">Сделки</span>
                        </a>
                    </li>
                          @endif
                          
                  
                    
                    <!-- Клиенты - только для админов и менеджеров -->
                    @if($role && in_array($role, ['admin', 'manager']))
                    <li class="nav-item {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('clients.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="fas fa-users"></i>
                            </span>
                            <span class="nav-link-title">Клиенты</span>
                           
                        </a>
                    </li>
                    @endif
                    
                    <!-- Автомобили - для всех, но с разными правами -->
                    @if($role && in_array($role, ['admin', 'manager']))
                    <li class="nav-item {{ request()->routeIs('cars.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('cars.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="fas fa-car"></i>
                            </span>
                            <span class="nav-link-title">Автомобили</span>
                          
                        </a>
                    </li>
                    @endif
                    
                    <!-- Администрирование - только для админов -->
                    @if($role === 'admin')
                    <li class="nav-item {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.users.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="fas fa-cog"></i>
                            </span>
                            <span class="nav-link-title">Администрирование</span>
                        </a>
                    </li>
                    @endif
                    
                    <!-- Инвестиции - только для инвесторов -->
                    @if($role === 'investor')
                    <li class="nav-item {{ request()->routeIs('investor.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('investor.dashboard') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="fas fa-chart-line"></i>
                            </span>
                            <span class="nav-link-title">Инвестиции</span>
                        </a>
                    </li>
                    @endif
                    
                    <!-- Быстрые действия - только для админов и менеджеров -->
                    @if($role && in_array($role, ['admin', 'manager']))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="fas fa-plus-circle"></i>
                            </span>
                            <span class="nav-link-title">Быстрые действия</span>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="{{ route('deals.create') }}">
                                <i class="fas fa-file-contract me-2"></i>Новая сделка
                            </a>
                            <a class="dropdown-item" href="{{ route('clients.create') }}">
                                <i class="fas fa-user-plus me-2"></i>Новый клиент
                            </a>
                            @if($role === 'admin')
                            <a class="dropdown-item" href="{{ route('cars.create') }}">
                                <i class="fas fa-car me-2"></i>Новый автомобиль
                            </a>
                            @endif
                        </div>
                    </li>
                    @endif
                </ul>
                
                <!-- Поиск - только для админов и менеджеров -->
                @if($role && in_array($role, ['admin', 'manager']))
                <div class="my-2 my-md-0 flex-grow-1 flex-md-grow-0 order-first order-md-last">
                    <form action="{{ route('deals.index') }}" method="GET">
                        <div class="input-icon">
                            <span class="input-icon-addon">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control" placeholder="Поиск..." name="search">
                        </div>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
    
    
    
    
    
    
    <!-- Основной контент -->
    <div class="page-wrapper">
        <div class="container-xl">
            <!-- Заголовок страницы -->
            @hasSection('title')
            <div class="page-header d-print-none">
                <div class="row align-items-center">
                    <div class="col">
                       
                        <div class="page-pretitle text-muted">
                            @yield('page-pretitle', '')
                        </div>
                    </div>
                    <div class="col-auto ms-auto d-print-none">
                        @yield('header-actions')
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Уведомления -->
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
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            
            <!-- Контент -->
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
                            <li class="list-inline-item">© {{ date('Y') }} Leas-Pro CRM</li>
                            <li class="list-inline-item">v1.0.0</li>
<li class="list-inline-item">
    <div class="dropdown">
        <button class="btn btn-outline-info btn-sm dropdown-toggle" type="button" 
                data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-question-circle me-1"></i> Обучение
        </button>
        <ul class="dropdown-menu">
            <li>
                <a class="dropdown-item" href="#" onclick="showTour()">
                    <i class="fas fa-play-circle me-2"></i> Запустить тур
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="#" onclick="resetPageTour()">
                    <i class="fas fa-redo me-2"></i> Повторить для этой страницы
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="#" onclick="resetAllTours()">
                    <i class="fas fa-sync-alt me-2"></i> Сбросить все туры
                </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item" href="#" onclick="showTourStatus()">
                    <i class="fas fa-info-circle me-2"></i> Статус туров
                </a>
            </li>
        </ul>
    </div>
</li>
                        </ul>
                    </div>
                    <div class="col-12 col-lg-auto mt-3 mt-lg-0">
               
                    </div>
                </div>
            </div>
        </footer>
    </div>
    
    <!-- Скрипты -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Tabler Core JS -->
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/js/tabler.min.js"></script>
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/ru.js"></script>
    
    <!-- Кастомные скрипты -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Инициализация Select2
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
            
            // Анимации
            $('.card').addClass('animate__animated animate__fadeIn');
            
            // Обновление счетчиков
            function updateCounters() {
                // Можно добавить AJAX запросы для обновления счетчиков
            }
            
            // Обновляем каждые 30 секунд
            setInterval(updateCounters, 30000);
        });
        
        // Функции для работы с CRM
        function confirmDelete(message) {
            return confirm(message || 'Вы уверены?');
        }
        
        function showLoading() {
            $('body').append('<div class="loading-overlay"><div class="spinner"></div></div>');
        }
        
        function hideLoading() {
            $('.loading-overlay').remove();
        }
    </script>
    
    

     <!-- Chart.js для графиков -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>





 <script src="https://unpkg.com/shepherd.js@8.3.1/dist/js/shepherd.min.js"></script>
<script>
    window.userRole = '{{ auth()->user()->role ?? "" }}';
</script>
<script src="{{ asset('js/manager-tour.js') }}"></script>
    
    
    
    
    
    @stack('scripts')
</body>
</html>