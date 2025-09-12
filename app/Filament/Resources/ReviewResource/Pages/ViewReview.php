<?php declare(strict_types=1);

namespace App\Filament\Resources\ReviewResource\Pages;

use App\Filament\Resources\ReviewResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;

final class ViewReview extends ViewRecord
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
