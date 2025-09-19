<?php declare(strict_types=1);

namespace App\Filament\Resources\CollectionRuleResource\Pages;

use App\Filament\Resources\CollectionRuleResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListCollectionRules extends ListRecords
{
    protected static string $resource = CollectionRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

