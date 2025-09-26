<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected ?string $heading = 'Ubah Pesanan';

    protected static string $resource = OrderResource::class;

    protected string $view = 'filament.resources.orders.pages.edit-order';

    public function getBreadcrumbs(): array
    {
        return [
            url('/admin') => 'Dashboard',
            static::getResource()::getUrl() => static::getResource()::getNavigationLabel(),
            static::getUrl(['record' => $this->getRecord()]) => $this->getHeading(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->label('Cetak')->color('info'),
            DeleteAction::make(),
        ];
    }
}
