<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\OrderItem;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class Reports extends Page implements HasTable
{
    use InteractsWithTable;
    protected static string|UnitEnum|null $navigationGroup = 'Manajemen Pesanan';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-chart-bar';
    }

    public function getView(): string
    {
        return 'filament.pages.reports';
    }

    protected static ?string $navigationLabel = 'Laporan';

    protected static ?string $title = 'Laporan Penjualan';

    protected static ?int $navigationSort = 4;

    public function getBreadcrumbs(): array
    {
        return [
            url('/admin') => 'Dasbor',
            static::getUrl() => $this->getTitle(),
        ];
    }

    public ?string $startDate = null;

    public ?string $endDate = null;

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function getTableQuery(): Builder
    {
        return Order::query()
            ->when($this->startDate, fn($query) => $query->whereDate('created_at', '>=', $this->startDate))
            ->when($this->endDate, fn($query) => $query->whereDate('created_at', '<=', $this->endDate))
            ->with(['orderItems.product' => function ($query) {
                $query->select('id', 'name'); // Only load necessary columns
            }]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('id')
                    ->label('ID Pesanan')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->date('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('customer_name')
                    ->label('Pelanggan')
                    ->searchable(),

                TextColumn::make('total_price')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('delivery_method')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'delivery' => 'warning',
                        'takeaway' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('orderItems')
                    ->label('Items')
                    ->formatStateUsing(function ($state) {
                        if (! $state) {
                            return '-';
                        }

                        $items = collect($state);
                        if ($items->isEmpty()) {
                            return '-';
                        }

                        return $items->map(function ($item) {
                            // Debug: check if item is what we expect
                            if (! is_object($item) || ! isset($item->quantity)) {
                                return 'Item Invalid';
                            }

                            $productName = 'Produk Tidak Ditemukan';
                            if (isset($item->product) && is_object($item->product) && isset($item->product->name)) {
                                $productName = $item->product->name;
                            }

                            return $productName . ' x' . $item->quantity;
                        })->join(', ');
                    })
                    ->limit(50),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\OrdersStatsOverview::class,
            \App\Filament\Widgets\RevenueChart::class,
        ];
    }

    public function getReportData(): array
    {
        $query = $this->getTableQuery();

        return [
            'total_orders' => $query->count(),
            'total_revenue' => $query->sum('total_price'),
            'avg_order_value' => $query->count() > 0 ? $query->sum('total_price') / $query->count() : 0,
            'completed_orders' => $query->where('status', 'completed')->count(),
            'pending_orders' => $query->where('status', 'pending')->count(),
            'cancelled_orders' => $query->where('status', 'cancelled')->count(),
            'delivery_orders' => $query->where('delivery_method', 'delivery')->count(),
            'takeaway_orders' => $query->where('delivery_method', 'takeaway')->count(),
        ];
    }

    public function getTopProducts(): array
    {
        return OrderItem::selectRaw('products.name, SUM(order_items.quantity) as total_quantity, SUM(order_items.subtotal) as total_revenue')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->when($this->startDate, fn($query) => $query->whereDate('orders.created_at', '>=', $this->startDate))
            ->when($this->endDate, fn($query) => $query->whereDate('orders.created_at', '<=', $this->endDate))
            ->groupBy('order_items.product_id', 'products.name')
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function refreshData(): void
    {
        // This method is called when filters change
        // The table and widgets will automatically refresh
    }
}
