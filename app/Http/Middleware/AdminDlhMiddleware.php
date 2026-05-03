<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminDlhMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(
    Request $request,
    Closure $next
    ): Response {

        $user = auth()->user();

        if (
            !$user ||
            $user->role !== 'admin_dlh'
        ) {
            return response()->json([
                'message' => 'Forbidden - hanya admin DLH'
            ], 403);
        }

        return $next($request);
    }
}