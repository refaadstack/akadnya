<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Carbon\CarbonInterface;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UsersChart extends ChartWidget
{
    protected ?string $heading = 'User Growth';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '30s';

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
                    'backgroundColor' => 'rgba(173, 127, 53, 0.2)',
                    'borderColor' => 'rgb(173, 127, 53)',
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

    /**
     * @return array{labels: list<string>, users: list<int>}
     */
    private function getUserData(): array
    {
        $now = now();

        [$days, $startDate] = match ($this->filter) {
            'last7days' => [7, $now->copy()->subDays(7)],
            'last90days' => [90, $now->copy()->subDays(90)],
            'thisYear' => [$now->dayOfYear, $now->copy()->startOfYear()],
            default => [30, $now->copy()->subDays(30)],
        };

        $signupsPerDay = Cache::remember(
            "admin.dashboard.users.{$this->filter}",
            now()->addSeconds(60),
            fn (): array => $this->fetchSignupsPerDay($startDate),
        );

        $labels = [];
        $userCounts = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::parse($date)->format('M d');
            $userCounts[] = (int) ($signupsPerDay[$date] ?? 0);
        }

        return [
            'labels' => $labels,
            'users' => $userCounts,
        ];
    }

    /**
     * @return array<string, int>
     */
    private function fetchSignupsPerDay(CarbonInterface $startDate): array
    {
        return User::query()
            ->where('role', 'user')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) AS signup_date, COUNT(*) AS users')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('users', 'signup_date')
            ->all();
    }
}
