<?php

namespace App\Filament\Resources\Templates\Pages;

use App\Filament\Resources\Templates\TemplateResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\File;

class EditTemplate extends EditRecord
{
    protected static string $resource = TemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function (DeleteAction $action) {
                    $template = $this->record;

                    // Check if template is used by any published invitations
                    $publishedCount = $template->invitations()->published()->count();

                    if ($publishedCount > 0) {
                        Notification::make()
                            ->title('Cannot Delete Template')
                            ->body("Template tidak dapat dihapus karena masih digunakan oleh {$publishedCount} undangan aktif.")
                            ->danger()
                            ->send();

                        $action->cancel();
                    }
                })
                ->after(function () {
                    $template = $this->record;

                    // Delete template directory from storage/app/public/templates/{slug}/
                    $folderPath = $template->getFolderPath();

                    if (File::exists($folderPath)) {
                        File::deleteDirectory($folderPath);
                    }
                }),
        ];
    }
}
