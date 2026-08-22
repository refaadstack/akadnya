<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Rsvp;
use App\Services\CustomerInvitationService;
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
        
        if (!$invitation) {
            abort(404, 'Tidak ada undangan aktif');
        }

        $rsvps = Rsvp::where('invitation_id', $invitation->id)
            ->with('guest')
            ->latest()
            ->paginate(20);

        $stats = [
            'total' => Rsvp::where('invitation_id', $invitation->id)->count(),
            'hadir' => Rsvp::where('invitation_id', $invitation->id)->where('attendance', 'yes')->count(),
            'tidak_hadir' => Rsvp::where('invitation_id', $invitation->id)->where('attendance', 'no')->count(),
        ];

        return Inertia::render('Dashboard/Rsvp/Index', [
            'rsvps' => $rsvps,
            'stats' => $stats,
        ]);
    }
}
