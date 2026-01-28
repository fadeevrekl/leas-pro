<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\CarDocument;
use App\Models\CarExpense;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
// app/Http/Controllers/CarController.php

public function index(Request $request)
{
    // Начинаем запрос с загрузкой связей
    $query = Car::with(['manager', 'investor']);

    // ФИЛЬТРАЦИЯ ПО РОЛИ:
    // Если пользователь - менеджер, показываем только его автомобили
    if (auth()->user()->isManager()) {
        $query->where('manager_id', auth()->id());
    }
    // Если пользователь - инвестор, показываем только его автомобили
    elseif (auth()->user()->isInvestor()) {
        $query->where('investor_id', auth()->id());
    }
    // Админ видит все автомобили (без фильтра)

    // ФИЛЬТР ПО СТАТУСУ
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // ФИЛЬТР ПО МЕНЕДЖЕРУ (только для админа)
    if (auth()->user()->isAdmin() && $request->filled('manager_id')) {
        $query->where('manager_id', $request->manager_id);
    } elseif (auth()->user()->isManager()) {
        // Для менеджера всегда фильтр по себе
        $query->where('manager_id', auth()->id());
    }

    // ФИЛЬТР ПО ИНВЕСТОРУ
    if ($request->filled('investor_id')) {
        $query->where('investor_id', $request->investor_id);
    }

    // ФИЛЬТР ПО ТИПУ ТОПЛИВА
    if ($request->filled('fuel_type')) {
        $query->where('fuel_type', $request->fuel_type);
    }

    // ФИЛЬТР ПО ПОИСКУ
    if ($request->filled('search')) {
        $searchTerm = $request->search;
        $query->where(function ($q) use ($searchTerm) {
            $q->where('brand', 'like', "%{$searchTerm}%")
              ->orWhere('model', 'like', "%{$searchTerm}%")
              ->orWhere('license_plate', 'like', "%{$searchTerm}%")
              ->orWhere('vin', 'like', "%{$searchTerm}%")
              ->orWhere('color', 'like', "%{$searchTerm}%");
        });
    }

    // ФИЛЬТР ПО ДАТЕ СОЗДАНИЯ
    if ($request->filled('start_date')) {
        $query->whereDate('created_at', '>=', $request->start_date);
    }
    
    if ($request->filled('end_date')) {
        $query->whereDate('created_at', '<=', $request->end_date);
    }

    // ФИЛЬТР ПО ГОДУ ВЫПУСКА
    if ($request->filled('year_from')) {
        $query->where('year', '>=', $request->year_from);
    }
    
    if ($request->filled('year_to')) {
        $query->where('year', '<=', $request->year_to);
    }

    // ФИЛЬТР ПО ЦЕНЕ
    if ($request->filled('price_from')) {
        $query->where('price', '>=', $request->price_from);
    }
    
    if ($request->filled('price_to')) {
        $query->where('price', '<=', $request->price_to);
    }

    // Сортировка
    $sortBy = $request->get('sort_by', 'created_at');
    $sortOrder = $request->get('sort_order', 'desc');
    
    if (in_array($sortBy, ['id', 'brand', 'model', 'year', 'price', 'created_at'])) {
        $query->orderBy($sortBy, $sortOrder);
    } else {
        $query->orderBy('created_at', 'desc');
    }

    // Пагинация
    $perPage = $request->get('per_page', 20);
    $cars = $query->paginate($perPage)->appends($request->except('page'));
    
    // Получаем данные для фильтров
    $managers = auth()->user()->isAdmin() 
        ? User::where('role', User::ROLE_MANAGER)->where('is_active', true)->get()
        : collect();
    
    $investors = User::where('role', User::ROLE_INVESTOR)->where('is_active', true)->get();
    $statuses = Car::getStatuses();
    $fuelTypes = Car::getFuelTypes();

    return view('cars.index', compact('cars', 'managers', 'investors', 'statuses', 'fuelTypes'));
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Дополнительная проверка
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Доступ запрещен. Только администратор может создавать автомобили.');
        }
        
        $managers = User::where('role', User::ROLE_MANAGER)
                       ->where('is_active', true)
                       ->get();
        
        $investors = User::where('role', User::ROLE_INVESTOR)
                        ->where('is_active', true)
                        ->get();
        
        return view('cars.create', compact('managers', 'investors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Дополнительная проверка
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Доступ запрещен. Только администратор может создавать автомобили.');
        }

        $validated = $request->validate([
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year' => 'required|integer|min:1990|max:' . (date('Y') + 1),
            'vin' => 'required|string|size:17|unique:cars,vin',
            'color' => 'required|string|max:50',
            'license_plate' => 'required|string|max:20|unique:cars,license_plate',
            'mileage' => 'required|integer|min:0',
            'fuel_type' => 'required|string',
            'investor_id' => 'required|exists:users,id',
            'manager_id' => 'nullable|exists:users,id',
            'price' => 'required|numeric|min:0',
            'gps_tracker_id' => 'nullable|string|max:100',
            'status' => 'required|in:available,in_deal,maintenance,sold',
            'notes' => 'nullable|string',
            'documents' => 'nullable|array',
            'documents.*.file' => 'nullable|file|max:10240', // 10MB
            'documents.*.number' => 'nullable|string|max:100',
            'documents.*.issue_date' => 'nullable|date',
            'documents.*.expiry_date' => 'nullable|date',
            'documents.*.notes' => 'nullable|string',
        ]);

        // Создаем автомобиль
        $car = Car::create($validated);

        // Обрабатываем документы, если они есть
        if ($request->has('documents') && is_array($request->documents)) {
            foreach ($request->documents as $type => $documentData) {
                // Проверяем, есть ли файл для этого типа документа
                if (isset($documentData['file']) && $documentData['file'] instanceof \Illuminate\Http\UploadedFile) {
                    $document = [
                        'type' => $type,
                        'document_number' => $documentData['number'] ?? null,
                        'issue_date' => $documentData['issue_date'] ?? null,
                        'expiry_date' => $documentData['expiry_date'] ?? null,
                        'notes' => $documentData['notes'] ?? null,
                    ];

                    // Загружаем файл
                    $path = $documentData['file']->store('car_documents', 'public');
                    $document['file_path'] = $path;

                    // Создаем запись документа
                    $car->documents()->create($document);
                }
            }
        }

        return redirect()->route('cars.index')
            ->with('success', 'Автомобиль успешно добавлен' . ($request->has('documents') ? ' с документами' : '') . '.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Car $car)
    {
        $car->load(['documents', 'expenses', 'manager']);
        return view('cars.show', compact('car'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Car $car)
    {
        // Дополнительная проверка
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Доступ запрещен. Только администратор может редактировать автомобили.');
        }
        
        // Проверка статуса
    if (!$car->canBeEdited()) {
        abort(403, 'Невозможно редактировать автомобиль с текущим статусом: ' . $car->status_text);
    }
        
        $managers = User::where('role', User::ROLE_MANAGER)
                       ->where('is_active', true)
                       ->get();
        
        $investors = User::where('role', User::ROLE_INVESTOR)
                        ->where('is_active', true)
                        ->get();
        
        return view('cars.edit', compact('car', 'managers', 'investors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Car $car)
    {
        // Дополнительная проверка
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Доступ запрещен. Только администратор может редактировать автомобили.');
        }
        
         // Проверка статуса
    if (!$car->canBeEdited()) {
        abort(403, 'Невозможно редактировать автомобиль с текущим статусом: ' . $car->status_text);
    }
        
        $validated = $request->validate([
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year' => 'required|integer|min:1990|max:' . (date('Y') + 1), 
            'vin' => 'required|string|size:17|unique:cars,vin,' . $car->id,
            'color' => 'required|string|max:50',
            'license_plate' => 'required|string|max:20|unique:cars,license_plate,' . $car->id,
            'mileage' => 'required|integer|min:0',
            'fuel_type' => 'required|string',
            'investor_id' => 'required|exists:users,id',
            'manager_id' => 'nullable|exists:users,id',
            'price' => 'required|numeric|min:0',
            'gps_tracker_id' => 'nullable|string|max:100',
            'status' => 'required|in:available,in_deal,maintenance,sold',
            'notes' => 'nullable|string',
        ]);

        $car->update($validated);

        return redirect()->route('cars.index')
            ->with('success', 'Данные автомобиля обновлены.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Car $car)
    {
        // Дополнительная проверка
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Доступ запрещен. Только администратор может удалять автомобили.');
        }
        
         // Проверка статуса
    if (!$car->canBeDeleted()) {
        abort(403, 'Невозможно удалить автомобиль с текущим статусом: ' . $car->status_text);
    }
        
        // Проверка подтверждения
        if ($request->confirmation_text !== 'УДАЛИТЬ') {
            return back()
                ->with('error', 'Для удаления автомобиля необходимо подтверждение.')
                ->with('show_delete_modal', true); // Флаг для показа модального окна
        }
        
        // Проверка, есть ли активные сделки
        if ($car->deals()->whereIn('status', ['active', 'pending'])->exists()) {
            return back()
                ->with('error', 'Невозможно удалить автомобиль, так как у него есть активные или ожидающие сделки.')
                ->with('show_delete_modal', true);
        }
        
        // Удаление документов автомобиля
        foreach ($car->documents as $document) {
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            $document->delete();
        }
        
        // Удаление расходов автомобиля
        foreach ($car->expenses as $expense) {
            if ($expense->document_path && Storage::disk('public')->exists($expense->document_path)) {
                Storage::disk('public')->delete($expense->document_path);
            }
            $expense->delete();
        }
        
        // Удаление автомобиля
        $car->delete();
        
        return redirect()->route('cars.index')
            ->with('success', 'Автомобиль и все связанные данные успешно удалены.');
    }

    /**
     * Добавление документа к автомобилю
     */
public function addDocument(Request $request, Car $car)
{
    // Проверка статуса
    if (!$car->canAddDocuments()) {
        abort(403, 'Невозможно добавлять документы к автомобилю с текущим статусом: ' . $car->status_text);
    }
    
    $validated = $request->validate([
        'type' => 'required|in:pts,sts,osago,kasko,additional_insurance,autoteka,service_docs,other',
        'document_number' => 'nullable|string|max:100',
        'issue_date' => 'nullable|date',
        'expiry_date' => 'nullable|date',
        'document_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // ОБЯЗАТЕЛЬНО и с проверкой MIME
        'notes' => 'nullable|string',
    ], [
        'document_file.required' => 'Файл документа обязателен для заполнения',
        'document_file.mimes' => 'Разрешены только файлы: PDF, JPG, JPEG, PNG',
        'document_file.max' => 'Максимальный размер файла: 10MB',
    ]);

    // Загружаем файл
    $path = $request->file('document_file')->store('car_documents', 'public');
    $validated['file_path'] = $path;

    $car->documents()->create($validated);

    return back()->with('success', 'Документ добавлен.');
}

    /**
     * Добавление расхода к автомобилю
     */
    public function addExpense(Request $request, Car $car)
    {
        
         // Проверка статуса
    if (!$car->canAddExpenses()) {
        abort(403, 'Невозможно добавлять расходы к автомобилю с текущим статусом: ' . $car->status_text);
    }
        
        
        $validated = $request->validate([
            'expense_type' => 'required|in:maintenance,repair,wash,fuel,insurance,tax,other',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'expense_file' => 'nullable|file|max:10240', // 10MB
            'description' => 'nullable|string',
        ]);

        if ($request->hasFile('expense_file')) {
            $path = $request->file('expense_file')->store('car_expenses', 'public');
            $validated['document_path'] = $path;
        }

        $car->expenses()->create($validated);

        return back()->with('success', 'Расход добавлен.');
    }

/**
 * Удаление документа
 */
public function deleteDocument(Request $request, $car, $carDocument)
{
    \Log::info('Параметры запроса', [
        'car_param' => $car,
        'document_param' => $carDocument,
        'all_params' => $request->all()
    ]);
    
    // Находим документ по ID
    $document = CarDocument::find($carDocument);
    
    if (!$document) {
        \Log::error('Документ не найден', ['id' => $carDocument]);
        abort(404, 'Документ не найден');
    }
    
    \Log::info('Найден документ', [
        'document_id' => $document->id,
        'car_id' => $document->car_id,
        'file_path' => $document->file_path
    ]);
    
    // Проверка прав доступа
    if (!auth()->user()->isAdmin() && !auth()->user()->isManager()) {
        abort(403, 'Недостаточно прав для удаления документа');
    }
    
    // Проверяем, что документ принадлежит указанному автомобилю
    if ($document->car_id != $car) {
        \Log::error('Документ не принадлежит автомобилю', [
            'document_car_id' => $document->car_id,
            'requested_car_id' => $car
        ]);
        abort(403, 'Документ не принадлежит указанному автомобилю');
    }
    
    // Удаление файла
    if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
        \Log::info('Удаление файла', ['path' => $document->file_path]);
        Storage::disk('public')->delete($document->file_path);
    } else {
        \Log::warning('Файл не найден или путь пустой', ['path' => $document->file_path]);
    }
    
    // Удаление записи
    $deleted = $document->delete();
    \Log::info('Результат удаления', ['deleted' => $deleted, 'document_id' => $document->id]);
    
    return back()->with('success', 'Документ удален.');
}

    /**
     * Удаление расхода
     */
    public function deleteExpense(Request $request, Car $car, $expenseId)
    {
        // Проверка прав доступа (опционально)
        if (!auth()->user()->isAdmin() && !auth()->user()->isManager()) {
            abort(403, 'Недостаточно прав');
        }
        
        // Находим расход по ID
        $expense = CarExpense::findOrFail($expenseId);
        
        // Проверяем, что расход принадлежит этому автомобилю
        if ($expense->car_id != $car->id) {
            abort(403, 'Расход не принадлежит этому автомобилю');
        }
        
        // Удаляем файл, если он есть
        if ($expense->document_path && Storage::disk('public')->exists($expense->document_path)) {
            Storage::disk('public')->delete($expense->document_path);
        }
        
        // Удаляем запись из БД
        $expense->delete();
        
        return back()->with('success', 'Расход удален.');
    }
    
    
    
    
/**
 * Получить историю сделок автомобиля (AJAX)
 */
public function getDealHistory(Car $car)
{
    // Загружаем сделки с клиентами и менеджерами
    $car->load(['deals.client', 'deals.manager']);
    
    return view('cars.partials.deal-history-table', [
        'deals' => $car->deals()->with(['client', 'manager'])->get()
    ]);
}

/**
 * Страница с историей сделок (полная)
 */
public function dealHistory(Car $car)
{
    $car->load(['deals.client', 'deals.manager', 'deals.payments']);
    
    return view('cars.deal-history', compact('car'));
}
    


    
}