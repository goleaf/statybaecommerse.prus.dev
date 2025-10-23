<?php

declare(strict_types=1);

namespace App\Filament\Resources\LegalResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\LegalResource;
use App\Filament\WidgetTabs\Components\WidgetTab;
use App\Filament\WidgetTabs\Concerns\HasWidgetTabs;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\WidgetTabs\Components\WidgetTab;
use App\Filament\WidgetTabs\Concerns\HasWidgetTabs;
use Illuminate\Database\Eloquent\Builder;

class ListLegals extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = LegalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('legal.actions.create'))
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getWidgetTabs(): array
    {
        return [
            'all' => WidgetTab::make(__('legal.tabs.all'))
                ->icon('heroicon-o-document-text')
                ->value(fn () => $this->getResource()::getEloquentQuery()->count()),
            'enabled' => WidgetTab::make(__('legal.tabs.enabled'))
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_enabled', true)),

            'disabled' => WidgetTab::make(__('legal.tabs.disabled'))
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_enabled', false)),

            'required' => WidgetTab::make(__('legal.tabs.required'))
                ->icon('heroicon-o-exclamation-triangle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_required', true)),

            'published' => WidgetTab::make(__('legal.tabs.published'))
                ->icon('heroicon-o-eye')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('published_at')),

            'draft' => WidgetTab::make(__('legal.tabs.draft'))
                ->icon('heroicon-o-pencil')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('published_at')),
        ];
    }
}
