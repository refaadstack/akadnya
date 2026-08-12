<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Models\Product;
use App\Models\Template;
use App\Models\UserGrant;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserGrantsRelationManager extends RelationManager
{
    protected static string $relationship = 'grants';

    protected static ?string $title = 'Akses Gratis';

    protected static ?string $modelLabel = 'Akses';

    protected static ?string $pluralModelLabel = 'Akses Gratis';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('grant_type')
                    ->label('Jenis')
                    ->options([
                        UserGrant::TYPE_TEMPLATE => 'Template',
                        UserGrant::TYPE_ADDON => 'Addon (fitur tambahan)',
                    ])
                    ->default(UserGrant::TYPE_TEMPLATE)
                    ->live()
                    ->required()
                    ->native(false),

                Select::make('item_slug')
                    ->label('Item')
                    ->options(fn (Get $get): array => $get('grant_type') === UserGrant::TYPE_ADDON
                        ? Product::query()->addons()->active()->orderBy('name')->pluck('name', 'slug')->all()
                        : Template::query()->active()->orderBy('name')->pluck('name', 'slug')->all())
                    ->placeholder(fn (Get $get): string => $get('grant_type') === UserGrant::TYPE_ADDON
                        ? 'Semua addon'
                        : 'Semua template')
                    ->helperText('Kosongkan untuk memberikan akses ke semua item jenis ini.')
                    ->searchable()
                    ->preload()
                    ->native(false),

                DateTimePicker::make('expires_at')
                    ->label('Berlaku Sampai')
                    ->helperText('Kosongkan untuk akses permanen.')
                    ->seconds(false)
                    ->native(false),

                TextInput::make('notes')
                    ->label('Catatan')
                    ->maxLength(255)
                    ->placeholder('Contoh: sponsor influencer, owner, dst.'),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('item_slug')
            ->columns([
                TextColumn::make('grant_type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        UserGrant::TYPE_TEMPLATE => 'warning',
                        UserGrant::TYPE_ADDON => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        UserGrant::TYPE_TEMPLATE => 'Template',
                        UserGrant::TYPE_ADDON => 'Addon',
                        default => $state,
                    }),

                TextColumn::make('item_slug')
                    ->label('Item')
                    ->formatStateUsing(fn (?string $state): string => $state ?? 'Semua')
                    ->weight('medium'),

                TextColumn::make('expires_at')
                    ->label('Berlaku Sampai')
                    ->dateTime('d M Y H:i')
                    ->badge()
                    ->color(fn (UserGrant $record): string => $record->isActive() ? 'success' : 'danger')
                    ->formatStateUsing(fn (UserGrant $record, ?string $state): string => $state === null ? 'Permanen' : $state->format('d M Y')),

                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(40)
                    ->placeholder('-'),

                TextColumn::make('grantedBy.name')
                    ->label('Diberi Oleh')
                    ->placeholder('-'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i'),
            ])
            ->filters([])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Akses')
                    ->modalHeading('Tambah Akses Gratis')
                    ->modalSubmitActionLabel('Berikan Akses')
                    ->modalWidth('2xl')
                    ->createAnother(false),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->modalHeading('Hapus Akses?')
                    ->modalSubmitActionLabel('Hapus Akses')
                    ->modalCancelActionLabel('Batal'),
            ])
            ->toolbarActions([]);
    }
}
