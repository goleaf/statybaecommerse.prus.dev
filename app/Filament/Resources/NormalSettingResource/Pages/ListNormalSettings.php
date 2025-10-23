<?php

declare(strict_types=1);

namespace App\Filament\Resources\NormalSettingResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\NormalSettingResource;
use App\Models\NormalSetting;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;

class ListNormalSettings extends BaseListRecords
{
    use HasWidgetTabs;

    protected static string $resource = NormalSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getWidgetTabs(): array
    {
        $resource = $this->getResource();

        $tabs = [
            'all' => Tab::make(__('normal_settings.tabs.all')),
        ];

        foreach (NormalSetting::CANONICAL_TYPES as $type) {
            $tabs[$type] = Tab::make(__('normal_settings.tabs.' . $type))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', $type))
                ->badge(fn () => $resource::getEloquentQuery()->where('type', $type)->count());
        }

        $tabs['public'] = Tab::make(__('normal_settings.tabs.public'))
            ->modifyQueryUsing(fn (Builder $query) => $query->where('is_public', true))
            ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_public', true)->count());

        $tabs['private'] = Tab::make(__('normal_settings.tabs.private'))
            ->modifyQueryUsing(fn (Builder $query) => $query->where('is_public', false))
            ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_public', false)->count());

        $tabs['active'] = Tab::make(__('normal_settings.tabs.active'))
            ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
            ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_active', true)->count());

        return $tabs;
    }
}
