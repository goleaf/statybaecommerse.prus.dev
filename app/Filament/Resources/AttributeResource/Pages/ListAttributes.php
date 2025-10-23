<?php

declare(strict_types=1);

namespace App\Filament\Resources\AttributeResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\AttributeResource;
use Filament\Actions;

final class ListAttributes extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = AttributeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
