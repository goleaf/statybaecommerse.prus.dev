<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReviewResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\ReviewResource;
use Filament\Actions;

final class ListReviews extends BaseListRecords
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
