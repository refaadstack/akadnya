<?php

namespace App\Http\Middleware;

use App\Models\Invitation;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveInvitation
{
    /**
     * Handle an incoming request.
     *
     * Resolve invitation from subdomain or custom domain.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $invitation = null;

        $appDomain = config('app.domain');
        $testDomain = str_replace('.id', '.test', $appDomain);

        // Check if it's a subdomain (e.g., john-jane.akadnya.com)
        if (str_ends_with($host, '.'.$appDomain) || str_ends_with($host, '.'.$testDomain)) {
            $subdomain = explode('.', $host)[0];

            // Skip if it's the main domain
            $mainDomain = explode('.', $appDomain)[0];
            if (! in_array($subdomain, [$mainDomain, 'www'])) {
                $invitation = Invitation::where('subdomain', $subdomain)
                    ->where('status', 'published')
                    ->first();
            }
        } else {
            // Check if it's a custom domain
            $invitation = Invitation::where('custom_domain', $host)
                ->where('status', 'published')
                ->first();
        }

        if (! $invitation) {
            abort(404, 'Undangan tidak ditemukan atau belum dipublikasikan.');
        }

        // Share invitation with the request
        $request->merge(['invitation' => $invitation]);

        // Also make it available in views
        view()->share('invitation', $invitation);

        return $next($request);
    }
}
