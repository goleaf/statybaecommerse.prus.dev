<?php

declare(strict_types=1);

namespace App\Filament\Resources\CollectionRuleResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\CollectionRuleResource;
use Filament\Actions;

final class ListCollectionRules extends BaseListRecords
{
    protected static string $resource = CollectionRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
