<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest($request->is('admin*') ? route('admin.login') : route('scan'));
        }

        if ($role === 'admin' && ! $user->isAdmin()) {
            return redirect()->route('student.dashboard')->with('error', 'You do not have access to the admin panel.');
        }

        if ($role === 'student') {
            if (! $user->isStudent()) {
                return redirect()->route('admin.dashboard');
            }

            if (! $user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('scan')->with('error', 'Your student account is inactive. Please contact the librarian.');
            }
        }

        return $next($request);
    }
}
