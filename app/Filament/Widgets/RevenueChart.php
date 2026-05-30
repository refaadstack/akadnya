<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChart extends ChartWidget
{
    protected ?string $heading = 'Revenue Overview';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public ?string $filter = 'last30days';

    protected function getData(): array
    {
        $data = $this->getRevenueData();

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (Rp)',
                    'data' => $data['revenue'],
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                ],
                [
                    'label' => 'Orders',
                    'data' => $data['orders'],
                    'backgroundColor' => 'rgba(16, 185, 129, 0.5)',
                    'borderColor' => 'rgb(16, 185, 129)',
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

    private function getRevenueData(): array
    {
        $filter = $this->filter;
        $now = now();

        $query = Order::where('status', 'paid');

        switch ($filter) {
            case 'last7days':
                $days = 7;
                $query->where('paid_at', '>=', $now->copy()->subDays($days));
                break;
            case 'last30days':
                $days = 30;
                $query->where('paid_at', '>=', $now->copy()->subDays($days));
                break;
            case 'last90days':
                $days = 90;
                $query->where('paid_at', '>=', $now->copy()->subDays($days));
                break;
            case 'thisYear':
                $query->whereYear('paid_at', $now->year);
                $days = $now->dayOfYear;
                break;
            default:
                $days = 30;
                $query->where('paid_at', '>=', $now->copy()->subDays($days));
        }

        $orders = $query->get();

        // Group by date
        $groupedData = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i)->format('Y-m-d');
            $groupedData[$date] = [
                'revenue' => 0,
                'orders' => 0,
            ];
        }

        foreach ($orders as $order) {
            $date = Carbon::parse($order->paid_at)->format('Y-m-d');
            if (isset($groupedData[$date])) {
                $groupedData[$date]['revenue'] += $order->total_amount;
                $groupedData[$date]['orders'] += 1;
            }
        }

        $labels = [];
        $revenue = [];
        $orderCounts = [];

        foreach ($groupedData as $date => $data) {
            $labels[] = Carbon::parse($date)->format('M d');
            $revenue[] = $data['revenue'];
            $orderCounts[] = $data['orders'];
        }

        return [
            'labels' => $labels,
            'revenue' => $revenue,
            'orders' => $orderCounts,
        ];
    }
}
