<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Rsvp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RsvpController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        // The RSVP list now lives as a tab on the Guests page.
        return redirect()->route('dashboard.guests', ['tab' => 'rsvp']);
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
