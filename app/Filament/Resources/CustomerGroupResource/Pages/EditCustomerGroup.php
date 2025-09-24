<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerGroupResource\Pages;

use App\Filament\Resources\CustomerGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditCustomerGroup extends EditRecord
{
    protected static string $resource = CustomerGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
