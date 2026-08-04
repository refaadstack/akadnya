<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\GuestBookEntry;
use App\Models\Invitation;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Digital guest book (venue) service: gate check-in, souvenir pickup,
 * and raffle draws. All entries are scoped to a single invitation.
 */
class GuestBookService
{
    public function checkIn(Invitation $invitation, Guest $guest, ?array $meta = null): GuestBookEntry
    {
        $this->assertGuestBelongsToInvitation($invitation, $guest);

        if ($this->hasEntry($invitation, $guest, 'check_in')) {
            throw new InvalidArgumentException('Tamu ini sudah check-in.');
        }

        return $invitation->guestBookEntries()->create([
            'guest_id' => $guest->id,
            'event_type' => 'check_in',
            'meta' => $meta,
        ]);
    }

    public function takeSouvenir(Invitation $invitation, Guest $guest, ?array $meta = null): GuestBookEntry
    {
        $this->assertGuestBelongsToInvitation($invitation, $guest);

        if (! $this->hasEntry($invitation, $guest, 'check_in')) {
            throw new InvalidArgumentException('Tamu harus check-in terlebih dahulu sebelum mengambil souvenir.');
        }

        if ($this->hasEntry($invitation, $guest, 'souvenir')) {
            throw new InvalidArgumentException('Souvenir untuk tamu ini sudah diambil.');
        }

        return $invitation->guestBookEntries()->create([
            'guest_id' => $guest->id,
            'event_type' => 'souvenir',
            'meta' => $meta,
        ]);
    }

    public function raffleWinner(Invitation $invitation, ?array $meta = null): ?GuestBookEntry
    {
        $checkedInGuestIds = $invitation->guestBookEntries()
            ->where('event_type', 'check_in')
            ->pluck('guest_id');

        $winnerGuestIds = $invitation->guestBookEntries()
            ->where('event_type', 'raffle')
            ->pluck('guest_id');

        $eligibleIds = $checkedInGuestIds->diff($winnerGuestIds);

        if ($eligibleIds->isEmpty()) {
            return null;
        }

        $guest = Guest::whereKey($eligibleIds->shuffle()->first())->firstOrFail();

        return $invitation->guestBookEntries()->create([
            'guest_id' => $guest->id,
            'event_type' => 'raffle',
            'meta' => $meta,
        ]);
    }

    public function statusFor(Invitation $invitation, Guest $guest): array
    {
        return [
            'checked_in' => $this->hasEntry($invitation, $guest, 'check_in'),
            'souvenir_taken' => $this->hasEntry($invitation, $guest, 'souvenir'),
            'raffle_winner' => $this->hasEntry($invitation, $guest, 'raffle'),
        ];
    }

    public function entriesFor(Invitation $invitation): Collection
    {
        return $invitation->guestBookEntries()
            ->with('guest')
            ->latest()
            ->get();
    }

    public function checkedInGuests(Invitation $invitation): Collection
    {
        $guestIds = $invitation->guestBookEntries()
            ->where('event_type', 'check_in')
            ->pluck('guest_id');

        return Guest::whereKey($guestIds)->get();
    }

    public function hasEntry(Invitation $invitation, Guest $guest, string $eventType): bool
    {
        return $invitation->guestBookEntries()
            ->where('guest_id', $guest->id)
            ->where('event_type', $eventType)
            ->exists();
    }

    protected function assertGuestBelongsToInvitation(Invitation $invitation, Guest $guest): void
    {
        if ($guest->invitation_id !== $invitation->id) {
            throw new InvalidArgumentException('Tamu tidak terdaftar pada undangan ini.');
        }
    }
}
