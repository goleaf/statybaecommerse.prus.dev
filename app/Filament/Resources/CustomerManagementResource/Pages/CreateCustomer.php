<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerManagementResource\Pages;

use App\Filament\Resources\CustomerManagementResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerManagementResource::class;
}
