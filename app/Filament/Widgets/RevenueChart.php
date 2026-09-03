<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\CarbonInterface;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RevenueChart extends ChartWidget
{
    protected ?string $heading = 'Revenue Overview';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '30s';

    public ?string $filter = 'last30days';

    protected function getData(): array
    {
        $data = $this->getRevenueData();

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (Rp)',
                    'data' => $data['revenue'],
                    'backgroundColor' => 'rgba(173, 127, 53, 0.5)',
                    'borderColor' => 'rgb(173, 127, 53)',
                ],
                [
                    'label' => 'Orders',
                    'data' => $data['orders'],
                    'backgroundColor' => 'rgba(128, 0, 32, 0.5)',
                    'borderColor' => 'rgb(128, 0, 32)',
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getFilters(): ?array
    {
        return [
            'last7days' => 'Last 7 days',
            'last30days' => 'Last 30 days',
            'last90days' => 'Last 90 days',
            'thisYear' => 'This year',
        ];
    }

    /**
     * @return array{labels: list<string>, revenue: list<float|int>, orders: list<int>}
     */
    private function getRevenueData(): array
    {
        $now = now();

        [$days, $startDate] = match ($this->filter) {
            'last7days' => [7, $now->copy()->subDays(7)],
            'last90days' => [90, $now->copy()->subDays(90)],
            'thisYear' => [$now->dayOfYear, $now->copy()->startOfYear()],
            default => [30, $now->copy()->subDays(30)],
        };

        $dailyTotals = Cache::remember(
            "admin.dashboard.revenue.{$this->filter}",
            now()->addSeconds(60),
            fn (): array => $this->fetchDailyTotals($startDate),
        );

        $labels = [];
        $revenue = [];
        $orderCounts = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::parse($date)->format('M d');
            $revenue[] = (float) ($dailyTotals[$date]['revenue'] ?? 0);
            $orderCounts[] = (int) ($dailyTotals[$date]['orders'] ?? 0);
        }

        return [
            'labels' => $labels,
            'revenue' => $revenue,
            'orders' => $orderCounts,
        ];
    }

    /**
     * @return array<string, array{revenue: float, orders: int}>
     */
    private function fetchDailyTotals(CarbonInterface $startDate): array
    {
        return Order::query()
            ->where('status', 'paid')
            ->where('paid_at', '>=', $startDate)
            ->selectRaw('DATE(paid_at) AS sale_date, SUM(total_amount) AS revenue, COUNT(*) AS orders')
            ->groupBy(DB::raw('DATE(paid_at)'))
            ->get()
            ->mapWithKeys(fn (Order $order): array => [
                (string) $order->sale_date => [
                    'revenue' => (float) $order->revenue,
                    'orders' => (int) $order->orders,
                ],
            ])
            ->all();
    }
}
