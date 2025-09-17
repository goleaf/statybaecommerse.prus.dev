<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsResource\Pages;

use App\Filament\Resources\NewsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

final class ListNews extends ListRecords
{
    protected static string $resource = NewsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('news.tabs.all')),
            
            'published' => Tab::make(__('news.tabs.published'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_published', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_published', true)->count()),
            
            'draft' => Tab::make(__('news.tabs.draft'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_published', false))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_published', false)->count()),
            
            'featured' => Tab::make(__('news.tabs.featured'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_featured', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_featured', true)->count()),
            
            'breaking' => Tab::make(__('news.tabs.breaking'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_breaking', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_breaking', true)->count()),
            
            'today' => Tab::make(__('news.tabs.today'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('created_at', today()))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->whereDate('created_at', today())->count()),
            
            'this_week' => Tab::make(__('news.tabs.this_week'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count()),
            
            'this_month' => Tab::make(__('news.tabs.this_month'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count()),
        ];
    }
}
