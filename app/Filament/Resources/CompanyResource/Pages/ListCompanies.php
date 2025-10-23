<?php

declare(strict_types=1);

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\CompanyResource;
use Filament\Actions;

final class ListCompanies extends BaseListRecords
{
    
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
