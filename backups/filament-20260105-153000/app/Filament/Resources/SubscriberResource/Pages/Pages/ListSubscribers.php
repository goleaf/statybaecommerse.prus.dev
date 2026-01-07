<?php

declare(strict_types=1);

namespace App\Filament\Resources\SubscriberResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\SubscriberResource;
use Filament\Actions;

class ListSubscribers extends BaseListRecords
{
    protected static string $resource = SubscriberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
