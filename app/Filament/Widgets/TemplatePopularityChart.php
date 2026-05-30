<?php

namespace App\Filament\Widgets;

use App\Models\Template;
use Filament\Widgets\ChartWidget;

class TemplatePopularityChart extends ChartWidget
{
    protected ?string $heading = 'Template Popularity';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $templates = Template::withCount('invitations')
            ->where('is_active', true)
            ->orderBy('invitations_count', 'desc')
            ->get();

        $labels = [];
        $data = [];
        $colors = [
            'rgba(59, 130, 246, 0.8)',
            'rgba(16, 185, 129, 0.8)',
            'rgba(245, 158, 11, 0.8)',
            'rgba(239, 68, 68, 0.8)',
            'rgba(139, 92, 246, 0.8)',
            'rgba(236, 72, 153, 0.8)',
        ];

        foreach ($templates as $index => $template) {
            $labels[] = $template->name;
            $data[] = $template->invitations_count;
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
