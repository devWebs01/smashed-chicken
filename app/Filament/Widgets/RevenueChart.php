<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string
    {
        return 'Pendapatan 7 Hari Terakhir';
    }

    protected function getData(): array
    {
        $data = collect();

        // Get data for last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $endDate = $date->copy()->endOfDay();

            $revenue = Order::whereBetween('created_at', [$date, $endDate])
                ->sum('total_price');

            $data->push([
                'date' => $date->format('M d'),
                'revenue' => $revenue / 1000, // Convert to thousands for better chart scaling
                'orders' => Order::whereBetween('created_at', [$date, $endDate])->count(),
            ]);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pendapatan (Ribu Rupiah)',
                    'data' => $data->pluck('revenue')->toArray(),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Jumlah Pesanan',
                    'data' => $data->pluck('orders')->toArray(),
                    'borderColor' => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'yAxisID' => 'orders',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $data->pluck('date')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return "Rp " + (value * 1000).toLocaleString("id-ID"); }',
                    ],
                ],
                'orders' => [
                    'beginAtZero' => true,
                    'position' => 'right',
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
        ];
    }
}
