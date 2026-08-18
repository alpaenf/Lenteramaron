<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        $userRole = strtolower(trim($request->user()->role ?? ''));
        $allowedRoles = array_map('strtolower', array_map('trim', $roles));

        // Admin / Pustakawan has access to admin routes
        if (in_array($userRole, ['admin', 'pustakawan'])) {
            return $next($request);
        }

        if (! in_array($userRole, $allowedRoles)) {
            abort(403, 'Anda tidak memiliki hak akses untuk membuka halaman ini.');
        }

        return $next($request);
    }
}
