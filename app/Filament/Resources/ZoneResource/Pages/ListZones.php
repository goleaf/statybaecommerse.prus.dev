<?php declare(strict_types=1);

namespace App\Filament\Resources\ZoneResource\Pages;

use App\Filament\Resources\ZoneResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

final class ListZones extends ListRecords
{
    protected static string $resource = ZoneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('admin.actions.create_zone')),
        ];
    }

    public function getTitle(): string
    {
        return __('admin.titles.zones');
    }
}
