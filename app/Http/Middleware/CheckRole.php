<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role)
    {
        if (!auth()->check()) {
            return redirect('login');
        }

        if ($role === 'admin' && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        if ($role === 'worker' && !auth()->user()->isWorker()) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
