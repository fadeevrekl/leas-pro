<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InvestorController;

/*
|--------------------------------------------------------------------------
| Маршруты аутентификации (публичные)
|--------------------------------------------------------------------------
*/









// Форма входа
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');

// Обработка входа
Route::post('/login', [AuthController::class, 'login']);

// Выход
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Защищенные маршруты (только для авторизованных пользователей)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | Главная страница
    |--------------------------------------------------------------------------
    */
    
    // Управление пользователями (только для админов)
    Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
        // Пользователи (менеджеры и инвесторы)
        Route::resource('users', UserController::class);
    });
    
    Route::get('/', function () {
        // Получаем текущего пользователя
        $user = auth()->user();
        
        // В зависимости от роли показываем разную информацию
        switch ($user->role) {
            case 'admin':
                // Для администратора - список сделок
                return redirect()->route('deals.index');
                
            case 'manager':
                // Для менеджера - список сделок
                return redirect()->route('deals.index');
                
            case 'investor':
                // Для инвестора - специальная страница
                return redirect()->route('investor.dashboard');
                
            default:
                // По умолчанию - список сделок
                return redirect()->route('deals.index');
        }
    });
    
    /*
    |--------------------------------------------------------------------------
    | Клиенты
    |--------------------------------------------------------------------------
    */
    
    Route::resource('clients', ClientController::class);
    
    // Документы клиентов
    Route::prefix('clients/{client}')->group(function () {
        Route::post('/documents', [ClientController::class, 'uploadDocument'])->name('clients.documents.store');
        Route::delete('/documents/{document}', [ClientController::class, 'deleteDocument'])->name('clients.documents.destroy');
    });
    
    
    // История сделок клиента (AJAX)
Route::get('/clients/{client}/deal-history', [ClientController::class, 'getDealHistory'])
    ->name('clients.deal-history')
    ->middleware('auth');
    
    
    
/*
|--------------------------------------------------------------------------
| Автомобили
|--------------------------------------------------------------------------
*/

// Маршруты только для админов
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/cars/create', [CarController::class, 'create'])->name('cars.create');
    Route::post('/cars', [CarController::class, 'store'])->name('cars.store');
    Route::get('/cars/{car}/edit', [CarController::class, 'edit'])->name('cars.edit');
    Route::put('/cars/{car}', [CarController::class, 'update'])->name('cars.update');
    Route::delete('/cars/{car}', [CarController::class, 'destroy'])->name('cars.destroy');
});

// Маршруты для просмотра (доступны всем авторизованным)
Route::middleware('auth')->group(function () {
    Route::get('/cars', [CarController::class, 'index'])->name('cars.index');
    Route::get('/cars/{car}', [CarController::class, 'show'])->name('cars.show');
    
    // Документы и расходы (только менеджеры и админы)
    Route::middleware(['auth', 'role:admin,manager'])->group(function () {
        Route::post('/cars/{car}/documents', [CarController::class, 'addDocument'])->name('cars.documents.store');
       Route::delete('/cars/{car}/documents/{carDocument}', [CarController::class, 'deleteDocument'])->name('cars.documents.destroy');
        
        Route::post('/cars/{car}/expenses', [CarController::class, 'addExpense'])->name('cars.expenses.store');
        Route::delete('/cars/{car}/expenses/{expense}', [CarController::class, 'deleteExpense'])->name('cars.expenses.destroy');
    });
});
    
    // Дополнительные маршруты для автомобилей
    Route::prefix('cars/{car}')->group(function () {
        // Документы автомобиля
        Route::post('/documents', [CarController::class, 'addDocument'])->name('cars.documents.store');
       // Route::delete('/documents/{document}', [CarController::class, 'deleteDocument'])->name('cars.documents.destroy');
        
        // Расходы на автомобиль
        Route::post('/expenses', [CarController::class, 'addExpense'])->name('cars.expenses.store');
        Route::delete('/expenses/{expense}', [CarController::class, 'deleteExpense'])->name('cars.expenses.destroy');
    });
    /*
|--------------------------------------------------------------------------
| ДОКУМЕНТЫ АВТОМОБИЛЕЙ - ДОПОЛНИТЕЛЬНЫЕ МАРШРУТЫ
|--------------------------------------------------------------------------
*/

// Просмотр документа автомобиля (в браузере)
Route::get('/car-documents/{filename}/view', function ($filename) {
    try {
        $path = storage_path('app/public/car_documents/' . $filename);
        
        if (!file_exists($path)) {
            abort(404, 'Документ автомобиля не найден');
        }
        
        $mime = mime_content_type($path);
        
        return response()->file($path, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Ошибка при показе документа автомобиля: ' . $e->getMessage());
        abort(500, 'Ошибка при загрузке документа');
    }
})->name('car.documents.view')->middleware('auth');

// Скачивание документа автомобиля
Route::get('/car-documents/{filename}/download', function ($filename) {
    try {
        $path = storage_path('app/public/car_documents/' . $filename);
        
        if (!file_exists($path)) {
            abort(404, 'Документ автомобиля не найден');
        }
        
        return response()->download($path, $filename);
        
    } catch (\Exception $e) {
        \Log::error('Ошибка при скачивании документа автомобиля: ' . $e->getMessage());
        abort(500, 'Ошибка при скачивании документа');
    }
})->name('car.documents.download')->middleware('auth');
    
    
    /*
|--------------------------------------------------------------------------
| ДОКУМЕНТЫ РАСХОДОВ АВТОМОБИЛЕЙ
|--------------------------------------------------------------------------
*/

// Скачивание документа расхода
Route::get('/car-expenses/{filename}/download', function ($filename) {
    try {
        $path = storage_path('app/public/car_expenses/' . $filename);
        
        if (!file_exists($path)) {
            abort(404, 'Документ расхода не найден');
        }
        
        return response()->download($path, $filename);
        
    } catch (\Exception $e) {
        \Log::error('Ошибка при скачивании документа расхода: ' . $e->getMessage());
        abort(500, 'Ошибка при скачивании документа');
    }
})->name('car.expenses.document.download')->middleware('auth');
    
    /*
|--------------------------------------------------------------------------
| ДОКУМЕНТЫ РАСХОДОВ АВТОМОБИЛЕЙ
|--------------------------------------------------------------------------
*/

// Просмотр документа расхода (в браузере)
Route::get('/car-expenses/{filename}/view', function ($filename) {
    try {
        $path = storage_path('app/public/car_expenses/' . $filename);
        
        if (!file_exists($path)) {
            abort(404, 'Документ расхода не найден');
        }
        
        $mime = mime_content_type($path);
        
        return response()->file($path, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Ошибка при показе документа расхода: ' . $e->getMessage());
        abort(500, 'Ошибка при загрузке документа');
    }
})->name('car.expenses.document.view')->middleware('auth');

// Скачивание документа расхода
Route::get('/car-expenses/{filename}/download', function ($filename) {
    try {
        $path = storage_path('app/public/car_expenses/' . $filename);
        
        if (!file_exists($path)) {
            abort(404, 'Документ расхода не найден');
        }
        
        return response()->download($path, $filename);
        
    } catch (\Exception $e) {
        \Log::error('Ошибка при скачивании документа расхода: ' . $e->getMessage());
        abort(500, 'Ошибка при скачивании документа');
    }
})->name('car.expenses.document.download')->middleware('auth');
    
    
    /*
    |--------------------------------------------------------------------------
    | Сделки
    |--------------------------------------------------------------------------
    */
    
    Route::resource('deals', DealController::class);
    
    // Дополнительные маршруты для сделок
    Route::prefix('deals/{deal}')->group(function () {
        
        // Шаблон договора
    Route::get('/contract/template', [DealController::class, 'generateContractTemplate'])
         ->name('deals.contract.template.generate');
    
    Route::get('/contract/template/preview', [DealController::class, 'previewContractTemplate'])
         ->name('deals.contract.template.preview');
    
    // Загрузка подписанного договора (уже есть)
    Route::post('/upload-contract', [DealController::class, 'uploadContract'])
         ->name('deals.upload-contract');
    
    // Просмотр и скачивание подписанного договора (уже есть)
    Route::get('/download-contract', [DealController::class, 'downloadContract'])
         ->name('deals.contract.download');
    
    Route::get('/view-contract', [DealController::class, 'viewContract'])
         ->name('deals.contract.view');
        
        // Загрузка договора
        Route::post('/upload-contract', [DealController::class, 'uploadContract'])->name('deals.upload-contract');
        
        // Регистрация платежа
        Route::post('/payments/{payment}/register', [DealController::class, 'registerPayment'])->name('deals.payments.register');
        
        // Отправка напоминания
        Route::post('/send-reminder', [DealController::class, 'sendReminder'])->name('deals.send-reminder');
        
        // Завершение сделки
        Route::post('/complete', [DealController::class, 'completeDeal'])->name('deals.complete');
    });
    
    // Документы сделок
    Route::prefix('deals/{deal}/documents')->group(function () {
        Route::post('/', [DealController::class, 'uploadDocument'])->name('deals.documents.store');
        Route::delete('/{document}', [DealController::class, 'deleteDocument'])->name('deals.documents.destroy');
        Route::get('/{document}/download', [DealController::class, 'downloadDocument'])->name('deals.documents.download');
    });
    
    // Кабинет инвестора
   Route::middleware(['auth', 'role:investor'])->prefix('investor')->name('investor.')->group(function () {
    Route::get('/dashboard', [InvestorController::class, 'dashboard'])->name('dashboard');
    Route::get('/investments', [InvestorController::class, 'investments'])->name('investments');
    Route::get('/investments-advanced', [InvestorController::class, 'investmentsAdvanced'])->name('investments.advanced');
    Route::get('/cars/{car}', [InvestorController::class, 'showCar'])->name('cars.show');
});
    
    /*
    |--------------------------------------------------------------------------
    | ДОКУМЕНТЫ КЛИЕНТОВ (ЗАМЫКАНИЯ) - ОСНОВНЫЕ МАРШРУТЫ
    |--------------------------------------------------------------------------
    */
    
    // Просмотр документа клиента (в браузере)
    Route::get('/client-documents/{filename}', function ($filename) {
        try {
            $path = storage_path('app/public/client_documents/' . $filename);
            
            if (!file_exists($path)) {
                \Log::warning('Файл не найден: ' . $filename);
                abort(404, 'Документ не найден');
            }
            
            $mime = mime_content_type($path);
            
            return response()->file($path, [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Ошибка при показе документа: ' . $e->getMessage());
            abort(500, 'Ошибка при загрузке документа');
        }
    })->name('client.documents.show')->middleware('auth');

    // Скачивание документа клиента
    Route::get('/client-documents/download/{filename}', function ($filename) {
        try {
            $path = storage_path('app/public/client_documents/' . $filename);
            
            if (!file_exists($path)) {
                abort(404, 'Документ не найден');
            }
            
            return response()->download($path, $filename);
            
        } catch (\Exception $e) {
            \Log::error('Ошибка при скачивании документа: ' . $e->getMessage());
            abort(500, 'Ошибка при скачивании документа');
        }
    })->name('client.documents.download')->middleware('auth');
    
    /*
    |--------------------------------------------------------------------------
    | ДОКУМЕНТЫ АВТОМОБИЛЕЙ
    |--------------------------------------------------------------------------
    */
    
    // Просмотр документа автомобиля
    Route::get('/car-documents/{filename}', function ($filename) {
        try {
            $path = storage_path('app/public/car_documents/' . $filename);
            
            if (!file_exists($path)) {
                abort(404, 'Документ автомобиля не найден');
            }
            
            $mime = mime_content_type($path);
            
            return response()->file($path, [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Ошибка при показе документа автомобиля: ' . $e->getMessage());
            abort(500, 'Ошибка при загрузке документа');
        }
    })->name('car.documents.show')->middleware('auth');
});

/*
|--------------------------------------------------------------------------
| СТРАНИЦА ПРОВЕРКИ СТРУКТУРЫ БАЗЫ
|--------------------------------------------------------------------------
*/

Route::get('/check-tables-structure', function () {
    $tables = [
        'users',
        'clients',
        'client_documents',
        'cars',
        'car_documents',
        'car_expenses',
        'deals'
    ];
    $structure = [];
    foreach ($tables as $tableName) {
        try {
            if (\Schema::hasTable($tableName)) {
                $columns = \DB::select("DESCRIBE `$tableName`");
                $structure[$tableName] = $columns;
            }
        } catch (\Exception $e) {
            // Пропускаем таблицу, если ошибка
        }
    }
    return view('check-tables', compact('structure'));
})->middleware('auth');





Route::get('/update-client-statuses', function() {
    $clients = \App\Models\Client::all();
    $updated = 0;
    
    foreach ($clients as $client) {
        $oldStatus = $client->status;
        $client->updateStatusBasedOnDeals();
        
        if ($oldStatus !== $client->status) {
            $updated++;
            echo "Клиент {$client->id}: {$oldStatus} -> {$client->status}<br>";
        }
    }
    
    echo "<br>Обновлено: {$updated} клиентов";
});
// Скачивание договора сделки
Route::get('/deals/{deal}/download-contract', [DealController::class, 'downloadContract'])
     ->name('deals.contract.download');
     
     Route::get('/deals/{deal}/view-contract', [DealController::class, 'viewContract'])
     ->name('deals.contract.view');
     
// Получение деталей платежа (AJAX) - используем метод контроллера
Route::get('/deals/{deal}/payments/{payment}/details', [DealController::class, 'paymentDetails'])
     ->name('deals.payments.details')->middleware('auth');

// Маршруты для платежных документов
Route::get('/payment-documents/{filename}/view', function ($filename) {
    try {
        $path = storage_path('app/public/payment_documents/' . $filename);
        
        if (!file_exists($path)) {
            abort(404, 'Платежный документ не найден');
        }
        
        $mime = mime_content_type($path);
        
        return response()->file($path, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Ошибка при показе платежного документа: ' . $e->getMessage());
        abort(500, 'Ошибка при загрузке документа');
    }
})->name('payment.documents.view')->middleware('auth');

Route::get('/payment-documents/{filename}/download', function ($filename) {
    try {
        $path = storage_path('app/public/payment_documents/' . $filename);
        
        if (!file_exists($path)) {
            abort(404, 'Платежный документ не найден');
        }
        
        return response()->download($path, $filename);
        
    } catch (\Exception $e) {
        \Log::error('Ошибка при скачивании платежного документа: ' . $e->getMessage());
        abort(500, 'Ошибка при скачивании документа');
    }
})->name('payment.documents.download')->middleware('auth');

// Тестовый маршрут для удаления
Route::get('/test-delete-deal/{deal}', function (App\Models\Deal $deal) {
    \Log::info('Тестовый маршрут для удаления сделки', ['deal_id' => $deal->id]);
    return response()->json([
        'success' => true,
        'message' => 'Тестовый маршрут работает',
        'deal_id' => $deal->id,
        'deal_number' => $deal->deal_number
    ]);
})->middleware('auth');

// В группе маршрутов deals добавьте:
Route::get('/deals/{deal}/download-payment-schedule', [DealController::class, 'downloadPaymentSchedule'])
    ->name('deals.payment-schedule.download')->middleware('auth');
    
    Route::get('/deals/{deal}/preview-payment-schedule', [DealController::class, 'previewPaymentSchedule'])
    ->name('deals.payment-schedule.preview')->middleware('auth');
    





// Клиенты - только для админов и менеджеров
Route::middleware(['auth', 'admin.manager'])->group(function () {
    Route::resource('clients', ClientController::class);
    
    Route::prefix('clients/{client}')->group(function () {
        Route::post('/documents', [ClientController::class, 'uploadDocument'])->name('clients.documents.store');
        Route::delete('/documents/{document}', [ClientController::class, 'deleteDocument'])->name('clients.documents.destroy');
    });
    
    Route::get('/clients/{client}/deal-history', [ClientController::class, 'getDealHistory'])
        ->name('clients.deal-history');
});

// Сделки - только для админов и менеджеров
Route::middleware(['auth', 'admin.manager'])->group(function () {
    Route::resource('deals', DealController::class);
    
    Route::prefix('deals/{deal}')->group(function () {
        Route::get('/contract/template', [DealController::class, 'generateContractTemplate'])
             ->name('deals.contract.template.generate');
        // ... остальные маршруты сделок
    });
});

// Автомобили - разные права доступа
Route::middleware('auth')->group(function () {
    // Просмотр для всех
    Route::get('/cars', [CarController::class, 'index'])->name('cars.index');
    Route::get('/cars/{car}', [CarController::class, 'show'])->name('cars.show');
    
    // Редактирование - только для админов и менеджеров
    Route::middleware('admin.manager')->group(function () {
        Route::get('/cars/create', [CarController::class, 'create'])->name('cars.create');
        Route::post('/cars', [CarController::class, 'store'])->name('cars.store');
        Route::get('/cars/{car}/edit', [CarController::class, 'edit'])->name('cars.edit');
        Route::put('/cars/{car}', [CarController::class, 'update'])->name('cars.update');
        Route::delete('/cars/{car}', [CarController::class, 'destroy'])->name('cars.destroy');
    });
});