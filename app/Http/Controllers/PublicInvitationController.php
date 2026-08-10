<?php

namespace App\Http\Controllers;

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

        // Increment view count
        $invitation->increment('view_count');

        // Build data contract
        $data = $this->dataBuilder->build($invitation, $guestName, $guestCode);

        // Render invitation HTML
        $html = $this->bladeRenderer->renderInvitation($invitation, $data);

        // Inject SEO meta tags (per-invitation, with MyAkad branding)
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

        // Create RSVP directly without guest record
        $invitation->rsvps()->create([
            'name' => $validated['name'],
            'attendance' => $validated['attendance'],
            'pax_count' => $validated['attendance'] === 'yes' ? $validated['pax_count'] : 0,
            'message' => $validated['message'],
        ]);

        return back()->with('success', 'Terima kasih atas konfirmasi Anda!');
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
