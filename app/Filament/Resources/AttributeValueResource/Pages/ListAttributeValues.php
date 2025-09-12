<?php

declare(strict_types=1);

namespace App\Filament\Resources\AttributeValueResource\Pages;

use App\Filament\Resources\AttributeValueResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

final class ListAttributeValues extends ListRecords
{
    protected static string $resource = AttributeValueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('attributes.all_values'))
                ->icon('heroicon-m-tag'),
            'enabled' => Tab::make(__('attributes.enabled_values'))
                ->icon('heroicon-m-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_enabled', true)),
            'required' => Tab::make(__('attributes.required_values'))
                ->icon('heroicon-m-exclamation-triangle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_required', true)),
            'default' => Tab::make(__('attributes.default_values'))
                ->icon('heroicon-m-star')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_default', true)),
            'with_color' => Tab::make(__('attributes.with_color'))
                ->icon('heroicon-m-paint-brush')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('color_code')),
            'trashed' => Tab::make(__('attributes.trashed'))
                ->icon('heroicon-m-trash')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed()),
        ];
    }
}
