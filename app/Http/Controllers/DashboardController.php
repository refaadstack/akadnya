<?php

namespace App\Http\Controllers;

use App\Models\GuestBookEntry;
use App\Models\Invitation;
use App\Services\CustomerInvitationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private CustomerInvitationService $customerInvitations
    ) {}

    /**
     * Display the dashboard.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $ownedInvitations = $this->customerInvitations->ownedInvitations($user);
        $invitation = $this->customerInvitations->activeInvitation($user, ['template', 'content']);
        $invitationOptions = $ownedInvitations->map(fn (Invitation $ownedInvitation) => [
            'id' => $ownedInvitation->id,
            'status' => $ownedInvitation->status,
            'subdomain' => $ownedInvitation->subdomain,
            'custom_domain' => $ownedInvitation->custom_domain,
            'url' => $ownedInvitation->getPublicUrl(),
            'template' => $ownedInvitation->template ? [
                'id' => $ownedInvitation->template->id,
                'name' => $ownedInvitation->template->name,
                'slug' => $ownedInvitation->template->slug,
                'thumbnail_url' => $ownedInvitation->template->thumbnail_url,
            ] : null,
            'is_active' => $invitation?->id === $ownedInvitation->id,
        ])->values();

        // If no invitation, show empty state
        if (! $invitation) {
            return Inertia::render('Dashboard', [
                'stats' => [
                    'total_invitations' => 0,
                    'total_guests' => 0,
                    'confirmed_rsvps' => 0,
                    'total_views' => 0,
                ],
                'invitation' => null,
                'analytics' => null,
                'recentRsvps' => [],
                'recentWishes' => [],
                'invitationOptions' => $invitationOptions,
            ]);
        }

        // Basic stats
        $stats = [
            'total_invitations' => $user->invitations()->count(),
            'total_guests' => $invitation->guests()->count(),
            'confirmed_rsvps' => $invitation->rsvps()->where('attendance', 'yes')->count(),
            'total_views' => $invitation->view_count,
        ];

        // Detailed analytics
        $analytics = [
            'total_views' => $invitation->view_count ?? 0,
            'total_guests' => $invitation->guests()->count(),
            'total_rsvp' => $invitation->rsvps()->count(),
            'rsvp_attending' => $invitation->rsvps()->where('attendance', 'yes')->count(),
            'rsvp_not_attending' => $invitation->rsvps()->where('attendance', 'no')->count(),
            'total_pax' => $invitation->rsvps()->where('attendance', 'yes')->sum('pax_count'),
            'total_wishes' => $invitation->rsvps()->whereNotNull('message')->count(),
            'total_gallery_photos' => $invitation->gallery()->count(),
            'attendance_rate' => $invitation->rsvps()->count() > 0
                ? (int) round($invitation->rsvps()->where('attendance', 'yes')->count() / $invitation->rsvps()->count() * 100)
                : 0,
            'total_check_ins' => GuestBookEntry::where('invitation_id', $invitation->id)->count(),
            'rsvp_trend' => $this->buildRsvpTrend($invitation),
        ];

        // Get recent RSVPs (last 5)
        $recentRsvps = $invitation->rsvps()
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($rsvp) => [
                'id' => $rsvp->id,
                'name' => $rsvp->name ?? $rsvp->guest?->name ?? 'Tamu',
                'attendance' => $rsvp->attendance,
                'pax_count' => $rsvp->pax_count,
                'message' => $rsvp->message ? \Str::limit($rsvp->message, 50) : null,
                'created_at' => $rsvp->created_at->diffForHumans(),
            ]);

        // Get recent wishes (last 5)
        $recentWishes = $invitation->rsvps()
            ->whereNotNull('message')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($rsvp) => [
                'id' => $rsvp->id,
                'name' => $rsvp->name ?? $rsvp->guest?->name ?? 'Tamu',
                'message' => \Str::limit($rsvp->message, 100),
                'created_at' => $rsvp->created_at->diffForHumans(),
            ]);

        // Get invitation data
        $invitationData = [
            'id' => $invitation->id,
            'status' => $invitation->status,
            'subdomain' => $invitation->subdomain,
            'custom_domain' => $invitation->custom_domain,
            'url' => $invitation->custom_domain
                ? 'https://'.$invitation->custom_domain
                : rtrim(config('app.url'), '/').'/i/'.$invitation->subdomain,
            'published_at' => $invitation->published_at?->format('d M Y H:i'),
            'template' => $invitation->template ? [
                'name' => $invitation->template->name,
                'slug' => $invitation->template->slug,
            ] : null,
            'bride_name' => $invitation->content?->bride_name,
            'groom_name' => $invitation->content?->groom_name,
            'akad_datetime' => $invitation->content?->akad_datetime,
        ];

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'invitation' => $invitationData,
            'analytics' => $analytics,
            'recentRsvps' => $recentRsvps,
            'recentWishes' => $recentWishes,
            'invitationOptions' => $invitationOptions,
        ]);
    }

    public function selectInvitation(Request $request, Invitation $invitation)
    {
        $this->customerInvitations->selectInvitation($request->user(), $invitation);

        return back()->with('success', 'Template aktif berhasil dipilih.');
    }

    /**
     * Build a 14-day RSVP trend (total and attending per day).
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildRsvpTrend(Invitation $invitation): array
    {
        $start = now()->subDays(13)->startOfDay();

        $rows = $invitation->rsvps()
            ->where('created_at', '>=', $start)
            ->get(['created_at', 'attendance'])
            ->groupBy(fn ($rsvp) => $rsvp->created_at->format('Y-m-d'));

        $trend = [];

        for ($i = 13; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $key = $day->format('Y-m-d');
            $items = $rows->get($key, collect());

            $trend[] = [
                'date' => $key,
                'label' => $day->format('d M'),
                'total' => $items->count(),
                'attending' => $items->where('attendance', 'yes')->count(),
            ];
        }

        return $trend;
    }
}
