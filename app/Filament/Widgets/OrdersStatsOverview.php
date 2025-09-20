<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrdersStatsOverview extends BaseWidget
{
    protected function getCards(): array
    {
        $today = now()->startOfDay();
        $yesterday = now()->subDay()->startOfDay();

        $todayCount = Order::whereDate('created_at', $today)->count();
        $yesterdayCount = Order::whereDate('created_at', $yesterday)->count();
        $diff = $todayCount - $yesterdayCount;
        $trend = $yesterdayCount > 0 ? round(($diff / $yesterdayCount) * 100, 1) : ($todayCount > 0 ? 100 : 0);

        return [
            Stat::make('Pesanan Hari Ini', $todayCount)
                ->description($diff >= 0 ? "+$diff" : "$diff")
                ->descriptionIcon($diff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($diff >= 0 ? 'success' : 'danger')
                ->columnSpan(2),
            Stat::make('Kenaikan (%)', $trend.'%')
                ->description('Dibanding kemarin'),
        ];
    }
}
