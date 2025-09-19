<?php declare(strict_types=1);

namespace App\Filament\Resources\EnumManagementResource\Pages;

use App\Enums\CurrencyEnum;
use App\Enums\PriorityEnum;
use App\Enums\StatusEnum;
use App\Filament\Resources\EnumManagementResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;

class ListEnumManagement extends ListRecords
{
    protected static string $resource = EnumManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Enums'),
            'status' => Tab::make('Status Enum')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('enum_type', 'status')),
            'priority' => Tab::make('Priority Enum')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('enum_type', 'priority')),
            'currency' => Tab::make('Currency Enum')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('enum_type', 'currency')),
        ];
    }
}
