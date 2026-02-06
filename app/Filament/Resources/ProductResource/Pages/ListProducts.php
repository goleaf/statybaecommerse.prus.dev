<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\ProductResource;
use Filament\Actions;

class ListProducts extends BaseListRecords
{
    protected static string $resource = ProductResource::class;

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
            Actions\CreateAction::make(),
        ];
    }
}
