<?php declare(strict_types=1);

namespace App\Filament\Resources\AttributeResource\Pages;

use App\Filament\Resources\AttributeResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

final class EditAttribute extends EditRecord
{
    protected static string $resource = AttributeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
