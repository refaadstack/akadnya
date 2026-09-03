<?php

namespace App\Console\Commands;

use App\Models\Invitation;
use App\Models\InvitationSection;
use Illuminate\Console\Command;

class SyncInvitationSections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invitations:sync-sections';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill missing InvitationSection rows for invitations that have none yet';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $invitations = Invitation::query()
            ->with('template')
            ->whereDoesntHave('sections')
            ->get();

        if ($invitations->isEmpty()) {
            $this->info('All invitations already have sections.');

            return self::SUCCESS;
        }

        foreach ($invitations as $invitation) {
            if (! $invitation->template) {
                $this->warn("  - Skip /i/{$invitation->subdomain}: no template");

                continue;
            }

            $sections = $invitation->template->sections()->orderBy('sort_order')->get();

            foreach ($sections as $section) {
                InvitationSection::create([
                    'invitation_id' => $invitation->id,
                    'template_section_id' => $section->id,
                    'sort_order' => $section->sort_order,
                    'is_visible' => true,
                ]);
            }

            $this->line("  - Synced {$sections->count()} section(s) for /i/{$invitation->subdomain} ({$invitation->template->name})");
        }

        $this->newLine();
        $this->info("Done! Synced sections for {$invitations->count()} invitation(s).");

        return self::SUCCESS;
    }
}
