<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetTheme
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Простая версия - ничего не делаем
        return $next($request);
    }
}