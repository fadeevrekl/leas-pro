<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminManager
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        $user = Auth::user();
        
        // Разрешаем доступ только админам и менеджерам
        if (!in_array($user->role, ['admin', 'manager'])) {
            abort(403, 'Доступ запрещен. Требуется роль администратора или менеджера.');
        }
        
        return $next($request);
    }
}