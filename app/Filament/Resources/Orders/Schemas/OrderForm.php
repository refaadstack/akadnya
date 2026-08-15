<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('order_number')
                    ->required(),
                Select::make('status')
                    ->options(['pending' => 'Pending', 'paid' => 'Paid', 'failed' => 'Failed', 'expired' => 'Expired'])
                    ->default('pending')
                    ->required(),
                TextInput::make('subtotal_amount')
                    ->numeric()
                    ->default(0),
                TextInput::make('payment_gateway_fee')
                    ->numeric()
                    ->default(0),
                TextInput::make('tax_amount')
                    ->numeric()
                    ->default(0),
                TextInput::make('total_amount')
                    ->required()
                    ->numeric(),
                TextInput::make('metadata'),
                DateTimePicker::make('paid_at'),
            ]);
    }
}
