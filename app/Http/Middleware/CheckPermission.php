<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Usage: middleware('permission:products.create')
     * atau: middleware('permission:products.create,products.edit')  (salah satu cukup)
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = auth()->user();

        if (! $user || ! $user->hasAnyPermission($permissions)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses ditolak. Anda tidak memiliki izin.'], 403);
            }
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk tindakan ini.');
        }

        return $next($request);
    }
}
