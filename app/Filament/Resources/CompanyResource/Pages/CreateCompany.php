<?php

declare(strict_types=1);

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

final class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;

}
