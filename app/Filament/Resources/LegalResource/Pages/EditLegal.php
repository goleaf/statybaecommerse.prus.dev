<?php declare(strict_types=1);

namespace App\Filament\Resources\LegalResource\Pages;

use App\Filament\Resources\LegalResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

final class EditLegal extends EditRecord
{
    protected static string $resource = LegalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

