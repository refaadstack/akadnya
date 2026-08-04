<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Services\CustomerInvitationService;
use App\Services\GuestBookService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class GuestBookController extends Controller
{
    public function __construct(
        private CustomerInvitationService $customerInvitations,
        private GuestBookService $guestBook
    ) {}

    /**
     * Show guest book dashboard: stats, recent entries, and raffle.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $this->authorizeGuestBook($user);

        $invitation = $this->customerInvitations->activeInvitation($user);
        abort_if(! $invitation, 404);

        $entries = $this->guestBook->entriesFor($invitation);

        return Inertia::render('Dashboard/GuestBook/Index', [
            'invitation' => [
                'id' => $invitation->id,
                'subdomain' => $invitation->subdomain,
                'status' => $invitation->status,
            ],
            'stats' => [
                'checked_in' => $entries->where('event_type', 'check_in')->count(),
                'souvenirs' => $entries->where('event_type', 'souvenir')->count(),
                'raffles' => $entries->where('event_type', 'raffle')->count(),
            ],
            'entries' => $entries->map(fn ($entry) => [
                'id' => $entry->id,
                'event_type' => $entry->event_type,
                'guest_id' => $entry->guest_id,
                'guest_name' => $entry->guest?->name ?? 'Tamu tanpa nama',
                'created_at' => $entry->created_at?->toIso8601String(),
            ]),
        ]);
    }

    /**
     * Show the camera scanner page.
     */
    public function scan(Request $request): Response
    {
        $user = $request->user();
        $this->authorizeGuestBook($user);

        $invitation = $this->customerInvitations->activeInvitation($user);
        abort_if(! $invitation, 404);

        return Inertia::render('Dashboard/GuestBook/Scan', [
            'invitation' => [
                'id' => $invitation->id,
                'subdomain' => $invitation->subdomain,
            ],
        ]);
    }

    /**
     * Check a guest in by unique code or guest id.
     */
    public function checkIn(Request $request): RedirectResponse
    {
        $guest = $this->resolveGuest($request);

        try {
            $this->guestBook->checkIn($guest->invitation, $guest, [
                'checked_in_by' => $request->user()->id,
            ]);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "{$guest->name} berhasil check-in.");
    }

    /**
     * Mark a guest's souvenir as taken.
     */
    public function souvenir(Request $request): RedirectResponse
    {
        $guest = $this->resolveGuest($request);

        try {
            $this->guestBook->takeSouvenir($guest->invitation, $guest, [
                'handed_by' => $request->user()->id,
            ]);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Souvenir untuk {$guest->name} sudah diambil.");
    }

    /**
     * Draw a raffle winner among checked-in guests.
     */
    public function raffle(Request $request): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeGuestBook($user);

        $invitation = $this->customerInvitations->activeInvitation($user);
        abort_if(! $invitation, 404);

        $winner = $this->guestBook->raffleWinner($invitation, [
            'drawn_by' => $user->id,
        ]);

        if (! $winner) {
            return back()->with('error', 'Belum ada tamu yang check-in atau semua sudah pernah menang.');
        }

        return back()->with('success', 'Pemenang undian: '.($winner->guest?->name ?? 'Tamu tanpa nama'));
    }

    protected function resolveGuest(Request $request): Guest
    {
        $user = $request->user();
        $this->authorizeGuestBook($user);

        $invitation = $this->customerInvitations->activeInvitation($user);
        abort_if(! $invitation, 404);

        $validated = $request->validate([
            'guest_id' => 'nullable|integer|exists:guests,id',
            'code' => 'nullable|string|max:255',
        ]);

        $guest = ($validated['guest_id'] ?? null)
            ? Guest::find($validated['guest_id'])
            : Guest::where('invitation_id', $invitation->id)
                ->where('unique_code', $validated['code'] ?? '')
                ->first();

        abort_if(! $guest || $guest->invitation_id !== $invitation->id, 422, 'Kode tamu tidak ditemukan.');

        return $guest;
    }

    protected function authorizeGuestBook($user): void
    {
        abort_unless($user && $user->hasFeature('guest_book'), 403, 'Fitur Buku Tamu belum aktif untuk akun Anda.');
    }
}
