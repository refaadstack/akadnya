<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class BestSellerProducts extends TableWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(static::bestSellersQuery())
            ->columns([
                TextColumn::make('selling_rank')
                    ->label('#'),

                TextColumn::make('name')
                    ->label('Product')
                    ->weight('medium'),

                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'base_package' => 'Base Package',
                        'addon' => 'Addon',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'base_package' => 'info',
                        'addon' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('total_sold')
                    ->label('Qty Sold'),

                TextColumn::make('total_revenue')
                    ->label('Est. Revenue')
                    ->money('IDR'),
            ])
            ->heading('Best Seller Products')
            ->poll('30s')
            ->paginated(false);
    }

    /**
     * Top 5 products ranked by total quantity sold across all paid orders.
     *
     * @return Builder<Product>
     */
    public static function bestSellersQuery(): Builder
    {
        return Product::query()
            ->selectRaw('products.*, SUM(order_items.quantity) AS total_sold, SUM(order_items.price * order_items.quantity) AS total_revenue, ROW_NUMBER() OVER (ORDER BY SUM(order_items.quantity) DESC, SUM(order_items.price * order_items.quantity) DESC) AS selling_rank')
            ->join('order_items', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'paid')
            ->groupBy('products.id')
            ->orderBy('selling_rank')
            ->limit(5);
    }
}
