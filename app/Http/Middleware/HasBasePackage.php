<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasBasePackage
{
    /**
     * Handle an incoming request.
     *
     * Ensure the authenticated user has an active base_package feature.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Check if user has active base_package feature
        $hasBasePackage = $user->features()
            ->whereHas('orderItem.product', function ($query) {
                $query->where('type', 'base_package');
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->exists();

        if (! $hasBasePackage) {
            return redirect()->route('welcome')
                ->with('error', 'Anda belum memiliki paket aktif. Silakan pilih template dan lakukan pembayaran terlebih dahulu.');
        }

        return $next($request);
    }
}
