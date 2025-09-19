<?php declare(strict_types=1);

namespace App\Filament\Resources\NewsCommentResource\Pages;

use App\Filament\Resources\NewsCommentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewNewsComment extends ViewRecord
{
    protected static string $resource = NewsCommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
