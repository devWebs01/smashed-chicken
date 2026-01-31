<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Widgets\OrdersPerDayChart;
use App\Filament\Widgets\OrdersStatsOverview;
use Dotswan\FilamentLaravelPulse\Widgets\PulseCache;
use Dotswan\FilamentLaravelPulse\Widgets\PulseExceptions;
use Dotswan\FilamentLaravelPulse\Widgets\PulseQueues;
use Dotswan\FilamentLaravelPulse\Widgets\PulseServers;
use Dotswan\FilamentLaravelPulse\Widgets\PulseSlowJobs;
use Dotswan\FilamentLaravelPulse\Widgets\PulseSlowOutGoingRequests;
use Dotswan\FilamentLaravelPulse\Widgets\PulseSlowQueries;
use Dotswan\FilamentLaravelPulse\Widgets\PulseSlowRequests;
use Dotswan\FilamentLaravelPulse\Widgets\PulseUsage;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Enums\ActionSize;
use Filament\Widgets\Widget;

class Dashboard extends BaseDashboard
{
    public function getColumns(): int|array
    {
        return 12;
    }

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('1h')
                    ->label('1 Hour')
                    ->action(fn () => $this->redirect(route('filament.admin.pages.dashboard'))),
                Action::make('24h')
                    ->label('24 Hours')
                    ->action(fn () => $this->redirect(route('filament.admin.pages.dashboard', ['period' => '24_hours']))),
                Action::make('7d')
                    ->label('7 Days')
                    ->action(fn () => $this->redirect(route('filament.admin.pages.dashboard', ['period' => '7_days']))),
            ])
                ->label('Filter')
                ->icon('heroicon-o-funnel')
                ->size(ActionSize::Small)
                ->color('gray')
                ->button(),
        ];
    }

    /**
     * @return array<class-string<Widget>|string>
     */
    public function getWidgets(): array
    {
        return [
            PulseServers::class,
            OrdersStatsOverview::class,
            OrdersPerDayChart::class,
            PulseCache::class,
            PulseExceptions::class,
            PulseUsage::class,
            PulseQueues::class,
            PulseSlowJobs::class,
            PulseSlowQueries::class,
            PulseSlowRequests::class,
            PulseSlowOutGoingRequests::class,
        ];
    }

    public function getVisibleWidgets(): array
    {
        return $this->getWidgets();
    }
}
