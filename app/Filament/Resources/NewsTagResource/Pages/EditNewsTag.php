<?php declare(strict_types=1);

namespace App\Filament\Resources\NewsTagResource\Pages;

use App\Filament\Resources\NewsTagResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditNewsTag extends EditRecord
{
    protected static string $resource = NewsTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

