<?php

namespace App\Console\Commands;

use App\Models\Invitation;
use App\Services\InvitationService;
use Illuminate\Console\Command;

class BackfillInvitationAutoWishes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invitations:backfill-auto-wishes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the MyAkad welcome wish for published invitations that do not have one yet';

    /**
     * Execute the console command.
     */
    public function handle(InvitationService $invitationService): int
    {
        $invitations = Invitation::query()
            ->where('status', 'published')
            ->whereDoesntHave('rsvps', fn ($query) => $query->where('is_from_myakad', true))
            ->with('content')
            ->get();

        if ($invitations->isEmpty()) {
            $this->info('All published invitations already have the MyAkad welcome wish.');

            return self::SUCCESS;
        }

        foreach ($invitations as $invitation) {
            $invitationService->ensureMyAkadAutoWish($invitation);
            $this->line("  - Seeded wish for /i/{$invitation->subdomain}");
        }

        $this->newLine();
        $this->info("Done! Added the MyAkad welcome wish to {$invitations->count()} invitation(s).");

        return self::SUCCESS;
    }
}
