<?php

declare(strict_types=1);

namespace App\Filament\Resources\VariantImageResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\VariantImageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListVariantImages extends ListRecords
{
    use HasResizableColumns;

    protected static string $resource = VariantImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
