<?php

namespace App\Console\Commands;

use App\Models\Rsvp;
use Illuminate\Console\Command;

class BackfillRsvpGuests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rsvps:backfill-guests {--apply : Actually link orphans instead of a dry run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Link orphan RSVPs (guest_id null) to guests by exact single-name match';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        $orphans = Rsvp::query()
            ->whereNull('guest_id')
            ->where('is_from_akadnya', false)
            ->with('invitation')
            ->get();

        if ($orphans->isEmpty()) {
            $this->info('No orphan RSVPs to link.');

            return self::SUCCESS;
        }

        $linked = 0;
        $ambiguous = 0;
        $unmatched = 0;
        $taken = 0;

        foreach ($orphans as $rsvp) {
            $matches = $rsvp->invitation->guests()
                ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim((string) $rsvp->name))])
                ->get();

            if ($matches->count() === 1) {
                $guest = $matches->first();

                // One confirmation per guest (unique rsvps.guest_id).
                if ($guest->rsvp()->whereKeyNot($rsvp->id)->exists()) {
                    $this->warn("RSVP #{$rsvp->id} '{$rsvp->name}': Guest #{$guest->id} already has a confirmation, skipped.");
                    $taken++;

                    continue;
                }

                $this->line("RSVP #{$rsvp->id} '{$rsvp->name}' -> Guest #{$guest->id} '{$guest->name}'");

                if ($apply) {
                    $rsvp->update(['guest_id' => $guest->id]);
                }

                $linked++;
            } elseif ($matches->count() > 1) {
                $this->warn("RSVP #{$rsvp->id} '{$rsvp->name}': {$matches->count()} guests share this name, skipped.");
                $ambiguous++;
            } else {
                $this->warn("RSVP #{$rsvp->id} '{$rsvp->name}': no matching guest, skipped.");
                $unmatched++;
            }
        }

        $this->info("Linked: {$linked}, already taken: {$taken}, ambiguous: {$ambiguous}, unmatched: {$unmatched}".($apply ? '' : ' (dry run, pass --apply to link)'));

        return self::SUCCESS;
    }
}
