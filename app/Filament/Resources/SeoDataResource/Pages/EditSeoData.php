<?php declare(strict_types=1);

namespace App\Filament\Resources\SeoDataResource\Pages;

use App\Filament\Resources\SeoDataResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

final class EditSeoData extends EditRecord
{
    protected static string $resource = SeoDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
