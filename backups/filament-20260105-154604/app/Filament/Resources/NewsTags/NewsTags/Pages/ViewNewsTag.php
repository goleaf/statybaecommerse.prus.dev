<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsTags\Pages;

use App\Filament\Resources\NewsTags\NewsTagResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewNewsTag extends ViewRecord
{
    protected static string $resource = NewsTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
