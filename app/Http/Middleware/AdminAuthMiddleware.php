<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated admin request.'], 401);
            }
            return redirect()->guest(route('admin.login'))->with('error', 'Please login with your admin credentials to access the Super Admin Dashboard.');
        }

        $user = Auth::user();

        // Check if user has admin privileges
        if (!in_array($user->role, ['admin', 'super_admin', 'manager', 'staff'])) {
            Auth::logout();
            return redirect()->route('admin.login')->with('error', 'Access denied. You do not have permission to access the Super Admin Dashboard.');
        }

        // Check if account is active
        if ($user->status === 'blocked') {
            Auth::logout();
            return redirect()->route('admin.login')->with('error', 'Your administrative account has been deactivated. Please contact the administrator.');
        }

        return $next($request);
    }
}
