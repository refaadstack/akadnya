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
     * Ensure the authenticated user owns an invitation (template
     * purchased à la carte, which includes full access).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->invitations()->exists()) {
            return redirect()->route('welcome')
                ->with('error', 'Anda belum memiliki undangan aktif. Silakan pilih template dan lakukan pembayaran terlebih dahulu.');
        }

        return $next($request);
    }
}
