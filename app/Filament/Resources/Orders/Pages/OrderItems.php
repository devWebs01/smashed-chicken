<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class OrderItems extends Page
{
    use InteractsWithRecord;

    protected ?string $heading = 'Menu-menu Pesanan';

    protected static string $resource = OrderResource::class;

    protected string $view = 'filament.resources.orders.pages.order-items';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }
}
