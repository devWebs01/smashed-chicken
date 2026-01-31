<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Services\FonnteService;
use Illuminate\Console\Command;

class FonnteStatisticsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fonnte:statistics
                            {device? : Device phone number (optional)}
                            {--start= : Start date (Y-m-d)}
                            {--end= : End date (Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Fonnte message statistics for a device';

    /**
     * Execute the console command.
     */
    public function handle(FonnteService $fonnteService): int
    {
        $devicePhone = $this->argument('device');
        $startDate = $this->option('start');
        $endDate = $this->option('end');

        // Get device
        if ($devicePhone) {
            $device = Device::where('device', $devicePhone)->first();
            if (! $device) {
                $this->error('Device not found: '.$devicePhone);

                return Command::FAILURE;
            }
        } else {
            // Show list of devices
            $devices = Device::all();
            if ($devices->isEmpty()) {
                $this->error('No devices found in database');

                return Command::FAILURE;
            }

            $this->info('Available devices:');
            $devices->each(function ($device, $index): void {
                $this->line('  '.($index + 1).sprintf('. %s (%s)', $device->name, $device->device));
            });

            $choice = $this->anticipate('Select device number', $devices->pluck('device')->toArray());
            $device = Device::where('device', $choice)->first();

            if (! $device) {
                $this->error('Invalid device selection');

                return Command::FAILURE;
            }
        }

        $this->info(sprintf('Fetching statistics for: %s (%s)', $device->name, $device->device));
        $this->newLine();

        // Get statistics
        $result = $fonnteService->getStatistics(
            $device->token,
            $startDate,
            $endDate
        );

        if (! $result['status']) {
            $this->error('Failed to fetch statistics: '.($result['error'] ?? 'Unknown error'));

            return Command::FAILURE;
        }

        $data = $result['data'] ?? [];

        // Display statistics
        $this->displayStatistics($data);

        return Command::SUCCESS;
    }

    /**
     * Display statistics in a formatted table
     *
     * @param  array  $data  Statistics data
     */
    private function displayStatistics(array $data): void
    {
        if ($data === []) {
            $this->warn('No statistics data available');

            return;
        }

        $this->info('=== Fonnte Message Statistics ===');
        $this->newLine();

        // Display main stats
        if (isset($data['outgoing'])) {
            $this->line('<fg=green>Outgoing Messages:</> '.$data['outgoing']);
        }

        if (isset($data['incoming'])) {
            $this->line('<fg=blue>Incoming Messages:</> '.$data['incoming']);
        }

        if (isset($data['total'])) {
            $this->line('<fg=yellow>Total Messages:</> '.$data['total']);
        }

        $this->newLine();

        // Display per-day stats if available
        if (isset($data['daily']) && is_array($data['daily'])) {
            $this->table(
                ['Date', 'Outgoing', 'Incoming', 'Total'],
                collect($data['daily'])->map(fn ($day): array => [
                    $day['date'] ?? 'Unknown',
                    $day['outgoing'] ?? 0,
                    $day['incoming'] ?? 0,
                    ($day['outgoing'] ?? 0) + ($day['incoming'] ?? 0),
                ])->toArray()
            );
        }

        // Display quota info if available
        if (isset($data['quota'])) {
            $this->info('Quota Information:');
            $this->line('  Used: '.($data['quota']['used'] ?? 0));
            $this->line('  Remaining: '.($data['quota']['remaining'] ?? 0));
            $this->line('  Total: '.($data['quota']['total'] ?? 0));
        }
    }
}
