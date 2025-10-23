<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsImages\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\NewsImages\NewsImageResource;
use Filament\Actions\CreateAction;

class ListNewsImages extends BaseListRecords
{
    
    protected static string $resource = NewsImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
