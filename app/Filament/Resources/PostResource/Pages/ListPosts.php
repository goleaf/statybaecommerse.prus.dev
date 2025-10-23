<?php

declare(strict_types=1);

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\PostResource;
use Filament\Actions;

final class ListPosts extends BaseListRecords
{
    
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
