<?php

namespace App\Filament\Widgets;

use App\Models\Template;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

class TemplatePopularityChart extends ChartWidget
{
    protected ?string $heading = 'Template Popularity';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $templates = Cache::remember(
            'admin.dashboard.template_popularity',
            now()->addSeconds(60),
            fn (): array => Template::query()
                ->withCount('invitations')
                ->where('is_active', true)
                ->orderByDesc('invitations_count')
                ->get()
                ->map(fn (Template $template): array => [
                    'name' => $template->name,
                    'count' => $template->invitations_count,
                ])
                ->all(),
        );

        $labels = [];
        $data = [];
        $colors = [
            'rgba(173, 127, 53, 0.8)',
            'rgba(128, 0, 32, 0.8)',
            'rgba(181, 136, 62, 0.8)',
            'rgba(153, 10, 40, 0.8)',
            'rgba(200, 160, 80, 0.8)',
            'rgba(100, 0, 26, 0.8)',
        ];

        foreach ($templates as $template) {
            $labels[] = $template['name'];
            $data[] = $template['count'];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Invitations Created',
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                    'borderColor' => array_slice($colors, 0, count($data)),
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
