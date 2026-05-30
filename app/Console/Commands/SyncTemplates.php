<?php

namespace App\Console\Commands;

use App\Services\TemplateService;
use Illuminate\Console\Command;

class SyncTemplates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'templates:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync templates from storage/app/public/templates/ to database';

    /**
     * Execute the console command.
     */
    public function handle(TemplateService $templateService): int
    {
        $this->info('Syncing templates from storage/app/public/templates/...');
        $this->newLine();

        $result = $templateService->syncTemplates();

        if ($result['synced'] > 0) {
            $this->info("✓ Successfully synced {$result['synced']} template(s)");
        } else {
            $this->warn('No templates synced');
        }

        if (! empty($result['errors'])) {
            $this->newLine();
            $this->error('Errors encountered:');
            foreach ($result['errors'] as $error) {
                $this->line("  - {$error}");
            }
        }

        $this->newLine();
        $this->info('Done!');

        return Command::SUCCESS;
    }
}
