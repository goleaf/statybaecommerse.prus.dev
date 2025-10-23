<?php

declare(strict_types=1);

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\CompanyResource;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

final class ListCompanies extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
