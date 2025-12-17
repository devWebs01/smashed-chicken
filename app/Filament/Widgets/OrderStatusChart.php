<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrderStatusChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    public function getHeading(): string
    {
        return 'Distribusi Status Pesanan';
    }

    protected function getData(): array
    {
        $statusCounts = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $statusLabels = [
            'pending' => 'Menunggu',
            'processing' => 'Diproses',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            'confirm' => 'Dikonfirmasi',
            'draft' => 'Draft',
        ];

        $labels = [];
        $data = [];
        $colors = [];

        $statusColors = [
            'pending' => ['rgba(255, 193, 7, 0.8)', 'rgb(255, 193, 7)'],
            'processing' => ['rgba(0, 123, 255, 0.8)', 'rgb(0, 123, 255)'],
            'completed' => ['rgba(40, 167, 69, 0.8)', 'rgb(40, 167, 69)'],
            'cancelled' => ['rgba(220, 53, 69, 0.8)', 'rgb(220, 53, 69)'],
            'confirm' => ['rgba(23, 162, 184, 0.8)', 'rgb(23, 162, 184)'],
            'draft' => ['rgba(108, 117, 125, 0.8)', 'rgb(108, 117, 125)'],
        ];

        foreach ($statusCounts as $status => $count) {
            $labels[] = $statusLabels[$status] ?? ucfirst($status);
            $data[] = $count;
            $colors[] = $statusColors[$status][0] ?? 'rgba(108, 117, 125, 0.8)';
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => array_map(function ($color) {
                        return str_replace('0.8)', '1)', $color);
                    }, $colors),
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
                    'position' => 'bottom',
                    'labels' => [
                        'padding' => 20,
                        'usePointStyle' => true,
                    ],
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { return context.label + ": " + context.parsed + " pesanan"; }',
                    ],
                ],
            ],
            'maintainAspectRatio' => false,
            'responsive' => true,
        ];
    }
}
