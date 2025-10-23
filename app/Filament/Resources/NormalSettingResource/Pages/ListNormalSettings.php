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
        return [
            'all'    => Tab::make(__('admin.normal_settings.tabs.all')),
            'string' => Tab::make(__('admin.normal_settings.tabs.string'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'string'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'string')->count()),
            'integer' => Tab::make(__('admin.normal_settings.tabs.integer'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'integer'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'integer')->count()),
            'boolean' => Tab::make(__('admin.normal_settings.tabs.boolean'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'boolean'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'boolean')->count()),
            'array' => Tab::make(__('admin.normal_settings.tabs.array'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'array'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'array')->count()),
            'json' => Tab::make(__('admin.normal_settings.tabs.json'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'json'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'json')->count()),
            'public' => Tab::make(__('admin.normal_settings.tabs.public'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_public', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_public', true)->count()),
            'private' => Tab::make(__('admin.normal_settings.tabs.private'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_public', false))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_public', false)->count()),
            'active' => Tab::make(__('admin.normal_settings.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_active', true)->count()),
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
