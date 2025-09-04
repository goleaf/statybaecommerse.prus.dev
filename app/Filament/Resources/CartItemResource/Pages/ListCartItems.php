<?php declare(strict_types=1);

namespace App\Filament\Resources\CartItemResource\Pages;

use App\Filament\Resources\CartItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListCartItems extends ListRecords
{
    protected static string $resource = CartItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
