<?php

declare(strict_types=1);

namespace App\Filament\Resources\ChannelResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\ChannelResource;
use Filament\Actions\CreateAction;

final class ListChannels extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = ChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
