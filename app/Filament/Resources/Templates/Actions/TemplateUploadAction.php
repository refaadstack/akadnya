<?php

namespace App\Filament\Resources\Templates\Actions;

use App\Services\TemplateService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class TemplateUploadAction
{
    public static function make(): Action
    {
        return Action::make('uploadTemplate')
            ->label('Upload Template')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('primary')
            ->form([
                Section::make('Upload Rules')
                    ->description('Read the rules below before uploading a template')
                    ->schema([
                        Text::make(view('filament.components.template-upload-instructions')->render()),
                    ]),

                FileUpload::make('template_file')
                    ->label('Template File (ZIP or HTML)')
                    ->acceptedFileTypes(['application/zip', 'application/x-zip-compressed', 'text/html', 'application/xhtml+xml'])
                    ->maxSize(51200) // 50MB
                    ->required()
                    ->helperText('Upload a ZIP file (multi-section template) or a single HTML file (full-page template). Maximum file size: 50MB.')
                    ->disk('local')
                    ->directory('temp-uploads')
                    ->visibility('private')
                    ->columnSpanFull(),
            ])
            ->action(function (array $data, Action $action) {
                $templateService = app(TemplateService::class);

                // Get the uploaded file path
                $relativePath = $data['template_file'];
                $fullPath = Storage::disk('local')->path($relativePath);

                // Debug logging
                \Log::info('Template Upload Debug', [
                    'relative_path' => $relativePath,
                    'full_path' => $fullPath,
                    'file_exists' => file_exists($fullPath),
                    'disk_exists' => Storage::disk('local')->exists($relativePath),
                ]);

                // Check if file exists before processing
                if (!file_exists($fullPath)) {
                    Notification::make()
                        ->title('❌ Upload Failed')
                        ->body('Uploaded file not found. Please try again.')
                        ->danger()
                        ->send();
                    
                    $action->halt();
                    return;
                }

                // Process the upload
                $result = $templateService->processUpload($fullPath);

                // Clean up the uploaded file
                Storage::disk('local')->delete($relativePath);

                if ($result['success']) {
                    Notification::make()
                        ->title('✅ Template Uploaded Successfully')
                        ->body($result['message'])
                        ->success()
                        ->duration(5000)
                        ->send();

                    // Redirect to edit page if template was created
                    if (isset($result['template'])) {
                        return redirect()->route('filament.admin.resources.templates.edit', ['record' => $result['template']->id]);
                    }
                } else {
                    // Parse and display detailed errors
                    self::showDetailedErrors($result['message']);

                    // Prevent modal from closing
                    $action->halt();
                }
            })
            ->modalHeading('Upload Template')
            ->modalDescription('Upload a ZIP file or a single HTML file. The system will validate the structure and register the template.')
            ->modalSubmitActionLabel('Upload & Process')
            ->modalWidth('3xl')
            ->closeModalByClickingAway(false);
    }

    protected static function showDetailedErrors(string $message): void
    {
        // Check if message contains validation errors
        if (str_contains($message, 'Template validation failed:')) {
            $errorPart = str_replace('Template validation failed: ', '', $message);
            $errors = array_map('trim', explode(',', $errorPart));
            $errors = array_filter($errors);

            $body = self::formatErrorList($errors);

            Notification::make()
                ->title('❌ Template Validation Failed')
                ->body($body)
                ->danger()
                ->persistent()
                ->send();
        } else {
            // Single error or unexpected error
            Notification::make()
                ->title('❌ Upload Failed')
                ->body($message)
                ->danger()
                ->persistent()
                ->send();
        }
    }

    protected static function formatErrorList(array $errors): string
    {
        $html = '<div class="space-y-3">';
        $html .= '<p class="font-medium text-red-700">The following issues were found:</p>';
        $html .= '<ul class="list-none space-y-2">';

        foreach ($errors as $error) {
            $icon = self::getErrorIcon($error);
            $html .= '<li class="flex items-start gap-2">';
            $html .= '<span class="text-red-500 mt-0.5">'.$icon.'</span>';
            $html .= '<span class="text-sm text-gray-700">'.e($error).'</span>';
            $html .= '</li>';
        }

        $html .= '</ul>';
        $html .= '<div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">';
        $html .= '<p class="text-sm text-blue-800"><strong>💡 Tip:</strong> Check the documentation for the correct ZIP structure and required files.</p>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    protected static function getErrorIcon(string $error): string
    {
        if (str_contains($error, 'not found') || str_contains($error, 'missing')) {
            return '📁'; // Missing file
        }

        if (str_contains($error, 'invalid') || str_contains($error, 'JSON')) {
            return '⚠️'; // Invalid format
        }

        if (str_contains($error, 'unsafe') || str_contains($error, 'security')) {
            return '🔒'; // Security issue
        }

        return '❌'; // Generic error
    }
}
