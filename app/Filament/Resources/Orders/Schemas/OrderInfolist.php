<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Section::make('Order Information')
                            ->columnSpan(2)
                            ->schema([
                                Grid::make(2)->schema([
                                    TextEntry::make('order_number')
                                        ->label('Order Number')
                                        ->weight('bold')
                                        ->copyable(),

                                    TextEntry::make('status')
                                        ->label('Status')
                                        ->badge()
                                        ->color(fn (string $state): string => match ($state) {
                                            'pending' => 'warning',
                                            'paid' => 'success',
                                            'failed' => 'danger',
                                            'expired' => 'gray',
                                            default => 'info',
                                        }),

                                    TextEntry::make('user.name')
                                        ->label('Customer'),

                                    TextEntry::make('user.email')
                                        ->label('Email')
                                        ->copyable(),

                                    TextEntry::make('created_at')
                                        ->label('Order Date')
                                        ->dateTime('d M Y H:i'),

                                    TextEntry::make('paid_at')
                                        ->label('Paid At')
                                        ->dateTime('d M Y H:i')
                                        ->placeholder('Not paid yet'),
                                ]),
                            ]),

                        Section::make('Payment Summary')
                            ->columnSpan(1)
                            ->schema([
                                TextEntry::make('total_amount')
                                    ->label('Total Amount')
                                    ->money('IDR')
                                    ->size('lg')
                                    ->weight('bold')
                                    ->color('success'),

                                TextEntry::make('payment.provider')
                                    ->label('Payment Provider')
                                    ->badge()
                                    ->placeholder('No payment'),

                                TextEntry::make('payment.status')
                                    ->label('Payment Status')
                                    ->badge()
                                    ->color(fn (?string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'paid' => 'success',
                                        'failed' => 'danger',
                                        default => 'gray',
                                    })
                                    ->placeholder('No payment'),

                                TextEntry::make('payment.snap_token')
                                    ->label('Snap Token')
                                    ->copyable()
                                    ->placeholder('No token')
                                    ->limit(30),
                            ]),
                    ]),

                Section::make('Metadata')
                    ->collapsed()
                    ->schema([
                        TextEntry::make('metadata')
                            ->label('Order Metadata')
                            ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT) : 'None')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
