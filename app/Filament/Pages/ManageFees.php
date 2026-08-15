<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageFees extends Page
{
    protected string $view = 'filament.pages.manage-fees';

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Biaya';

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 2;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $config = config('fees');

        $this->form->fill([
            'tax_enabled' => filter_var(SiteSetting::get('tax_enabled', $config['tax']['enabled']), FILTER_VALIDATE_BOOLEAN),
            'tax_rate' => SiteSetting::get('tax_rate', $config['tax']['rate']),
            'payment_gateway_fee_percentage' => SiteSetting::get('payment_gateway_fee_percentage', $config['payment_gateway']['percentage']),
            'payment_gateway_fee_flat' => SiteSetting::get('payment_gateway_fee_flat', $config['payment_gateway']['flat']),
            'payment_gateway_fee_default_rule' => SiteSetting::get('payment_gateway_fee_default_rule', $config['payment_gateway']['default_rule']),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Toggle::make('tax_enabled')
                        ->label('Aktifkan Pajak (PPN)')
                        ->helperText('Matikan jika bisnis belum wajib membayar pajak (masih di bawah ambang PKP).'),
                    TextInput::make('tax_rate')
                        ->label('Tarif Pajak')
                        ->numeric()
                        ->step(0.01)
                        ->suffix('%')
                        ->helperText('Dikenakan atas subtotal ditambah biaya payment gateway, hanya jika pajak aktif.'),
                    TextInput::make('payment_gateway_fee_percentage')
                        ->label('Fee Payment Gateway (Persentase)')
                        ->numeric()
                        ->step(0.01)
                        ->suffix('%')
                        ->helperText('Digunakan untuk metode pembayaran dengan biaya persentase (mis. e-wallet).'),
                    TextInput::make('payment_gateway_fee_flat')
                        ->label('Fee Payment Gateway (Flat)')
                        ->numeric()
                        ->prefix('Rp')
                        ->helperText('Biaya tetap per transaksi untuk metode dengan biaya flat (mis. BCA Virtual Account).'),
                    Select::make('payment_gateway_fee_default_rule')
                        ->label('Aturan Fee Default')
                        ->options([
                            'percentage' => 'Persentase',
                            'flat' => 'Flat',
                        ])
                        ->helperText('Aturan yang dipakai saat metode pembayaran belum diketahui (halaman checkout).'),
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

        SiteSetting::set('tax_enabled', $data['tax_enabled'] ? '1' : '0');
        SiteSetting::set('tax_rate', $data['tax_rate']);
        SiteSetting::set('payment_gateway_fee_percentage', $data['payment_gateway_fee_percentage']);
        SiteSetting::set('payment_gateway_fee_flat', $data['payment_gateway_fee_flat']);
        SiteSetting::set('payment_gateway_fee_default_rule', $data['payment_gateway_fee_default_rule']);

        Notification::make()
            ->success()
            ->title('Biaya disimpan')
            ->send();
    }
}