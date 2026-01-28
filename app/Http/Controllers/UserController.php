<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Получаем только менеджеров и инвесторов (не админов)
        $users = User::where('role', '!=', 'admin')->get();
        
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
public function store(Request $request)
{
    $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:6',
        'role' => ['required', Rule::in(['manager', 'investor'])],
    ];
    
    // Для инвесторов добавляем правило для процента комиссии
if ($request->role === 'investor') {
    $rules['commission_percent'] = 'required|integer|min:0|max:100';
}
    
    $request->validate($rules);
    
    $userData = [
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => $request->role,
        'is_active' => true,
    ];
    
    // Добавляем процент комиссии для инвесторов
    if ($request->role === 'investor') {
        $userData['commission_percent'] = $request->commission_percent;
    }
    
    User::create($userData);
    
    return redirect()->route('admin.users.index')
        ->with('success', 'Пользователь успешно создан');
}






    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        // Проверяем, что это не администратор
        if ($user->role === 'admin') {
            return redirect()->route('admin.users.index')
                ->with('error', 'Нет доступа к просмотру администратора');
        }
        
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        // Проверяем, что это не администратор
        if ($user->role === 'admin') {
            return redirect()->route('admin.users.index')
                ->with('error', 'Нет доступа к редактированию администратора');
        }
        
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
public function update(Request $request, User $user)
{
    // Проверяем, что это не администратор
    if ($user->role === 'admin') {
        return redirect()->route('admin.users.index')
            ->with('error', 'Нет доступа к редактированию администратора');
    }
    
    // Валидация данных
    $rules = [
        'name' => 'required|string|max:255',
        'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
        'is_active' => 'boolean',
    ];
    
// Для инвесторов добавляем правило для процента комиссии
if ($user->role === 'investor') {
    $rules['commission_percent'] = 'required|integer|min:0|max:100';
}
    
    $request->validate($rules);
    
    // Обновляем данные
    $updateData = [
        'name' => $request->name,
        'email' => $request->email,
        'is_active' => $request->has('is_active') ? true : false,
    ];
    
    // Добавляем процент комиссии для инвесторов
    if ($user->role === 'investor' && $request->has('commission_percent')) {
        $updateData['commission_percent'] = $request->commission_percent;
    }
    
    $user->update($updateData);
    
    // Если указан новый пароль
    if ($request->filled('password')) {
        $user->update([
            'password' => Hash::make($request->password)
        ]);
    }
    
    return redirect()->route('admin.users.index')
        ->with('success', 'Пользователь успешно обновлен');
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Проверяем, что это не администратор
        if ($user->role === 'admin') {
            return redirect()->route('admin.users.index')
                ->with('error', 'Невозможно удалить администратора');
        }
        
        $user->delete();
        
        return redirect()->route('admin.users.index')
            ->with('success', 'Пользователь успешно удален');
    }
}
