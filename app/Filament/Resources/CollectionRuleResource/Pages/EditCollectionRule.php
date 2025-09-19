<?php declare(strict_types=1);

namespace App\Filament\Resources\CollectionRuleResource\Pages;

use App\Filament\Resources\CollectionRuleResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditCollectionRule extends EditRecord
{
    protected static string $resource = CollectionRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

