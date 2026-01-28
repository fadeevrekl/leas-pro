<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Получаем запрос
        $query = Client::query();
        
        // Фильтр по статусу
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        // Поиск по фамилии
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('last_name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%");
            });
        }
        
        // Сортировка по фамилии
        $query->orderBy('last_name');
        
        // Пагинация
        $clients = $query->paginate(20)->withQueryString();
        
      // Получаем все статусы для фильтра
$statuses = [
    'all' => 'Все статусы',
    Client::STATUS_ACTIVE => 'Свободен',
    Client::STATUS_DRAFT => 'Черновик',
    Client::STATUS_IN_DEAL => 'В сделке',
    Client::STATUS_ARCHIVED => 'Архивный',
];
        
        return view('clients.index', compact('clients', 'statuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('clients.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'last_name' => 'required|string|max:100',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'passport_series' => 'required|string|size:4',
            'passport_number' => 'required|string|size:6',
            'passport_issued_by' => 'required|string|max:255',
            'passport_issued_date' => 'required|date',
            'passport_division_code' => 'required|string|max:7',
            'registration_address' => 'required|string',
            'residential_address' => 'required|string',
            'drivers_license' => 'nullable|string|max:50',
            'phone' => 'required|string|max:20',
            'additional_phone' => 'nullable|string|max:20',
            'guarantor' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            
            // Документы (обязательные только паспорта)
            'passport_main' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'passport_registration' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'drivers_license_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'additional_documents.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        // Создаем клиента (без документов в основном массиве)
        $clientData = [
            'last_name' => $validated['last_name'],
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'],
            'passport_series' => $validated['passport_series'],
            'passport_number' => $validated['passport_number'],
            'passport_issued_by' => $validated['passport_issued_by'],
            'passport_issued_date' => $validated['passport_issued_date'],
            'passport_division_code' => $validated['passport_division_code'],
            'registration_address' => $validated['registration_address'],
            'residential_address' => $validated['residential_address'],
            'drivers_license' => $validated['drivers_license'],
            'phone' => $validated['phone'],
            'additional_phone' => $validated['additional_phone'],
            'guarantor' => $validated['guarantor'],
            'notes' => $validated['notes'],
            'status' => Client::STATUS_ACTIVE, // Используем константу
        ];

        $client = Client::create($clientData);

        // Сохраняем документы
        $this->saveClientDocuments($client, $request);
        \Log::info('Клиент создан с ID: ' . $client->id);

        return redirect()->route('clients.index')
            ->with('success', 'Клиент успешно создан.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        $client->load('documents');
        return view('clients.show', compact('client'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'last_name' => 'required|string|max:100',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'passport_series' => 'required|string|size:4',
            'passport_number' => 'required|string|size:6',
            'passport_issued_by' => 'required|string|max:255',
            'passport_issued_date' => 'required|date',
            'passport_division_code' => 'required|string|size:7',
            'registration_address' => 'required|string',
            'residential_address' => 'required|string',
            'drivers_license' => 'nullable|string|max:50',
            'phone' => 'required|string|max:20',
            'additional_phone' => 'nullable|string|max:20',
            'guarantor' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $client->update($validated);

        return redirect()->route('clients.index')
            ->with('success', 'Данные клиента обновлены.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Client $client)
    {
        // Проверка подтверждения
        if ($request->confirmation_text !== 'УДАЛИТЬ') {
            return back()
                ->with('error', 'Для удаления клиента необходимо подтверждение.')
                ->with('show_delete_modal', true);
        }
        
        // Проверка, есть ли активные сделки
        if ($client->hasActiveDeals()) {
            return back()
                ->with('error', 'Невозможно удалить клиента, так как у него есть активные или ожидающие сделки.')
                ->with('show_delete_modal', true);
        }
        
        // Удаляем документы клиента
        foreach ($client->documents as $document) {
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
        }
        
        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Клиент удален.');
    }



/**
 * Получить историю сделок клиента (для модального окна)
 */
public function getDealHistory(Client $client)
{
    // Получаем все сделки клиента с пагинацией
    $deals = $client->deals()
        ->with(['car', 'manager', 'payments'])
        ->orderBy('created_at', 'desc')
        ->paginate(10);
    
    // Если запрос AJAX - возвращаем только HTML таблицы
    if (request()->ajax()) {
        return view('clients.partials.deal-history-table', compact('client', 'deals'))->render();
    }
    
    // Для полной страницы (если понадобится)
    return view('clients.deal-history', compact('client', 'deals'));
}



    /**
     * Сохранение документов клиента
     */
    private function saveClientDocuments(Client $client, Request $request): void
    {
        $userId = auth()->id();
        
        try {
            // 1. Паспорт (основная)
            if ($request->hasFile('passport_main')) {
                $path = $request->file('passport_main')->store('client_documents', 'public');
                \App\Models\ClientDocument::create([
                    'client_id' => $client->id,
                    'type' => 'passport_main',
                    'name' => 'Паспорт (основная страница)',
                    'document_number' => $client->passport_series . ' ' . $client->passport_number,
                    'file_path' => $path,
                    'issue_date' => $client->passport_issued_date,
                    'uploaded_by' => $userId,
                ]);
            }
            
            // 2. Паспорт (прописка)
            if ($request->hasFile('passport_registration')) {
                $path = $request->file('passport_registration')->store('client_documents', 'public');
                \App\Models\ClientDocument::create([
                    'client_id' => $client->id,
                    'type' => 'passport_registration',
                    'name' => 'Паспорт (страница регистрации)',
                    'document_number' => $client->passport_series . ' ' . $client->passport_number,
                    'file_path' => $path,
                    'uploaded_by' => $userId,
                ]);
            }
            
            // 3. Водительское удостоверение
            if ($request->hasFile('drivers_license_file') && $client->drivers_license) {
                $path = $request->file('drivers_license_file')->store('client_documents', 'public');
                \App\Models\ClientDocument::create([
                    'client_id' => $client->id,
                    'type' => 'drivers_license',
                    'name' => 'Водительское удостоверение',
                    'document_number' => $client->drivers_license,
                    'file_path' => $path,
                    'uploaded_by' => $userId,
                ]);
            }
            
            // 4. Дополнительные документы
            if ($request->hasFile('additional_documents')) {
                foreach ($request->file('additional_documents') as $index => $file) {
                    $path = $file->store('client_documents', 'public');
                    \App\Models\ClientDocument::create([
                        'client_id' => $client->id,
                        'type' => 'additional',
                        'name' => 'Дополнительный документ ' . ($index + 1),
                        'file_path' => $path,
                        'uploaded_by' => $userId,
                    ]);
                }
            }
            
        } catch (\Exception $e) {
            \Log::error('Ошибка сохранения документов: ' . $e->getMessage());
            \Log::error('Trace: ' . $e->getTraceAsString());
        }
    }

    /**
     * Загрузка документа для клиента
     */
    public function uploadDocument(Request $request, Client $client)
    {
        $validated = $request->validate([
            'type' => 'required|in:passport_main,passport_registration,drivers_license,additional,other',
            'name' => 'required|string|max:255',
            'document_number' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'document_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($request->hasFile('document_file')) {
            $path = $request->file('document_file')->store('client_documents', 'public');
            $validated['file_path'] = $path;
        }

        $validated['client_id'] = $client->id;
        $validated['uploaded_by'] = auth()->id();

        ClientDocument::create($validated);

        return back()->with('success', 'Документ успешно загружен.');
    }

/**
 * Удаление документа клиента
 */
public function deleteDocument(Client $client, ClientDocument $document)
{
    \Log::info('Попытка удаления документа', [
        'document_id' => $document->id,
        'client_id' => $client->id,
        'user_id' => auth()->id()
    ]);
    
    // Проверяем, что документ принадлежит клиенту
    if ($document->client_id !== $client->id) {
        \Log::warning('Документ не принадлежит клиенту', [
            'document_client_id' => $document->client_id,
            'request_client_id' => $client->id
        ]);
        return back()->with('error', 'Документ не принадлежит указанному клиенту.');
    }
    
    // Проверяем права доступа
    if (auth()->id() !== $document->uploaded_by && !auth()->user()->isAdmin()) {
        \Log::warning('Недостаточно прав для удаления документа', [
            'document_uploaded_by' => $document->uploaded_by,
            'current_user' => auth()->id()
        ]);
        return back()->with('error', 'Недостаточно прав для удаления документа.');
    }

    try {
        // Удаляем файл
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
            \Log::info('Файл документа удален', ['file_path' => $document->file_path]);
        }

        $document->delete();
        \Log::info('Документ успешно удален из БД', ['document_id' => $document->id]);

        return back()->with('success', 'Документ удален.');
        
    } catch (\Exception $e) {
        \Log::error('Ошибка при удалении документа', [
            'document_id' => $document->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return back()->with('error', 'Ошибка при удалении документа: ' . $e->getMessage());
    }
}
}