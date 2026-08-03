<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasInvitationAccess
{
    /**
     * Handle an incoming request.
     *
     * Ensure the authenticated user either owns an invitation (template
     * purchased à la carte, which includes full access) or has an active
     * base_package feature (legacy bundled purchases).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $hasInvitation = $user->invitations()->exists();

        $hasActivePackage = $user->features()
            ->whereHas('orderItem.product', function ($query) {
                $query->where('type', 'base_package');
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->exists();

        if (! $hasInvitation && ! $hasActivePackage) {
            return redirect()->route('welcome')
                ->with('error', 'Anda belum memiliki undangan aktif. Silakan pilih template dan lakukan pembayaran terlebih dahulu.');
        }

        return $next($request);
    }
}
