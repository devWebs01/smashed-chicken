<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrdersStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getCards(): array
    {
        $today = now()->startOfDay();
        $yesterday = now()->subDay()->startOfDay();
        $thisWeek = now()->startOfWeek();
        $lastWeek = now()->startOfWeek()->subWeek();

        // Orders count
        $todayCount = Order::whereDate('created_at', $today)->count();
        $yesterdayCount = Order::whereDate('created_at', $yesterday)->count();
        $thisWeekCount = Order::where('created_at', '>=', $thisWeek)->count();
        $lastWeekCount = Order::whereBetween('created_at', [$lastWeek, $lastWeek->copy()->endOfWeek()])->count();

        // Revenue calculations
        $todayRevenue = Order::whereDate('created_at', $today)->sum('total_price');
        $yesterdayRevenue = Order::whereDate('created_at', $yesterday)->sum('total_price');
        $thisWeekRevenue = Order::where('created_at', '>=', $thisWeek)->sum('total_price');
        $lastWeekRevenue = Order::whereBetween('created_at', [$lastWeek, $lastWeek->copy()->endOfWeek()])->sum('total_price');

        // Completed orders
        $completedToday = Order::whereDate('created_at', $today)->where('status', 'completed')->count();
        $pendingToday = Order::whereDate('created_at', $today)->where('status', 'pending')->count();

        // Calculate trends
        $orderDiff = $todayCount - $yesterdayCount;
        $orderTrend = $yesterdayCount > 0 ? round(($orderDiff / $yesterdayCount) * 100, 1) : ($todayCount > 0 ? 100 : 0);

        $revenueDiff = $todayRevenue - $yesterdayRevenue;
        $revenueTrend = $yesterdayRevenue > 0 ? round(($revenueDiff / $yesterdayRevenue) * 100, 1) : ($todayRevenue > 0 ? 100 : 0);

        return [
            Stat::make('Pesanan Hari Ini', $todayCount)
                ->description($orderDiff >= 0 ? "+$orderDiff dari kemarin" : "$orderDiff dari kemarin")
                ->descriptionIcon($orderDiff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($orderDiff >= 0 ? 'success' : 'danger'),

            Stat::make('Pendapatan Hari Ini', 'Rp '.number_format($todayRevenue, 0, ',', '.'))
                ->description($revenueDiff >= 0 ? '+Rp '.number_format($revenueDiff, 0, ',', '.') : '-Rp '.number_format(abs($revenueDiff), 0, ',', '.'))
                ->descriptionIcon($revenueDiff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueDiff >= 0 ? 'success' : 'danger'),

            Stat::make('Pesanan Minggu Ini', $thisWeekCount)
                ->description($lastWeekCount > 0 ? round((($thisWeekCount - $lastWeekCount) / $lastWeekCount) * 100, 1).'% dari minggu lalu' : '+100%')
                ->descriptionIcon($thisWeekCount >= $lastWeekCount ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($thisWeekCount >= $lastWeekCount ? 'success' : 'danger'),

            Stat::make('Rata-rata per Pesanan', $todayCount > 0 ? 'Rp '.number_format($todayRevenue / $todayCount, 0, ',', '.') : 'Rp 0')
                ->description('Hari ini')
                ->color('gray'),

            Stat::make('Pesanan Selesai', $completedToday)
                ->description($pendingToday > 0 ? "$pendingToday pending hari ini" : 'Semua selesai')
                ->descriptionIcon($pendingToday === 0 ? 'heroicon-m-check-circle' : 'heroicon-m-clock')
                ->color($pendingToday === 0 ? 'success' : 'warning'),

            Stat::make('Total Pendapatan', 'Rp '.number_format(Order::sum('total_price'), 0, ',', '.'))
                ->description(Order::count().' pesanan total')
                ->color('primary'),
        ];
    }
}
