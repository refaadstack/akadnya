<?php

namespace App\Filament\Pages;

use App\Models\UserFeature;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class ManagedSetups extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.managed-setups';

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static ?string $navigationLabel = 'Managed Setup';

    protected static string|UnitEnum|null $navigationGroup = 'Layanan';

    protected static ?int $navigationSort = 1;

    /**
     * @return array<string, string>
     */
    public static function setupStatusOptions(): array
    {
        return [
            'pending' => 'Menunggu setup',
            'in_progress' => 'Sedang dikerjakan',
            'waiting_user' => 'Menunggu info dari user',
            'done' => 'Selesai',
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                UserFeature::query()
                    ->with('user')
                    ->where('feature', 'managed_setup')
                    ->latest('activated_at')
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('metadata.order_number')
                    ->label('Order #')
                    ->getStateUsing(fn (UserFeature $record): string => (string) ($record->metadata['order_number'] ?? '-')),

                TextColumn::make('setup_status')
                    ->label('Status Setup')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::setupStatusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'in_progress' => 'info',
                        'waiting_user' => 'primary',
                        'done' => 'success',
                        default => 'warning',
                    })
                    ->sortable(),

                TextColumn::make('setup_notes')
                    ->label('Catatan')
                    ->limit(45)
                    ->tooltip(fn (UserFeature $record): string => (string) ($record->setup_notes ?? ''))
                    ->toggleable(),

                TextColumn::make('setup_updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('activated_at')
                    ->label('Diaktifkan')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('setup_status')
                    ->label('Status Setup')
                    ->options(self::setupStatusOptions()),
            ])
            ->recordActions([
                Action::make('manageSetup')
                    ->label('Kelola Setup')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->modalHeading('Kelola Setup')
                    ->modalSubmitActionLabel('Simpan')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options(self::setupStatusOptions())
                            ->required()
                            ->native(false),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(4)
                            ->helperText('Catatan untuk user tentang progres setup (opsional).'),
                    ])
                    ->fillForm(fn (UserFeature $record): array => [
                        'status' => $record->setup_status ?? 'pending',
                        'notes' => $record->setup_notes ?? '',
                    ])
                    ->action(function (UserFeature $record, array $data): void {
                        $record->update([
                            'setup_status' => $data['status'],
                            'setup_notes' => $data['notes'] ?? null,
                            'setup_updated_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Status setup diperbarui')
                            ->body(self::setupStatusOptions()[$data['status']] ?? $data['status'])
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('activated_at', 'desc');
    }
}
