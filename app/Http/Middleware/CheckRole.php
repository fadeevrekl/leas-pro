<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Проверяем, авторизован ли пользователь
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        // Получаем текущего пользователя
        $user = Auth::user();
        
        // Проверяем, есть ли у пользователя нужная роль
        if (!in_array($user->role, $roles)) {
            // Если нет доступа - перенаправляем на главную с ошибкой
            return redirect('/')->with('error', 'У вас нет доступа к этой странице');
        }
        
        return $next($request);
    }
}