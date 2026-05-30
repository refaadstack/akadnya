<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class UsersChart extends ChartWidget
{
    protected ?string $heading = 'User Growth';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public ?string $filter = 'last30days';

    protected function getData(): array
    {
        $data = $this->getUserData();

        return [
            'datasets' => [
                [
                    'label' => 'New Users',
                    'data' => $data['users'],
                    'fill' => true,
                    'backgroundColor' => 'rgba(139, 92, 246, 0.2)',
                    'borderColor' => 'rgb(139, 92, 246)',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
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

    private function getUserData(): array
    {
        $filter = $this->filter;
        $now = now();

        $query = User::where('role', 'user');

        switch ($filter) {
            case 'last7days':
                $days = 7;
                $query->where('created_at', '>=', $now->copy()->subDays($days));
                break;
            case 'last30days':
                $days = 30;
                $query->where('created_at', '>=', $now->copy()->subDays($days));
                break;
            case 'last90days':
                $days = 90;
                $query->where('created_at', '>=', $now->copy()->subDays($days));
                break;
            case 'thisYear':
                $query->whereYear('created_at', $now->year);
                $days = $now->dayOfYear;
                break;
            default:
                $days = 30;
                $query->where('created_at', '>=', $now->copy()->subDays($days));
        }

        $users = $query->get();

        // Group by date
        $groupedData = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i)->format('Y-m-d');
            $groupedData[$date] = 0;
        }

        foreach ($users as $user) {
            $date = Carbon::parse($user->created_at)->format('Y-m-d');
            if (isset($groupedData[$date])) {
                $groupedData[$date] += 1;
            }
        }

        $labels = [];
        $userCounts = [];

        foreach ($groupedData as $date => $count) {
            $labels[] = Carbon::parse($date)->format('M d');
            $userCounts[] = $count;
        }

        return [
            'labels' => $labels,
            'users' => $userCounts,
        ];
    }
}
