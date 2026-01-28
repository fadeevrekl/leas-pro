{{-- resources/views/layouts/app-simple.blade.php --}}
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CRM')</title>
    
    <!-- Только Tabler CSS -->
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css" rel="stylesheet">
    
    <style>
        body { padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Простая навигация -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
            <div class="container-fluid">
                <a class="navbar-brand" href="/">CRM</a>
                <div class="navbar-nav">
                    <a class="nav-link" href="{{ route('deals.index') }}">Сделки</a>
                    <a class="nav-link" href="{{ route('clients.index') }}">Клиенты</a>
                    <a class="nav-link" href="{{ route('cars.index') }}">Автомобили</a>
                </div>
            </div>
        </nav>
        
        <!-- Контент -->
        <div class="content">
            @yield('content')
        </div>
    </div>
    
    <!-- Tabler JS -->
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/js/tabler.min.js"></script>
</body>
</html>