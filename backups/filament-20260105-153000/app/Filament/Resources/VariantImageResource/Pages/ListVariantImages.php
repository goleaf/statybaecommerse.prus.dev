<?php

declare(strict_types=1);

namespace App\Filament\Resources\VariantImageResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\VariantImageResource;
use Filament\Actions;

final class ListVariantImages extends BaseListRecords
{
    protected static string $resource = VariantImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
