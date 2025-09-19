<?php declare(strict_types=1);

namespace App\Filament\Resources\NewsImageResource\Pages;

use App\Filament\Resources\NewsImageResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewNewsImage extends ViewRecord
{
    protected static string $resource = NewsImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
