<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerGroupResource\Pages;

use App\Filament\Resources\CustomerGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewCustomerGroup extends ViewRecord
{
    protected static string $resource = CustomerGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
