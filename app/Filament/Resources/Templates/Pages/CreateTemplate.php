<?php

namespace App\Filament\Resources\Templates\Pages;

use App\Filament\Resources\Templates\TemplateResource;
use App\Services\TemplateService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateTemplate extends CreateRecord
{
    protected static string $resource = TemplateResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // If template_zip is provided, process it
        if (isset($data['template_zip'])) {
            $zipPath = Storage::disk('local')->path($data['template_zip']);

            /** @var TemplateService $templateService */
            $templateService = app(TemplateService::class);

            $result = $templateService->processUpload($zipPath);

            // Clean up uploaded ZIP file
            Storage::disk('local')->delete($data['template_zip']);

            if (! $result['success']) {
                // Show detailed error notification
                $this->showValidationErrors($result['message']);

                // Halt the creation process
                $this->halt();
            }

            // If successful, use the template data from the result
            if (isset($result['template'])) {
                $template = $result['template'];

                return [
                    'slug' => $template->slug,
                    'name' => $template->name,
                    'version' => $template->version,
                    'thumbnail_url' => $template->thumbnail_url,
                    'price' => $template->price,
                    'is_free' => $template->is_free,
                    'is_active' => $template->is_active,
                    'synced_at' => $template->synced_at,
                ];
            }
        }

        return $data;
    }

    protected function showValidationErrors(string $message): void
    {
        // Parse error message to show structured errors
        $errors = $this->parseErrorMessage($message);

        if (count($errors) > 1) {
            // Multiple errors - show as list
            $body = '<div class="space-y-2">';
            $body .= '<p class="font-semibold text-red-600">Template validation failed:</p>';
            $body .= '<ul class="list-disc list-inside space-y-1 text-sm">';
            foreach ($errors as $error) {
                $body .= '<li>'.e($error).'</li>';
            }
            $body .= '</ul>';
            $body .= '<p class="mt-3 text-sm text-gray-600">Please fix these issues and try again.</p>';
            $body .= '</div>';

            Notification::make()
                ->title('Template Upload Failed')
                ->body($body)
                ->danger()
                ->persistent()
                ->send();
        } else {
            // Single error - show simple message
            Notification::make()
                ->title('Template Upload Failed')
                ->body($message)
                ->danger()
                ->persistent()
                ->send();
        }
    }

    protected function parseErrorMessage(string $message): array
    {
        // Try to extract multiple errors from message
        if (str_contains($message, 'Template validation failed:')) {
            $errorPart = str_replace('Template validation failed: ', '', $message);
            $errors = array_map('trim', explode(',', $errorPart));

            return array_filter($errors);
        }

        return [$message];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Template uploaded successfully';
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title($this->getCreatedNotificationTitle())
            ->body('The template has been processed and is now available.')
            ->send();
    }
}
