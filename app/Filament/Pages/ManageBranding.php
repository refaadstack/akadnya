<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class ManageBranding extends Page
{
    protected string $view = 'filament.pages.manage-branding';

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = 'Branding';

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 1;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'qr_logo' => SiteSetting::get('qr_logo_url'),
            'support_email' => SiteSetting::get('support_email', 'support@refaadstack.com'),
            'support_whatsapp' => SiteSetting::get('support_whatsapp', '6282374338273'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    TextInput::make('support_email')
                        ->label('Email Support')
                        ->email()
                        ->required()
                        ->helperText('Ditampilkan di footer, halaman FAQ, dan halaman legal sebagai alamat email support.'),
                    TextInput::make('support_whatsapp')
                        ->label('Nomor WhatsApp Support')
                        ->numeric()
                        ->minLength(8)
                        ->maxLength(15)
                        ->required()
                        ->helperText('Format internasional tanpa tanda + (contoh: 6281234567890). Dipakai untuk tautan wa.me di footer dan halaman FAQ.'),
                    FileUpload::make('qr_logo')
                        ->label('Logo QR Tamu')
                        ->disk('public')
                        ->directory('branding')
                        ->visibility('public')
                        ->acceptedFileTypes([
                            'image/svg+xml',
                            'image/png',
                            'image/jpeg',
                            'image/webp',
                            'image/gif',
                        ])
                        ->maxSize(1024)
                        ->helperText('Ditampilkan 56×56 px di tengah QR tamu (area putih). Gunakan logo bujursangkar dengan padding di sekelilingnya agar QR tetap mudah dipindai. Kosongkan untuk kembali ke logo bawaan.'),
                ])
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Simpan')
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        SiteSetting::set('support_email', $data['support_email']);
        SiteSetting::set('support_whatsapp', $data['support_whatsapp']);

        $logo = $data['qr_logo'] ?? null;

        if (is_array($logo)) {
            $logo = $logo[0] ?? null;
        }

        if (filled($logo)) {
            SiteSetting::set('qr_logo_url', url(Storage::disk('public')->url($logo)));
        } else {
            SiteSetting::remove('qr_logo_url');
        }

        Notification::make()
            ->success()
            ->title('Logo disimpan')
            ->send();
    }
}
