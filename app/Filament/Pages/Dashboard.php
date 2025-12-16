<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\OrdersPerDayChart;
use App\Filament\Widgets\OrdersStatsOverview;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static array $widgets = [
        OrdersStatsOverview::class,
        OrdersPerDayChart::class,
    ];

    public function getColumns(): int|array
    {
        return 2;
    }
}
