<?php

namespace App\Filament\Resources\Templates\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class TemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Upload Template ZIP')
                    ->description('Upload a template ZIP file containing template.json, sections/, and optional template-owned assets/')
                    ->schema([
                        FileUpload::make('template_zip')
                            ->label('Template ZIP File')
                            ->acceptedFileTypes(['application/zip', 'application/x-zip-compressed'])
                            ->maxSize(51200) // 50MB
                            ->disk('local')
                            ->directory('temp-templates')
                            ->visibility('private')
                            ->required()
                            ->helperText('Upload a ZIP file with template.json, sections/, and optional assets/')
                            ->columnSpanFull()
                            ->hiddenOn('edit'),

                        Text::make(new HtmlString('
                            <div class="text-sm space-y-2">
                                <p class="font-semibold">Your ZIP must contain:</p>
                                <ul class="list-disc list-inside space-y-1 text-gray-600">
                                    <li><code>template.json</code> - Template metadata (name, slug, sections)</li>
                                    <li><code>sections/</code> - Folder with HTML section files</li>
                                    <li><code>template.json.defaults</code> - Preview fallback values owned by this template</li>
                                    <li><code>assets/</code> - Optional CSS, JavaScript, images, and fonts owned by this template</li>
                                    <li><code>template.json.assets</code> - Optional CSS/JS load order</li>
                                </ul>
                                <p class="mt-2 text-gray-500">See documentation for detailed structure.</p>
                            </div>
                        '))
                            ->columnSpanFull()
                            ->hiddenOn('edit'),
                    ])
                    ->collapsible()
                    ->hiddenOn('edit'),

                Section::make('Template Information')
                    ->description('Template details from template.json')
                    ->schema([
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(100)
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Otomatis mengikuti nama template')
                            ->columnSpan(1),

                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->dehydrated()
                            ->helperText('Editable — disimpan ke template.json agar tidak tertimpa saat sync')
                            ->columnSpan(1),

                        TextInput::make('version')
                            ->required()
                            ->default('1.0.0')
                            ->maxLength(20)
                            ->columnSpan(1),

                        FileUpload::make('thumbnail_url')
                            ->label('Thumbnail Image')
                            ->image()
                            ->disk('public')
                            ->directory('templates/thumbnails')
                            ->visibility('public')
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->maxSize(2048) // 2MB
                            ->helperText('Upload thumbnail image (max 2MB). Recommended: 800x600px')
                            ->columnSpanFull(),

                        TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp')
                            ->minValue(0)
                            ->step(1000)
                            ->columnSpan(1),

                        Toggle::make('is_free')
                            ->label('Free Template')
                            ->default(false)
                            ->columnSpan(1),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Only active templates are visible to users')
                            ->columnSpan(1),

                        DateTimePicker::make('synced_at')
                            ->label('Last Synced')
                            ->disabled()
                            ->columnSpan(1),
                    ])
                    ->columns(2),
            ]);
    }
}
