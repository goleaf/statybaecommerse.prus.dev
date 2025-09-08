<?php declare(strict_types=1);

namespace App\Filament\Resources\LegalResource\Pages;

use App\Filament\Resources\LegalResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;

final class ViewLegal extends ViewRecord
{
    protected static string $resource = LegalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

