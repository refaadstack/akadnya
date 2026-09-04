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

    /**
     * Link a legacy orphan RSVP (no guest_id) to a guest on the list.
     */
    public function link(Request $request, Rsvp $rsvp): RedirectResponse
    {
        $this->authorizeOwner($request, $rsvp);

        $validated = $request->validate([
            'guest_id' => 'required|integer',
        ]);

        $guest = $rsvp->invitation->guests()->whereKey($validated['guest_id'])->firstOrFail();

        if ($guest->rsvp()->whereKeyNot($rsvp->id)->exists()) {
            abort(422, 'Tamu tersebut sudah memiliki konfirmasi lain.');
        }

        $rsvp->update([
            'guest_id' => $guest->id,
            'name' => $guest->name,
        ]);

        return back()->with('success', "RSVP dihubungkan ke {$guest->name}");
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
