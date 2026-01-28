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
    public function index()
    {
        $cars = Car::with('manager')
                  ->orderBy('brand')
                  ->orderBy('model')
                  ->paginate(20);
        
        return view('cars.index', compact('cars'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
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
        $validated = $request->validate([
        'brand' => 'required|string|max:100',
        'model' => 'required|string|max:100',
        'vin' => 'required|string|size:17|unique:cars,vin',
        'color' => 'required|string|max:50',
        'license_plate' => 'required|string|max:20|unique:cars,license_plate',
        'mileage' => 'required|integer|min:0',
        'fuel_type' => 'required|string',
        'investor_id' => 'required|exists:users,id', // ИЗМЕНИЛ: было 'investor'
        'manager_id' => 'nullable|exists:users,id',
        'price' => 'required|numeric|min:0',
        'gps_tracker_id' => 'nullable|string|max:100',
        'status' => 'required|in:available,in_deal,maintenance,sold',
        'notes' => 'nullable|string',
        ]);

        Car::create($validated);

        return redirect()->route('cars.index')
            ->with('success', 'Автомобиль успешно добавлен.');
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
        $validated = $request->validate([
        'brand' => 'required|string|max:100',
        'model' => 'required|string|max:100',
        'vin' => 'required|string|size:17|unique:cars,vin,' . $car->id,
        'color' => 'required|string|max:50',
        'license_plate' => 'required|string|max:20|unique:cars,license_plate,' . $car->id,
        'mileage' => 'required|integer|min:0',
        'fuel_type' => 'required|string',
        'investor_id' => 'required|exists:users,id', // ОСТАВЛЯЕМ ТОЛЬКО ЭТО
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
    public function destroy(Car $car)
    {
        $car->delete();

        return redirect()->route('cars.index')
            ->with('success', 'Автомобиль удален.');
    }

    /**
     * Добавление документа к автомобилю
     */
    public function addDocument(Request $request, Car $car)
    {
        $validated = $request->validate([
            'type' => 'required|in:pts,sts,osago,kasko,additional_insurance,autoteka,service_docs,other',
            'document_number' => 'nullable|string|max:100',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'document_file' => 'nullable|file|max:10240', // 10MB
            'notes' => 'nullable|string',
        ]);

        if ($request->hasFile('document_file')) {
            $path = $request->file('document_file')->store('car_documents', 'public');
            $validated['file_path'] = $path;
        }

        $car->documents()->create($validated);

        return back()->with('success', 'Документ добавлен.');
    }

    /**
     * Добавление расхода к автомобилю
     */
    public function addExpense(Request $request, Car $car)
    {
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





public function deleteDocument(Request $request, $carId, $documentId)
{
    // Проверка прав доступа
    if (!auth()->user()->isAdmin() && !auth()->user()->isManager()) {
        abort(403, 'У вас нет прав для удаления документов');
    }
    
    // Находим автомобиль вручную
    $car = Car::findOrFail($carId);
    
    // Находим документ
    $document = $car->documents()->findOrFail($documentId);
    
    // Удаляем файл
    if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
        Storage::disk('public')->delete($document->file_path);
    }
    
    // Удаляем запись
    $document->delete();
    
    return back()->with('success', 'Документ удален.');
}

    /**
     * Удаление расхода
     */
    public function deleteExpense(CarExpense $expense)
    {
        if ($expense->document_path && Storage::disk('public')->exists($expense->document_path)) {
            Storage::disk('public')->delete($expense->document_path);
        }
        
        $expense->delete();
        
        return back()->with('success', 'Расход удален.');
    }
}