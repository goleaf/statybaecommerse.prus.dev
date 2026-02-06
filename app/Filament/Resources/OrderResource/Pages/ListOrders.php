<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\OrderResource;
use Filament\Actions\CreateAction;

class ListOrders extends BaseListRecords
{
    protected static string $resource = OrderResource::class;

    public function loadTable(): void
    {
        if (! isset($this->table)) {
            $this->bootedInteractsWithTable();
        }

        $this->getTableRecords();
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
