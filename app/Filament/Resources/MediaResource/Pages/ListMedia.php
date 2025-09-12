<?php declare(strict_types=1);

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use Filament\Resources\Pages\ListRecords;

final class ListMedia extends ListRecords
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action since media is uploaded through other resources
        ];
    }
}
