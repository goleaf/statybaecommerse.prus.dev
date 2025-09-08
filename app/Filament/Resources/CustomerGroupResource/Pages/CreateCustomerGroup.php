<?php declare(strict_types=1);

namespace App\Filament\Resources\CustomerGroupResource\Pages;

use App\Filament\Resources\CustomerGroupResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateCustomerGroup extends CreateRecord
{
    protected static string $resource = CustomerGroupResource::class;
}
