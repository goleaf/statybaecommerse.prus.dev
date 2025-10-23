<?php

declare(strict_types=1);

namespace App\Filament\Resources\Channels\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\Channels\ChannelResource;
use Filament\Actions\CreateAction;
use App\Filament\Pages\Support\BaseListRecords;

class ListChannels extends BaseListRecords
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
