<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\BarChartWidget;
use Illuminate\Support\Carbon;

class OrdersPerDayChart extends BarChartWidget
{
    protected ?string $heading = 'Pesanan per Hari';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $days = collect(range(0, 6))->map(function ($i) {
            return now()->subDays(6 - $i)->format('Y-m-d');
        });

        $orders = Order::query()
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->get()
            ->groupBy(fn ($order) => $order->created_at->format('Y-m-d'));

        $counts = $days->map(fn ($day) => isset($orders[$day]) ? $orders[$day]->count() : 0);

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Pesanan',
                    'data' => $counts->toArray(),
                    'backgroundColor' => '#3b82f6',
                ],
            ],
            'labels' => $days->map(fn ($d) => Carbon::parse($d)->translatedFormat('D, d M'))->toArray(),
        ];
    }
}
