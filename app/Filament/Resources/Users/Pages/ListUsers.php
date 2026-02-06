<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\CreateAction;

class ListUsers extends BaseListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
