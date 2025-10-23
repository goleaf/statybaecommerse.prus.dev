<?php

declare(strict_types=1);

namespace App\Filament\Resources\NormalSettingResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\NormalSettingResource;
use App\Filament\WidgetTabs\Components\WidgetTab;
use App\Filament\WidgetTabs\Concerns\HasWidgetTabs;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListNormalSettings extends BaseListRecords
{
    use HasResizableColumns;
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
            'all'    => Tab::make(__('normal_settings.tabs.all')),
            'string' => Tab::make(__('normal_settings.tabs.string'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'string'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('type', 'string')->count()),
            'integer' => WidgetTab::make(__('normal_settings.tabs.integer'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'integer'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('type', 'integer')->count()),
            'boolean' => WidgetTab::make(__('normal_settings.tabs.boolean'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'boolean'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('type', 'boolean')->count()),
            'array' => WidgetTab::make(__('normal_settings.tabs.array'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'array'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('type', 'array')->count()),
            'json' => WidgetTab::make(__('normal_settings.tabs.json'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'json'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('type', 'json')->count()),
            'public' => WidgetTab::make(__('normal_settings.tabs.public'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_public', true))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('is_public', true)->count()),
            'private' => WidgetTab::make(__('normal_settings.tabs.private'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_public', false))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('is_public', false)->count()),
            'active' => WidgetTab::make(__('normal_settings.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('is_active', true)->count()),
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
