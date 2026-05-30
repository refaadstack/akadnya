<?php

namespace App\Filament\Resources\Templates\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Artisan;

class TemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail_url')
                    ->label('Thumbnail')
                    ->square()
                    ->defaultImageUrl(url('/images/placeholder-template.png')),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('slug')
                    ->searchable()
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('version')
                    ->badge()
                    ->color('info'),

                TextColumn::make('price')
                    ->money('IDR')
                    ->sortable(),

                IconColumn::make('is_free')
                    ->label('Free')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('synced_at')
                    ->label('Last Synced')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('invitations_count')
                    ->label('Usage')
                    ->counts('invitations')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                Action::make('sync')
                    ->label('Sync Templates')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->action(function () {
                        Artisan::call('templates:sync');

                        return redirect()->back();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Sync Templates')
                    ->modalDescription('This will scan the storage/app/public/templates directory and sync all templates to the database.')
                    ->modalSubmitActionLabel('Sync Now'),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
