<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Лизинг @yield('title')</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Chart.js Adapter for date -->
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
<!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />


    <style>
        body { 
            background-color: #f5f7fb; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar-brand, .nav-link {
            color: white !important;
            font-weight: 500;
        }
        .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
            border-radius: 4px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .card:hover {
            
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }
        .card-header {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            font-weight: 600;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
        }
        .form-control, .form-select {
            border-radius: 6px;
            border: 1px solid #e0e0e0;
            padding: 10px 15px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(102, 126, 234, 0.05);
        }
        .action-buttons .btn {
            margin-right: 5px;
            padding: 4px 12px;
            font-size: 0.875rem;
        }
        .bg-light {
            background-color: #f8f9fa !important;
        }
    </style>
</head>
<body class="overflow-y-scroll">
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ url('/') }}">
                <i class="fas fa-car-fill me-2"></i>CRM Лизинг
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @auth
<!-- Для инвесторов -->
@if(auth()->user()->role === 'investor')
<li class="nav-item">
    <a class="nav-link {{ request()->is('investor/dashboard') ? 'active' : '' }}" href="{{ route('investor.dashboard') }}">
        <i class="fas fa-car me-1"></i>Мои автомобили
    </a>
</li>
<li class="nav-item">
    <a class="nav-link {{ request()->is('investor/investments') ? 'active' : '' }}" href="{{ route('investor.investments') }}">
        <i class="fas fa-chart-line me-1"></i>Мои инвестиции
    </a>
</li>

                        
                        <!-- Для администраторов и менеджеров -->
                        @elseif(in_array(auth()->user()->role, ['admin', 'manager']))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{ url('/') }}">
                                    <i class="fas fa-home-door me-1"></i>Главная
                                </a>
                            </li>
                            
                            <!-- Для администраторов -->
                            @if(auth()->user()->role === 'admin')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin*') ? 'active' : '' }}" href="{{ url('/admin/users') }}">
                                    <i class="fas fa-users-fill me-1"></i>Пользователи
                                </a>
                            </li>
                            @endif
                            
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('clients*') ? 'active' : '' }}" href="{{ route('clients.index') }}">
                                    <i class="fas fa-users me-1"></i>Клиенты
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('deals*') ? 'active' : '' }}" href="{{ route('deals.index') }}">
                                    <i class="fas fa-file-contract me-1"></i>Сделки
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('cars*') ? 'active' : '' }}" href="{{ route('cars.index') }}">
                                    <i class="fas fa-car me-1"></i>Автомобили
                                </a>
                            </li>
                        @endif
                        
                        <!-- Выпадающее меню пользователя -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" 
                               data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle me-1"></i>{{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li>
                                    <span class="dropdown-item-text small text-muted">
                                        <i class="bi bi-shield me-1"></i>Роль: 
                                        @if(Auth::user()->role === 'admin')
                                            <span class="badge bg-danger">Администратор</span>
                                        @elseif(Auth::user()->role === 'manager')
                                            <span class="badge bg-primary">Менеджер</span>
                                        @elseif(Auth::user()->role === 'investor')
                                            <span class="badge bg-success">Инвестор</span>
                                            <br>
                                            <small>Комиссия: {{ Auth::user()->commission_percent }}%</small>
                                        @endif
                                    </span>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                
                                <!-- Дополнительные ссылки для админа в выпадающем меню -->
                                @if(Auth::user()->role === 'admin')
                                <li>
                                    <a class="dropdown-item" href="{{ url('/admin/users') }}">
                                        <i class="fas fa-users-fill me-2"></i>Управление пользователями
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                @endif
                                
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt me-2"></i>Выйти
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle-fill me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <main>
            @yield('content')
        </main>
    </div>

    <!-- Bootstrap 5 JS with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Функция для создания маски телефона
        function createPhoneMask(inputId) {
            const input = document.getElementById(inputId);
            if (!input) return;
            
            input.addEventListener('input', function(e) {
                // Удаляем все нецифры
                let value = this.value.replace(/\D/g, '');
                
                // Убираем ведущие 7 или 8
                if (value.startsWith('7') || value.startsWith('8')) {
                    value = value.substring(1);
                }
                
                // Форматируем
                let formatted = '+7';
                if (value.length > 0) {
                    formatted += ' (' + value.substring(0, 3);
                }
                if (value.length > 3) {
                    formatted += ') ' + value.substring(3, 6);
                }
                if (value.length > 6) {
                    formatted += '-' + value.substring(6, 8);
                }
                if (value.length > 8) {
                    formatted += '-' + value.substring(8, 10);
                }
                
                this.value = formatted;
                
                // Позиция курсора
                const cursorPosition = this.selectionStart;
                if (cursorPosition < 4) {
                    this.setSelectionRange(4, 4);
                }
            });
            
            // При фокусе ставим курсор после +7 (
            input.addEventListener('focus', function() {
                if (this.value === '') {
                    this.value = '+7 (';
                    this.setSelectionRange(4, 4);
                }
            });
        }
        
        // Функция для маски кода подразделения
        function createDivisionCodeMask(inputId) {
            const input = document.getElementById(inputId);
            if (!input) return;
            
            input.addEventListener('input', function(e) {
                // Удаляем все нецифры
                let value = this.value.replace(/\D/g, '');
                
                // Форматируем XXX-XXX
                let formatted = '';
                if (value.length > 0) {
                    formatted = value.substring(0, 3);
                }
                if (value.length > 3) {
                    formatted += '-' + value.substring(3, 6);
                }
                
                this.value = formatted;
            });
        }
        
        // Функция для маски серии паспорта (4 цифры)
        function createPassportSeriesMask(inputId) {
            const input = document.getElementById(inputId);
            if (!input) return;
            
            input.addEventListener('input', function(e) {
                // Удаляем все нецифры и ограничиваем 4 символами
                let value = this.value.replace(/\D/g, '').substring(0, 4);
                this.value = value;
            });
        }
        
        // Функция для маски номера паспорта (6 цифр)
        function createPassportNumberMask(inputId) {
            const input = document.getElementById(inputId);
            if (!input) return;
            
            input.addEventListener('input', function(e) {
                // Удаляем все нецифры и ограничиваем 6 символами
                let value = this.value.replace(/\D/g, '').substring(0, 6);
                this.value = value;
            });
        }
        
        // Функция для чекбокса "Совпадает с местом регистрации"
        function setupAddressCheckbox() {
            const checkbox = document.getElementById('same_as_registration');
            const regAddress = document.getElementById('registration_address');
            const resAddress = document.getElementById('residential_address');
            
            if (!checkbox || !regAddress || !resAddress) return;
            
            // При изменении чекбокса
            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    // Копируем адрес регистрации в адрес проживания
                    resAddress.value = regAddress.value;
                    resAddress.readOnly = true;
                    resAddress.classList.add('bg-light');
                } else {
                    resAddress.readOnly = false;
                    resAddress.classList.remove('bg-light');
                }
            });
            
            // При изменении адреса регистрации (если чекбокс активен)
            regAddress.addEventListener('input', function() {
                if (checkbox.checked) {
                    resAddress.value = this.value;
                }
            });
            
            // При загрузке страницы проверяем, совпадают ли адреса
            if (regAddress.value === resAddress.value && regAddress.value.trim() !== '') {
                checkbox.checked = true;
                resAddress.readOnly = true;
                resAddress.classList.add('bg-light');
            }
        }
        
        // Инициализация при загрузке страницы
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Инициализация CRM Лизинг...');
            
            // Настраиваем маски
            createPhoneMask('phone');
            createPhoneMask('additional_phone');
            createDivisionCodeMask('passport_division_code');
            createPassportSeriesMask('passport_series');
            createPassportNumberMask('passport_number');
            
            // Настраиваем чекбокс адресов
            setupAddressCheckbox();
            
            // Назначаем плейсхолдеры
            const phoneInputs = document.querySelectorAll('input[type="tel"]');
            phoneInputs.forEach(input => {
                if (input.value === '') {
                    input.placeholder = '+7 (___) ___-__-__';
                }
            });
            
            const divisionCodeInput = document.getElementById('passport_division_code');
            if (divisionCodeInput && divisionCodeInput.value === '') {
                divisionCodeInput.placeholder = '123-456';
            }
            
            const passportSeriesInput = document.getElementById('passport_series');
            if (passportSeriesInput && passportSeriesInput.value === '') {
                passportSeriesInput.placeholder = '1234';
            }
            
            const passportNumberInput = document.getElementById('passport_number');
            if (passportNumberInput && passportNumberInput.value === '') {
                passportNumberInput.placeholder = '123456';
            }
            
            console.log('Инициализация завершена');
        });
        
        // Дополнительная функция для форматирования существующих значений
        function formatExistingValues() {
            // Форматируем телефон если он в неправильном формате
            const phoneInput = document.getElementById('phone');
            if (phoneInput && phoneInput.value && !phoneInput.value.startsWith('+7')) {
                let phone = phoneInput.value.replace(/\D/g, '');
                if (phone.length >= 10) {
                    if (phone.startsWith('7') || phone.startsWith('8')) {
                        phone = phone.substring(1);
                    }
                    phone = '+7 (' + phone.substring(0, 3) + ') ' + phone.substring(3, 6) + '-' + phone.substring(6, 8) + '-' + phone.substring(8, 10);
                    phoneInput.value = phone;
                }
            }
            
            // Форматируем код подразделения
            const divisionInput = document.getElementById('passport_division_code');
            if (divisionInput && divisionInput.value) {
                let code = divisionInput.value.replace(/\D/g, '');
                if (code.length === 6) {
                    divisionInput.value = code.substring(0, 3) + '-' + code.substring(3, 6);
                }
            }
        }
        
        // Вызываем форматирование при загрузке
        window.addEventListener('load', formatExistingValues);
    </script>
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/ru.js"></script>
    
    
    @stack('scripts')
</body>
</html>