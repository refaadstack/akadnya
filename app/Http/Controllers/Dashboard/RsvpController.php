<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Rsvp;
use App\Services\CustomerInvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RsvpController extends Controller
{
    public function __construct(
        private CustomerInvitationService $customerInvitations
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $invitation = $this->customerInvitations->activeInvitation($user);

        if (! $invitation) {
            abort(404, 'Tidak ada undangan aktif');
        }

        $rsvps = Rsvp::where('invitation_id', $invitation->id)
            ->where('is_from_akadnya', false)
            ->with('guest')
            ->latest()
            ->paginate(20);

        $stats = [
            'total' => Rsvp::where('invitation_id', $invitation->id)->where('is_from_akadnya', false)->count(),
            'hadir' => Rsvp::where('invitation_id', $invitation->id)->where('attendance', 'yes')->count(),
            'tidak_hadir' => Rsvp::where('invitation_id', $invitation->id)->where('attendance', 'no')->count(),
        ];

        return Inertia::render('Dashboard/Rsvp/Index', [
            'rsvps' => $rsvps,
            'stats' => $stats,
        ]);
    }

    /**
     * Hide a wish message from the public invitation.
     */
    public function hide(Request $request, Rsvp $rsvp): RedirectResponse
    {
        $this->authorizeOwner($request, $rsvp);

        $rsvp->update(['is_hidden' => true]);

        return back()->with('success', 'Ucapan disembunyikan dari undangan');
    }

    /**
     * Show a previously hidden wish message on the public invitation.
     */
    public function show(Request $request, Rsvp $rsvp): RedirectResponse
    {
        $this->authorizeOwner($request, $rsvp);

        $rsvp->update(['is_hidden' => false]);

        return back()->with('success', 'Ucapan ditampilkan kembali di undangan');
    }

    private function authorizeOwner(Request $request, Rsvp $rsvp): void
    {
        // The Invitation global scope hides other users' invitations, so a
        // missing relation means the RSVP does not belong to this user.
        $invitation = $rsvp->invitation;

        if (! $invitation || $invitation->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
