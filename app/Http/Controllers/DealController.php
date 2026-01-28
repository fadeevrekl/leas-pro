<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\DealPayment;
use App\Models\Client;
use App\Models\Car;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class DealController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        
         // ОТЛАДКА: логируем запрос поиска
    \Log::info('=== DEAL SEARCH DEBUG ===');
    \Log::info('Search term:', ['search' => $request->search]);
    \Log::info('All request params:', $request->all());
    
        $query = Deal::with(['client', 'car', 'manager']);
        
        // Фильтр по статусу
        if ($request->filled('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }
        
        // Фильтр по типу сделки
        if ($request->filled('deal_type') && $request->deal_type !== '') {
            $query->where('deal_type', $request->deal_type);
        }
        
        // Фильтр по периоду оплаты
        if ($request->filled('payment_period') && $request->payment_period !== '') {
            $query->where('payment_period', $request->payment_period);
        }
        
    // Фильтр по менеджеру
if (auth()->user()->isAdmin() && $request->filled('manager_id') && $request->manager_id !== '') {
    // Админ может фильтровать по любому менеджеру
    $query->where('manager_id', $request->manager_id);
} elseif (auth()->user()->isManager()) {
    // Менеджер видит только свои сделки (если не выбран фильтр менеджера)
    if (!$request->filled('manager_id') || $request->manager_id === '') {
        $query->where('manager_id', auth()->id());
    } elseif ($request->filled('manager_id') && $request->manager_id !== '') {
        // Менеджер не может фильтровать по другим менеджерам
        // Игнорируем фильтр и показываем только свои сделки
        $query->where('manager_id', auth()->id());
    }
}
        
        // Фильтр по инвестору (через автомобиль)
        if ($request->filled('investor_id') && $request->investor_id !== '') {
            $query->whereHas('car', function($q) use ($request) {
                $q->where('investor_id', $request->investor_id);
            });
        }
        
        // Фильтр по дате создания сделки
        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', $request->start_date . ' 00:00:00');
        }
        
        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }
        
     // ПОИСК - УЛУЧШЕННЫЙ ВАРИАНТ
    if ($request->filled('search')) {
        $searchTerm = $request->search;
        \Log::info('Searching for:', ['term' => $searchTerm]);
        
        // Тестируем поиск по отдельности
        $clientResults = Client::where('last_name', 'like', "%{$searchTerm}%")
            ->orWhere('first_name', 'like', "%{$searchTerm}%")
            ->get();
        \Log::info('Client search results count:', ['count' => $clientResults->count()]);
        
        $carResults = Car::where('brand', 'like', "%{$searchTerm}%")
            ->orWhere('model', 'like', "%{$searchTerm}%")
            ->get();
        \Log::info('Car search results count:', ['count' => $carResults->count()]);
        
        $query->where(function($q) use ($searchTerm) {
            $q->where('deal_number', 'like', "%{$searchTerm}%")
              ->orWhereHas('client', function($q) use ($searchTerm) {
                  $q->where('last_name', 'like', "%{$searchTerm}%")
                    ->orWhere('first_name', 'like', "%{$searchTerm}%")
                    ->orWhere('middle_name', 'like', "%{$searchTerm}%")
                    ->orWhere('phone', 'like', "%{$searchTerm}%");
              })
              ->orWhereHas('car', function($q) use ($searchTerm) {
                  $q->where('brand', 'like', "%{$searchTerm}%")
                    ->orWhere('model', 'like', "%{$searchTerm}%")
                    ->orWhere('license_plate', 'like', "%{$searchTerm}%")
                    ->orWhere('vin', 'like', "%{$searchTerm}%");
              })
              ->orWhereHas('manager', function($q) use ($searchTerm) {
                  $q->where('name', 'like', "%{$searchTerm}%");
              });
        });
        
        \Log::info('Final SQL query:', ['sql' => $query->toSql()]);
    }
        
        // Сортировка по умолчанию
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $query->orderBy($sortBy, $sortOrder);
        
        // Пагинация с сохранением параметров
        $perPage = $request->get('per_page', 20);
        
        try {
            $deals = $query->paginate($perPage);
            $deals->appends($request->except('page'));
        } catch (\Exception $e) {
            \Log::error('Error in deal search: ' . $e->getMessage());
            
            // Возвращаем пустой результат при ошибке
            $deals = collect([]);
        }
        
        // Получаем данные для фильтров
        $statuses = ['' => 'Все статусы'] + Deal::getStatuses();
        $dealTypes = ['' => 'Все типы'] + Deal::getDealTypes();
        $paymentPeriods = ['' => 'Все периоды'] + Deal::getPaymentPeriods();
        
// Получаем менеджеров для фильтра
if (auth()->user()->isAdmin()) {
    // Админ видит всех менеджеров
    $managers = User::where('role', User::ROLE_MANAGER)
                   ->where('is_active', true)
                   ->orderBy('name')
                   ->get();
} else {
    // Остальные роли не видят фильтр по менеджерам
    $managers = collect([]);}
        
        // Получаем инвесторов для фильтра
        $investors = User::where('role', User::ROLE_INVESTOR)
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->get();
        
        return view('deals.index', compact(
            'deals', 
            'statuses', 
            'dealTypes', 
            'paymentPeriods',
            'managers',
            'investors'
        ));
}

    public function create(Request $request)
    {
        // Получаем ID клиента из запроса, если он есть
        $clientId = $request->get('client_id');
        $client = null;
        
        if ($clientId) {
            $client = Client::find($clientId);
        }
        
        // Получаем ID автомобиля из запроса, если он есть
        $carId = $request->get('car_id');
        $car = null;
        
        if ($carId) {
            $car = Car::find($carId);
        }
        
        // Получаем клиентов для выпадающего списка
        $clients = Client::orderBy('last_name')->get();
        
        // Получаем автомобили (для менеджера только его автомобили)
        $carsQuery = Car::where('status', Car::STATUS_AVAILABLE);
        
        if (auth()->user()->isManager()) {
            $carsQuery->where('manager_id', auth()->id());
        }
        
        $cars = $carsQuery->orderBy('brand')->get();
        
        // Получаем менеджеров для выпадающего списка (только активных)
        $managers = User::where('role', User::ROLE_MANAGER)
                   ->where('is_active', true)
                   ->orderBy('name')
                   ->get();
        
        return view('deals.create', compact('clients', 'cars', 'car', 'managers', 'client'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Определяем manager_id в зависимости от роли пользователя
        $managerId = null;
        
        if (auth()->user()->isManager()) {
            // Менеджер может создавать только от своего имени
            $managerId = auth()->id();
        } elseif (auth()->user()->isAdmin() && $request->filled('manager_id')) {
            // Админ должен выбрать менеджера
            $managerId = $request->manager_id;
        } else {
            // Для администратора по умолчанию - сам админ, если не выбрал менеджера
            $managerId = auth()->id();
        }
        
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'car_id' => 'required|exists:cars,id',
            'deal_type' => 'required|in:rental,lease',
            'total_amount' => 'required|numeric|min:0|max:999999999.99',
            'initial_payment' => 'nullable|numeric|min:0|max:999999999.99',
            'payment_count' => 'required|integer|min:1|max:365',
            'payment_amount' => 'required|numeric|min:0|max:99999999.99',
            'start_date' => 'required|date',
            'payment_period' => 'required|in:day,week,month',
            'payment_day' => 'nullable|integer|min:1|max:31',
            'sms_notifications' => 'boolean',
            'notes' => 'nullable|string',
        ]);
        
        // Добавляем manager_id к валидированным данным
        $validated['manager_id'] = $managerId;
        
        // Проверяем автомобиль
        $car = Car::findOrFail($validated['car_id']);
        
        if (!$car->canCreateDeals()) {
            if ($car->hasDraftDeals()) {
                return back()
                    ->withInput()
                    ->with('error', 'Невозможно создать сделку. Автомобиль уже используется в черновой сделке.');
            } else {
                return back()
                    ->withInput()
                    ->with('error', 'Невозможно создать сделку. Автомобиль уже участвует в другой сделке.');
            }
        }
        
        // Дополнительная проверка для менеджера
        if (auth()->user()->isManager() && $car->manager_id != auth()->id()) {
            return back()
                ->withInput()
                ->with('error', 'Вы не можете создавать сделки с этим автомобилем. Автомобиль закреплен за другим менеджером.');
        }
        
        // Проверяем, можно ли создавать сделки с этим клиентом
        $client = Client::findOrFail($validated['client_id']);
        
        if (!$client->canCreateDeals()) {
            return back()
                ->withInput()
                ->with('error', 'Невозможно создать сделку. У клиента уже есть активная подписанная сделка.');
        }

        // Генерируем номер сделки
        $validated['deal_number'] = Deal::generateDealNumber();
        
        // По умолчанию статус черновика, пока не загружен договор
        $validated['status'] = Deal::STATUS_DRAFT;
        
        // Рассчитываем дату окончания
        $startDate = Carbon::parse($validated['start_date']);
        $validated['end_date'] = $this->calculateEndDate(
            $startDate, 
            $validated['payment_count'], 
            $validated['payment_period']
        );

        // Создаем сделку
        $deal = Deal::create($validated);
        
        // Обновляем статус клиента
        $client->updateStatusBasedOnDeals();
        
        // Создаем график платежей
        $this->createPaymentSchedule($deal);

        // Обновляем статус автомобиля
        $car->status = Car::STATUS_IN_DEAL;
        $car->deal_count += 1;
        $car->save();

        return redirect()->route('deals.show', $deal)
            ->with('success', 'Сделка успешно создана. Теперь можно сгенерировать договор.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Deal $deal)
    {
        $deal->load(['client', 'car', 'manager', 'payments', 'notifications']);
        return view('deals.show', compact('deal'));
    }

/**
 * Show the form for editing the specified resource.
 */
public function edit(Deal $deal)
{
    // Запрещаем редактирование завершенных сделок
    if ($deal->status === Deal::STATUS_COMPLETED) {
        return redirect()->route('deals.show', $deal)
            ->with('error', 'Невозможно редактировать завершенную сделку.');
    }
    
    $clients = Client::orderBy('last_name')->get();
    $cars = Car::whereIn('status', [Car::STATUS_AVAILABLE, Car::STATUS_IN_DEAL])->get();
    $managers = User::all();
    
    return view('deals.edit', compact('deal', 'clients', 'cars', 'managers'));
}

/**
 * Update the specified resource in storage.
 */
public function update(Request $request, Deal $deal)
{
    // Запрещаем обновление завершенных сделок
    if ($deal->status === Deal::STATUS_COMPLETED) {
        return redirect()->route('deals.show', $deal)
            ->with('error', 'Невозможно обновить завершенную сделку.');
    }
    
    $validated = $request->validate([
        'client_id' => 'required|exists:clients,id',
        'car_id' => 'required|exists:cars,id',
        'manager_id' => 'required|exists:users,id',
        'deal_type' => 'required|in:rental,lease',
        'total_amount' => 'required|numeric|min:0',
        'initial_payment' => 'nullable|numeric|min:0',
        'payment_count' => 'required|integer|min:1|max:365',
        'payment_amount' => 'required|numeric|min:0',
        'start_date' => 'required|date',
        'payment_period' => 'required|in:day,week,month',
        'payment_day' => 'nullable|integer|min:1|max:31',
        'sms_notifications' => 'boolean',
        'notes' => 'nullable|string',
    ]);

    // Проверяем, менялся ли клиент
    if ($deal->client_id != $validated['client_id']) {
        // Обновляем статус старого клиента
        $oldClient = Client::find($deal->client_id);
        $oldClient->updateStatusBasedOnDeals();
        
        // Обновляем статус нового клиента
        $newClient = Client::find($validated['client_id']);
        $newClient->updateStatusBasedOnDeals();
    }

    // Проверяем, менялся ли автомобиль
    if ($deal->car_id != $validated['car_id']) {
        // Возвращаем предыдущий автомобиль в доступные
        $oldCar = Car::find($deal->car_id);
        $oldCar->status = Car::STATUS_AVAILABLE;
        $oldCar->save();
        
        // Новый автомобиль помечаем как занятый
        $newCar = Car::find($validated['car_id']);
        $newCar->status = Car::STATUS_IN_DEAL;
        $newCar->save();
    }

    // Пересчитываем дату окончания
    $startDate = Carbon::parse($validated['start_date']);
    $validated['end_date'] = $this->calculateEndDate(
        $startDate, 
        $validated['payment_count'], 
        $validated['payment_period']
    );

    $deal->update($validated);
    
    // Если изменились параметры платежей, пересоздаем график
    if ($this->paymentParamsChanged($deal, $validated)) {
        $deal->payments()->delete();
        $this->createPaymentSchedule($deal);
    }
    
    // ВАЖНО: ОБНОВЛЯЕМ СТАТУС КЛИЕНТА
    $client = Client::find($validated['client_id']);
    $client->updateStatusBasedOnDeals();

    return redirect()->route('deals.show', $deal)
        ->with('success', 'Данные сделки обновлены.');
}

    /**
     * Сгенерировать договор для сделки
     */
    public function generateContract(Deal $deal)
    {
        // Проверяем, что сделка имеет все необходимые данные
        if (!$deal->client || !$deal->car) {
            return back()->with('error', 'Недостаточно данных для генерации договора. Проверьте клиента и автомобиль.');
        }
        
        // Загружаем связанные данные
        $deal->load(['client', 'car', 'payments']);
        
        try {
            // Генерируем PDF
            $pdf = Pdf::loadView('deals.contract_template', compact('deal'));
            
           // Важные настройки для русского языка и Times New Roman
            $dompdf = $pdf->getDomPDF();
            
            $options = $dompdf->getOptions();
            $options->set([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'isPhpEnabled' => false,
                'defaultFont' => 'times', // Используем 'times' как имя шрифта в dompdf
                'fontDir' => storage_path('fonts/'),
                'fontCache' => storage_path('fonts/'),
                'tempDir' => sys_get_temp_dir(),
                'chroot' => realpath(base_path()),
                'enableCssFloat' => true,
                'enableFontSubsetting' => true,
                'isFontSubsettingEnabled' => true,
                'defaultPaperSize' => 'A4',
                'defaultPaperOrientation' => 'portrait',
                'dpi' => 96,
            ]);
            
            // Сохраняем файл
            $filename = 'contract_' . $deal->deal_number . '_' . time() . '.pdf';
            $path = 'contracts/' . $filename;
            
            Storage::disk('public')->put($path, $pdf->output());
            
            // Обновляем сделку
            $deal->contract_path = $path;
            $deal->contract_generated_at = now();
            $deal->save();
            
            return back()->with('success', 'Договор успешно сгенерирован и сохранен.');
            
        } catch (\Exception $e) {
            \Log::error('Ошибка генерации договора: ' . $e->getMessage());
            return back()->with('error', 'Ошибка при генерации договора: ' . $e->getMessage());
        }
    }

    /**
     * Предпросмотр договора
     */
    public function previewContract(Deal $deal)
    {
        $deal->load(['client', 'car', 'payments']);
        return view('deals.contract_template', compact('deal'));
    }



    /**
     * Загрузка подписанного договора и активация сделки
     */
    public function uploadContract(Request $request, Deal $deal)
    {
        // Проверяем, что сделка в статусе черновика
        if ($deal->status !== 'draft') {
            return back()->with('error', 'Невозможно загрузить договор. Сделка уже активирована.');
        }
        
        $request->validate([
            'contract_file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'contract_signed_date' => 'required|date',
        ]);

        if ($request->hasFile('contract_file')) {
            // Удаляем старый файл если есть
            if ($deal->contract_path && Storage::disk('public')->exists($deal->contract_path)) {
                Storage::disk('public')->delete($deal->contract_path);
            }
            
            $path = $request->file('contract_file')->store('contracts', 'public');
            $deal->contract_path = $path;
        }

          $deal->contract_signed_date = $request->contract_signed_date;
    $deal->status = Deal::STATUS_ACTIVE;
    $deal->save();
    
    // Обновляем статус клиента
    $client = $deal->client;
    $client->updateStatusBasedOnDeals();
    
    // Обновляем статус автомобиля
    $car = $deal->car;
    $car->updateStatusBasedOnDeals();

        return back()->with('success', 'Договор успешно загружен. Сделка активирована.');
    }

    /**
     * Регистрация платежа с возможностью прикрепления документа
     */
public function registerPayment(Request $request, Deal $deal, DealPayment $payment)
{
    $request->validate([
        'payment_method' => 'required|in:cash,card,transfer,other',
        'transaction_id' => 'nullable|string|max:100',
        'notes' => 'nullable|string',
        'payment_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
    ]);

    // Обработка платежного документа
    $documentPath = null;
    if ($request->hasFile('payment_document')) {
        $documentPath = $request->file('payment_document')->store('payment_documents', 'public');
    }

    // Регистрируем платеж
    $payment->markAsPaid(
        $request->payment_method,
        $request->transaction_id,
        $request->notes,
        $documentPath
    );

    // Сбрасываем метку отправки SMS
    $deal->last_sms_sent_at = null;
    $deal->save();
    


    return back()->with('success', 'Платеж успешно зарегистрирован.');
}

    /**
     * Отправка SMS напоминания
     */
    public function sendReminder(Deal $deal, DealPayment $payment = null)
    {
        if (!$payment) {
            $payment = $deal->next_payment;
        }

        if (!$payment) {
            return back()->with('error', 'Нет предстоящих платежей для напоминания.');
        }

        try {
            // Здесь будет реальная отправка SMS через API
            // Пока имитируем успешную отправку
            
            $payment->sendReminder();
            
            $deal->last_sms_sent_at = now();
            $deal->sms_count += 1;
            $deal->save();

            return back()->with('success', 'Напоминание отправлено клиенту.');
        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка отправки SMS: ' . $e->getMessage());
        }
    }

    /**
     * Завершение сделки
     */
public function completeDeal(Request $request, Deal $deal)
{
    // Проверка с погрешностью
    $totalPaid = round($deal->total_paid, 2);
    $totalAmount = round($deal->total_amount, 2);
    $isFullyPaid = abs($totalAmount - $totalPaid) <= 0.05;
    
    if (!$isFullyPaid) {
        $difference = $totalAmount - $totalPaid;
        return back()->with('error', 'Не все платежи выполнены. Осталось оплатить: ' . 
            number_format($difference, 2, ',', ' ') . ' ₽');
    }

    // Сохраняем ID перед изменением
    $clientId = $deal->client_id;
    $carId = $deal->car_id;
    
    // Обновляем статус сделки
    $deal->status = Deal::STATUS_COMPLETED;
    $deal->save();
    
    \Log::info("Сделка {$deal->id} завершена. Тип: {$deal->deal_type}. Обновляем клиента {$clientId} и автомобиль {$carId}");
    
    // ОБНОВЛЯЕМ КЛИЕНТА
    $client = Client::find($clientId);
    if ($client) {
        \Log::info("До обновления - статус клиента: " . $client->status);
        $client->updateStatusBasedOnDeals();
        \Log::info("После обновления - статус клиента: " . $client->status);
    } else {
        \Log::error("Клиент {$clientId} не найден!");
    }
    
    // ОБНОВЛЯЕМ АВТОМОБИЛЬ с учетом типа сделки
    $car = Car::find($carId);
    if ($car) {
        \Log::info("До обновления - статус автомобиля: " . $car->status);
        \Log::info("Тип завершенной сделки: " . $deal->deal_type . " (" . $deal->deal_type_text . ")");
        
        // ВАЖНО: Прямая установка статуса для лизинга с выкупом
        if ($deal->deal_type === Deal::TYPE_LEASE) {
            \Log::info("Лизинг с выкупом завершен. Автомобиль {$carId} помечается как ПРОДАН");
            $car->status = Car::STATUS_SOLD;
            $car->save();
        } else {
            // Для аренды - обычное обновление
            $car->updateStatusBasedOnDeals();
        }
        
        \Log::info("После обновления - статус автомобиля: " . $car->status);
    } else {
        \Log::error("Автомобиль {$carId} не найден!");
    }

    return back()->with('success', 'Сделка успешно завершена.');
}




/**
 * Remove the specified resource from storage.
 */
public function destroy(Request $request, Deal $deal)
{
    \Log::info('=== НАЧАЛО УДАЛЕНИЯ СДЕЛКИ ===', [
        'deal_id' => $deal->id,
        'deal_number' => $deal->deal_number,
        'user_id' => auth()->id(),
        'user_role' => auth()->user()->role,
        'confirmation_text' => $request->confirmation_text ?? 'не указано'
    ]);
    
    // Только для админа
    if (!auth()->user()->isAdmin()) {
        \Log::warning('Попытка удаления сделки не админом', [
            'deal_id' => $deal->id,
            'user_id' => auth()->id()
        ]);
        return redirect()->route('deals.show', $deal)
            ->with('error', 'У вас недостаточно прав для удаления сделки.');
    }
    
    // Проверка подтверждения
    if (!$request->has('confirmation_text') || $request->confirmation_text !== 'УДАЛИТЬ') {
        \Log::warning('Нет подтверждения для удаления сделки', [
            'deal_id' => $deal->id,
            'has_confirmation' => $request->has('confirmation_text'),
            'confirmation_value' => $request->confirmation_text ?? 'не указано'
        ]);
        return back()
            ->with('error', 'Для удаления сделки необходимо подтверждение.')
            ->with('show_delete_modal', true);
    }
    
    // Проверяем статус
    if ($deal->status === Deal::STATUS_ACTIVE || $deal->status === Deal::STATUS_OVERDUE) {
        \Log::warning('Попытка удаления активной сделки', [
            'deal_id' => $deal->id,
            'status' => $deal->status
        ]);
        return back()
            ->with('error', 'Невозможно удалить активную или просроченную сделку. Сначала завершите или отмените сделку.')
            ->with('show_delete_modal', true);
    }
    
    try {
        \Log::info('Начинаем удаление сделки', [
            'deal_id' => $deal->id,
            'status' => $deal->status,
            'client_id' => $deal->client_id,
            'car_id' => $deal->car_id
        ]);
        
        // 1. Сохраняем данные для обновления клиента
        $clientId = $deal->client_id;
        $carId = $deal->car_id;
        
        // 2. Возвращаем автомобиль в доступные
        $car = Car::find($carId);
        if ($car) {
            $car->status = Car::STATUS_AVAILABLE;
            $car->save();
            \Log::info('Автомобиль возвращен в доступные', ['car_id' => $carId]);
        }
        
        // 3. Безопасно удаляем связанные платежи (если модель существует)
        try {
            if (class_exists('App\Models\DealPayment') && method_exists($deal, 'payments')) {
                $paymentCount = $deal->payments()->count();
                $deal->payments()->delete();
                \Log::info('Удалены связанные платежи', ['count' => $paymentCount]);
            }
        } catch (\Exception $e) {
            \Log::warning('Не удалось удалить платежи', [
                'error' => $e->getMessage(),
                'deal_id' => $deal->id
            ]);
            // Продолжаем удаление сделки даже если платежи не удалились
        }
        
        // 4. Удаляем сделку
        $deal->delete();
        \Log::info('Сделка удалена из БД', ['deal_id' => $deal->id]);
        
        // 5. Обновляем статус клиента
        $client = Client::find($clientId);
        if ($client) {
            $client->updateStatusBasedOnDeals();
            \Log::info('Статус клиента обновлен', ['client_id' => $clientId]);
        }
        
        \Log::info('=== УДАЛЕНИЕ СДЕЛКИ УСПЕШНО ЗАВЕРШЕНО ===');
        
        return redirect()->route('deals.index')
            ->with('success', 'Сделка удалена.');
            
    } catch (\Exception $e) {
        \Log::error('КРИТИЧЕСКАЯ ОШИБКА при удалении сделки', [
            'deal_id' => $deal->id,
            'error_message' => $e->getMessage(),
            'error_trace' => $e->getTraceAsString()
        ]);
        
        return back()
            ->with('error', 'Произошла критическая ошибка при удалении сделки: ' . $e->getMessage())
            ->with('show_delete_modal', true);
    }
}





    /**
     * Отмена/архивация сделки
     */
public function cancelDeal(Request $request, Deal $deal)
{
    $request->validate([
        'cancellation_reason' => 'required|string|max:500',
    ]);

    $deal->status = Deal::STATUS_CANCELLED;
    $deal->notes = ($deal->notes ? $deal->notes . "\n\n" : '') . 
                  "Отменено: " . $request->cancellation_reason;
    $deal->save();
    
    // ОБНОВЛЯЕМ СТАТУС КЛИЕНТА
    $client = $deal->client;
    $client->updateStatusBasedOnDeals();
    
    // ОБНОВЛЯЕМ СТАТУС АВТОМОБИЛЯ
    $car = $deal->car;
    $car->updateStatusBasedOnDeals();

    return back()->with('success', 'Сделка отменена.');
}
    
    /**
     * Скачивание договора сделки
     */
    public function downloadContract(Deal $deal)
    {
        // Проверяем, что договор существует
        if (!$deal->contract_path) {
            return back()->with('error', 'Договор не загружен');
        }
        
        // Проверяем, что файл существует в хранилище
        if (!Storage::disk('public')->exists($deal->contract_path)) {
            \Log::error("Файл договора не найден: {$deal->contract_path}");
            return back()->with('error', 'Файл договора не найден в хранилище');
        }
        
        try {
            // Определяем расширение файла
            $extension = pathinfo($deal->contract_path, PATHINFO_EXTENSION);
            
            // Создаем имя файла для скачивания
            $filename = 'Договор_' . $deal->deal_number . '.' . $extension;
            
            // Логируем для отладки
            \Log::info("Скачивание договора: {$deal->contract_path} как {$filename}");
            
            // Возвращаем файл для скачивания
            return Storage::disk('public')->download($deal->contract_path, $filename);
            
        } catch (\Exception $e) {
            \Log::error('Ошибка при скачивании договора: ' . $e->getMessage());
            return back()->with('error', 'Ошибка при скачивании договора: ' . $e->getMessage());
        }
    }

    /**
     * Просмотр договора в браузере
     */
    public function viewContract(Deal $deal)
    {
        if (!$deal->contract_path) {
            abort(404, 'Договор не загружен');
        }
        
        if (!Storage::disk('public')->exists($deal->contract_path)) {
            abort(404, 'Файл договора не найден');
        }
        
        // Определяем MIME-тип
        $path = Storage::disk('public')->path($deal->contract_path);
        $mime = mime_content_type($path);
        
        return response()->file($path, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($deal->contract_path) . '"'
        ]);
    }

    /**
     * Рассчитать дату окончания сделки
     */
    private function calculateEndDate(Carbon $startDate, int $paymentCount, string $period): Carbon
    {
        switch ($period) {
            case 'day':
                return $startDate->copy()->addDays($paymentCount);
            case 'week':
                return $startDate->copy()->addWeeks($paymentCount);
            case 'month':
                return $startDate->copy()->addMonths($paymentCount);
            default:
                return $startDate->copy()->addMonths($paymentCount);
        }
    }

    /**
     * Создать график платежей
     */
private function createPaymentSchedule(Deal $deal): void
{
    $payments = [];
    $currentDate = Carbon::parse($deal->start_date);

    // Если есть первоначальный взнос - создаем его как ОЖИДАЮЩИЙ платеж
    if ($deal->initial_payment > 0) {
        $payments[] = [
            'deal_id' => $deal->id,
            'payment_number' => 0, // 0 = первоначальный взнос
            'due_date' => $deal->start_date,
            'amount' => $deal->initial_payment,
            'status' => DealPayment::STATUS_PENDING, // ВАЖНО: не paid, а pending!
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    // Рассчитываем сумму оставшихся платежей
    $remainingAmount = $deal->total_amount - $deal->initial_payment;
    
    // Если есть остаток после первоначального взноса
    if ($remainingAmount > 0 && $deal->payment_count > 0) {
        // Базовый платеж
        $baseAmount = $remainingAmount / $deal->payment_count;
        
        // Корректировка из-за округления
        $adjustedAmounts = [];
        $totalAdjusted = 0;
        
        for ($i = 1; $i <= $deal->payment_count; $i++) {
            if ($i == $deal->payment_count) {
                // Последний платеж = остаток
                $amount = $remainingAmount - $totalAdjusted;
            } else {
                $amount = round($baseAmount, 2);
            }
            
            $adjustedAmounts[$i] = round($amount, 2);
            $totalAdjusted += $amount;
        }

        // Создаем регулярные платежи
        for ($i = 1; $i <= $deal->payment_count; $i++) {
            // Определяем дату платежа
            switch ($deal->payment_period) {
                case 'day':
                    $dueDate = $currentDate->copy()->addDays($i);
                    break;
                case 'week':
                    $dueDate = $currentDate->copy()->addWeeks($i);
                    break;
                case 'month':
                    $dueDate = $currentDate->copy()->addMonths($i);
                    if ($deal->payment_day) {
                        $dueDate->day = min($deal->payment_day, $dueDate->daysInMonth);
                    }
                    break;
            }

            $payments[] = [
                'deal_id' => $deal->id,
                'payment_number' => $i,
                'due_date' => $dueDate->format('Y-m-d'),
                'amount' => $adjustedAmounts[$i],
                'status' => DealPayment::STATUS_PENDING,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
    }

    // Вставляем все платежи
    DealPayment::insert($payments);
}

    /**
     * Проверить, изменились ли параметры платежей
     */
    private function paymentParamsChanged(Deal $deal, array $newData): bool
    {
        return $deal->payment_count != $newData['payment_count'] ||
               $deal->payment_amount != $newData['payment_amount'] ||
               $deal->start_date != $newData['start_date'] ||
               $deal->payment_period != $newData['payment_period'] ||
               $deal->payment_day != $newData['payment_day'] ||
               $deal->initial_payment != $newData['initial_payment'];
    }
    
    /**
     * Получить детали платежа (AJAX)
     */
    public function paymentDetails(Deal $deal, DealPayment $payment)
    {
        // Проверяем, что платеж принадлежит сделке
        if ($payment->deal_id != $deal->id) {
            return response()->json(['error' => 'Платеж не принадлежит этой сделке'], 403);
        }
        
        return response()->json([
            'id' => $payment->id,
            'payment_number' => $payment->payment_number,
            'amount' => (float) $payment->amount,
            'paid_at' => $payment->paid_at ? $payment->paid_at->toISOString() : null,
            'payment_method' => $payment->payment_method,
            'payment_method_text' => $payment->payment_method_text,
            'transaction_id' => $payment->transaction_id,
            'notes' => $payment->notes,
            'payment_document_path' => $payment->payment_document_path,
        ]);
    }
    
    /**
     * Сгенерировать шаблон договора для скачивания
     */
    public function generateContractTemplate(Deal $deal)
    {
        // Проверяем, что сделка имеет все необходимые данные
        if (!$deal->client || !$deal->car) {
            return back()->with('error', 'Недостаточно данных для генерации договора. Проверьте клиента и автомобиль.');
        }
        
        // Проверяем статус - только для черновиков
        if ($deal->status !== 'draft') {
            return back()->with('error', 'Невозможно сгенерировать шаблон. Сделка уже активирована.');
        }
        
        // Загружаем связанные данные
        $deal->load(['client', 'car', 'payments']);
        
        try {
            // Генерируем PDF
            $pdf = Pdf::loadView('deals.contract_template', compact('deal'));
            
           // КРИТИЧНО ВАЖНЫЕ НАСТРОЙКИ ДЛЯ РУССКИХ ШРИФТОВ
            $pdf->setPaper('A4', 'portrait');
            
            $dompdf = $pdf->getDomPDF();
            
            // Установим опции напрямую
            $options = $dompdf->getOptions();
            $options->set([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'isPhpEnabled' => false,
               'defaultFont' => 'Times-Roman',
                'fontDir' => storage_path('fonts/'),
                'fontCache' => storage_path('fonts/'),
                'tempDir' => sys_get_temp_dir(),
                'chroot' => realpath(base_path()),
                'enableCssFloat' => true,
                'enableFontSubsetting' => true,
                'isFontSubsettingEnabled' => true,
            ]);
            
            // Генерируем имя файла
            $filename = 'Шаблон_договора_' . $deal->deal_number . '_' . now()->format('d-m-Y') . '.pdf';
            
            // Сохраняем как временный файл (не в БД!)
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            \Log::error('Ошибка генерации шаблона договора: ' . $e->getMessage());
            return back()->with('error', 'Ошибка при генерации шаблона договора: ' . $e->getMessage());
        }
    }

    /**
     * Предпросмотр шаблона договора
     */
    public function previewContractTemplate(Deal $deal)
    {
        // Проверяем статус
        if ($deal->status !== 'draft') {
            abort(403, 'Доступ только для сделок в статусе черновик');
        }
        
        $deal->load(['client', 'car', 'payments']);
        return view('deals.contract_template', compact('deal'));
    }
    
    public function uploadDocument(Request $request, Deal $deal)
    {
        $validated = $request->validate([
            'type' => 'required|in:contract,additional_agreement,act,payment_document,other',
            'name' => 'required|string|max:255',
            'document_number' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'issue_date' => 'nullable|date',
            'document_file' => 'required|file|max:10240', // 10MB
        ]);

        if ($request->hasFile('document_file')) {
            $path = $request->file('document_file')->store('deal_documents', 'public');
            $validated['file_path'] = $path;
        }

        $validated['deal_id'] = $deal->id;
        $validated['uploaded_by'] = auth()->id();

        DealDocument::create($validated);

        // Если загружается договор - обновляем путь к договору в сделке
        if ($validated['type'] === 'contract') {
            $deal->contract_path = $validated['file_path'];
            if (!$deal->contract_signed_date && $validated['issue_date']) {
                $deal->contract_signed_date = $validated['issue_date'];
            }
            $deal->save();
        }

        return back()->with('success', 'Документ успешно загружен.');
    }

    /**
     * Удаление документа
     */
    public function deleteDocument(DealDocument $document)
    {
        // Проверяем что документ принадлежит сделке текущего пользователя
        if (auth()->user()->id !== $document->uploaded_by && !auth()->user()->is_admin) {
            return back()->with('error', 'Недостаточно прав для удаления документа.');
        }

        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return back()->with('success', 'Документ удален.');
    }

    /**
     * Скачивание документа
     */
    public function downloadDocument(DealDocument $document)
    {
        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'Файл не найден');
        }

        return Storage::disk('public')->download($document->file_path);
    }
    
    
    
    
    
    /**
 * Скачать график платежей в PDF
 */
public function downloadPaymentSchedule(Deal $deal)
{
    try {
        // Загружаем связанные данные
        $deal->load(['client', 'car', 'payments']);
        
        // Настройки PDF для русского языка и Times New Roman
        $pdf = Pdf::loadView('deals.payment_schedule_pdf', compact('deal'));
        
        // Критически важные настройки для русского языка и Times New Roman
        $dompdf = $pdf->getDomPDF();
        $options = $dompdf->getOptions();
        
        // Добавляем шрифт Times New Roman
        $fontDirectory = storage_path('fonts/');
        
        $options->set([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'isPhpEnabled' => false,
            'defaultFont' => 'Times-Roman',
            'fontDir' => $fontDirectory,
            'fontCache' => $fontDirectory,
            'tempDir' => sys_get_temp_dir(),
            'chroot' => realpath(base_path()),
            'enableCssFloat' => true,
            'enableFontSubsetting' => true,
            'isFontSubsettingEnabled' => true,
            'defaultPaperSize' => 'A4',
            'defaultPaperOrientation' => 'portrait',
            'dpi' => 96,
        ]);
        
        // Генерируем имя файла
        $filename = 'График_платежей_' . $deal->deal_number . '_' . date('d-m-Y') . '.pdf';
        
        return $pdf->download($filename);
        
    } catch (\Exception $e) {
        \Log::error('Ошибка генерации графика платежей: ' . $e->getMessage());
        return back()->with('error', 'Ошибка при генерации графика платежей: ' . $e->getMessage());
    }
}
    
    
    /**
 * Предпросмотр графика платежей
 */
public function previewPaymentSchedule(Deal $deal)
{
    $deal->load(['client', 'car', 'payments']);
    return view('deals.payment_schedule_pdf', compact('deal'));
}
    
    
    
}
