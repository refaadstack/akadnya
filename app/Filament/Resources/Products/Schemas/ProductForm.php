<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->options([
                        'base_package' => 'Base Package',
                        'addon' => 'Add-on',
                    ])
                    ->required()
                    ->native(false)
                    ->columnSpan(1),

                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(100)
                    ->columnSpan(1),

                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),

                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(0)
                    ->step(1000)
                    ->columnSpan(1),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->columnSpan(1),

                Toggle::make('is_recurring')
                    ->label('Recurring')
                    ->default(false)
                    ->reactive()
                    ->columnSpan(1),

                TextInput::make('recurring_interval')
                    ->label('Recurring Interval')
                    ->placeholder('e.g., monthly, yearly')
                    ->maxLength(50)
                    ->visible(fn ($get) => $get('is_recurring'))
                    ->columnSpan(1),
            ])
            ->columns(2);
    }
}
