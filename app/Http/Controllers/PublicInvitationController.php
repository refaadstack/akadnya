<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Invitation;
use App\Services\BladeRenderService;
use App\Services\DataContractBuilder;
use App\Services\SeoMetaService;
use Illuminate\Http\Request;

class PublicInvitationController extends Controller
{
    public function __construct(
        protected BladeRenderService $bladeRenderer,
        protected DataContractBuilder $dataBuilder,
        protected SeoMetaService $seoMeta
    ) {}

    /**
     * Show public invitation
     */
    public function show(Request $request, string $subdomain)
    {
        // Find invitation by subdomain or custom domain
        $invitation = Invitation::where('subdomain', $subdomain)
            ->orWhere('custom_domain', $subdomain)
            ->with(['template', 'content', 'sections.templateSection', 'ornaments.templateOrnament', 'gallery'])
            ->firstOrFail();

        // Check if published
        if ($invitation->status !== 'published') {
            abort(404, 'Undangan belum dipublikasikan');
        }

        // Check if template directory exists
        if (! file_exists($invitation->template->getFolderPath())) {
            abort(500, 'Template files not found');
        }

        // Get guest name from URL parameter (optional)
        $guestName = $request->query('name');

        // Get guest unique code from URL parameter (barcode / QR payload)
        $guestCode = $request->query('guest');

        // Bind the guest session from the personal link so the later RSVP
        // submit (which posts without the code) can be linked to this guest.
        if (is_string($guestCode) && $guestCode !== '') {
            $linkedGuest = $invitation->guests()->where('unique_code', $guestCode)->first();

            if ($linkedGuest) {
                $request->session()->put($this->guestSessionKey($invitation), $linkedGuest->id);
            }
        }

        // Increment view count
        $invitation->increment('view_count');

        // Build data contract
        $data = $this->dataBuilder->build($invitation, $guestName, $guestCode);

        // Render invitation HTML
        $html = $this->bladeRenderer->renderInvitation($invitation, $data);

        // Inject SEO meta tags (per-invitation, with Akadnya.com branding)
        $html = $this->seoMeta->inject($html, $this->seoMeta->forInvitation($invitation, $data));

        return response($html)->header('Content-Type', 'text/html');
    }

    /**
     * Submit RSVP
     */
    public function rsvp(Request $request, string $subdomain)
    {
        $invitation = Invitation::where('subdomain', $subdomain)
            ->orWhere('custom_domain', $subdomain)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'attendance' => 'required|in:yes,no',
            'pax_count' => 'required_if:attendance,yes|integer|min:1|max:10',
            'message' => 'nullable|string|max:500',
        ]);

        // RSVP is only accepted from a guest on the invitation list, resolved
        // from the personal link (session or code) with an exact-name
        // fallback. This keeps every RSVP linked to a Guest row.
        $guest = $this->resolveGuest($request, $invitation, $validated['name']);

        if (! $guest) {
            return back()->withErrors([
                'rsvp' => 'Tautan undangan tidak valid. Silakan buka tautan undangan personal Anda atau hubungi mempelai.',
            ]);
        }

        // Wishes are hidden by the couple: drop any message content entirely.
        if (! ($invitation->content?->show_wishes ?? true)) {
            $validated['message'] = null;
        }

        // One confirmation per guest: re-submits update the existing row.
        // Pax can never exceed the guest's max_pax allocation.
        $invitation->rsvps()->updateOrCreate(
            ['guest_id' => $guest->id],
            [
                'name' => $guest->name,
                'attendance' => $validated['attendance'],
                'pax_count' => $validated['attendance'] === 'yes'
                    ? min($validated['pax_count'] ?? 1, $guest->max_pax)
                    : 0,
                'message' => $validated['message'] ?? null,
            ]
        );

        $request->session()->put($this->guestSessionKey($invitation), $guest->id);

        return back()->with('success', 'Terima kasih atas konfirmasi Anda!');
    }

    /**
     * Session key binding a guest to an invitation's public page.
     */
    private function guestSessionKey(Invitation $invitation): string
    {
        return "invitation_guest.{$invitation->id}";
    }

    /**
     * Resolve the invited guest for an RSVP submit: explicit code first,
     * then the personal-link session, then an exact single-name match.
     */
    private function resolveGuest(Request $request, Invitation $invitation, string $name): ?Guest
    {
        $code = $request->query('guest', $request->input('guest'));

        if (is_string($code) && $code !== '') {
            $guest = $invitation->guests()->where('unique_code', $code)->first();

            if ($guest) {
                return $guest;
            }
        }

        $guestId = $request->session()->get($this->guestSessionKey($invitation));

        if ($guestId) {
            $guest = $invitation->guests()->whereKey($guestId)->first();

            if ($guest) {
                return $guest;
            }
        }

        $matches = $invitation->guests()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($name))])
            ->get();

        if ($matches->count() === 1) {
            return $matches->first();
        }

        return null;
    }

    /**
     * Get wishes/messages by subdomain
     */
    public function wishes(Request $request, string $subdomain)
    {
        $invitation = Invitation::where('subdomain', $subdomain)
            ->orWhere('custom_domain', $subdomain)
            ->firstOrFail();

        $wishes = $invitation->rsvps()
            ->whereNotNull('message')
            ->where('is_hidden', false)
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => $wishes->map(fn ($rsvp) => [
                'id' => $rsvp->id,
                'name' => $rsvp->name ?? $rsvp->guest?->name ?? 'Tamu',
                'message' => $rsvp->message,
                'attendance' => $rsvp->attendance,
                'created_at' => $rsvp->created_at->toISOString(),
            ]),
        ]);
    }

    /**
     * Get wishes/messages by invitation ID
     */
    public function wishesByInvitationId(Request $request, int $invitationId)
    {
        $invitation = Invitation::findOrFail($invitationId);

        $wishes = $invitation->rsvps()
            ->whereNotNull('message')
            ->where('is_hidden', false)
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => $wishes->map(fn ($rsvp) => [
                'id' => $rsvp->id,
                'name' => $rsvp->name ?? $rsvp->guest?->name ?? 'Tamu',
                'message' => $rsvp->message,
                'attendance' => $rsvp->attendance,
                'created_at' => $rsvp->created_at->toISOString(),
            ]),
        ]);
    }
}
