<?php declare(strict_types=1);

namespace App\Filament\Resources\CollectionRuleResource\Pages;

use App\Filament\Resources\CollectionRuleResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;

class ViewCollectionRule extends ViewRecord
{
    protected static string $resource = CollectionRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

