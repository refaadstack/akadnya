<?php

namespace App\Filament\Resources\Templates\Pages;

use App\Filament\Resources\Templates\TemplateResource;
use App\Models\Template;
use App\Services\TemplateService;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class EditTemplate extends EditRecord
{
    protected static string $resource = TemplateResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $template = $this->record;

        if (isset($data['name']) && $data['name'] !== $template->name) {
            $newSlug = Str::slug($data['name']);

            if ($newSlug === '') {
                Notification::make()
                    ->title('Nama Template Tidak Valid')
                    ->body('Nama template tidak dapat diubah menjadi slug yang valid.')
                    ->danger()
                    ->send();

                $this->halt();
            }

            if (Template::where('slug', $newSlug)->whereKeyNot($template->getKey())->exists()) {
                Notification::make()
                    ->title('Slug Sudah Digunakan')
                    ->body("Template dengan slug \"{$newSlug}\" sudah ada. Gunakan nama lain.")
                    ->danger()
                    ->send();

                $this->halt();
            }

            $data['slug'] = $newSlug;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $template = $this->record;
        $service = app(TemplateService::class);

        if ($template->wasChanged('slug')) {
            $service->renameTemplateFolder($template->getOriginal('slug'), $template->slug);
        }

        if ($template->wasChanged('name')) {
            $service->updateTemplateName($template, $template->name);
        }
    }

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
