<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!empty($roles) && !in_array($user->role, $roles)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: Anda tidak memiliki wewenang untuk tindakan ini.',
                ], 403);
            }

            $redirectRoute = $user->isTeknisi() ? 'maps.index' : 'dashboard';
            return redirect()->route($redirectRoute)->with('warning', 'Akses dibatasi: Akun Anda (' . ucfirst($user->role ?? 'Pengguna') . ') tidak memiliki wewenang untuk halaman tersebut.');
        }

        return $next($request);
    }
}
