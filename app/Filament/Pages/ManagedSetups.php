<?php

namespace App\Filament\Pages;

use App\Models\UserFeature;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
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
                    ->searchable(),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('metadata.order_number')
                    ->label('Order #')
                    ->getStateUsing(fn (UserFeature $record): string => (string) ($record->metadata['order_number'] ?? '-')),

                TextColumn::make('activated_at')
                    ->label('Diaktifkan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('metadata.order_id')
                    ->label('Status')
                    ->badge()
                    ->color('warning')
                    ->getStateUsing(fn (UserFeature $record): string => $record->isActive() ? 'Menunggu setup' : 'Berakhir'),
            ])
            ->defaultSort('activated_at', 'desc');
    }
}