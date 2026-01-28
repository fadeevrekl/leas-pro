<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Показать форму входа
     */
    public function showLoginForm()
    {
        // Если пользователь уже авторизован, перенаправляем на главную
        if (Auth::check()) {
            return redirect('/');
        }
        
        return view('auth.login');
    }

    /**
     * Обработка входа в систему
     */
public function login(Request $request)
{
    // Валидация данных
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    // Попытка аутентификации
    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();

        // Редирект в зависимости от роли
        $user = Auth::user();
        
        // Проверяем активность пользователя
        if (!$user->is_active) {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Ваш аккаунт отключен. Обратитесь к администратору.',
            ]);
        }
        
        // Редирект по ролям
        switch ($user->role) {
            case 'admin':
                return redirect()->route('admin.users.index');
            case 'manager':
                return redirect()->route('deals.index');
            case 'investor':
                return redirect()->route('investor.dashboard');
            default:
                return redirect()->intended('/');
        }
    }

    // Если аутентификация не удалась
    return back()->withErrors([
        'email' => 'Неверные учетные данные.',
    ])->onlyInput('email');
}

    /**
     * Выход из системы
     */
    public function logout(Request $request)
    {
        // Выходим из системы
        Auth::logout();
        
        // Уничтожаем сессию
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Перенаправляем на страницу входа
        return redirect('/login');
    }
}