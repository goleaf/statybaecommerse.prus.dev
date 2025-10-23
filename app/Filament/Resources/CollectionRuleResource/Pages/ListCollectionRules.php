<?php

declare(strict_types=1);

namespace App\Filament\Resources\CollectionRuleResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\CollectionRuleResource;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

final class ListCollectionRules extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = CollectionRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
