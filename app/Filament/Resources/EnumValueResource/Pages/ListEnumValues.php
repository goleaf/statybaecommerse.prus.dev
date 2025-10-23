<?php

declare(strict_types=1);

namespace App\Filament\Resources\EnumValueResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\EnumValueResource;
use App\Models\EnumValue;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;

class ListEnumValues extends BaseListRecords
{
    use HasWidgetTabs;

    protected static string $resource = EnumValueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getWidgetTabs(): array
    {
        $tabs = [
            'all' => Tab::make(__('admin.enum_values.tabs.all')),
        ];

        foreach (EnumValue::getTypes() as $type => $label) {
            $tabs[$type] = Tab::make($label)
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('type', $type))
                ->badge(fn () => $this->getResource()::getModel()::where('type', $type)->count());
        }

        return $tabs;
    }
}
