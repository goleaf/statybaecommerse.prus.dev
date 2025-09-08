<?php declare(strict_types=1);

namespace App\Filament\Resources\DiscountCodeResource\Pages;

use App\Filament\Resources\DiscountCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditDiscountCode extends EditRecord
{
    protected static string $resource = DiscountCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
