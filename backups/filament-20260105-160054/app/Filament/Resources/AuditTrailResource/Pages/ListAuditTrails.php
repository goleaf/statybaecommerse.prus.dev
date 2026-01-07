<?php

declare(strict_types=1);

namespace App\Filament\Resources\AuditTrailResource\Pages;

use App\Filament\Resources\AuditTrailResource;
use Filament\Resources\Pages\ListRecords;

final class ListAuditTrails extends ListRecords
{
    protected static string $resource = AuditTrailResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
