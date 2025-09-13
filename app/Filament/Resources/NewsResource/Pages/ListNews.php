<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsResource\Pages;

use App\Filament\Resources\NewsResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

final class ListNews extends ListRecords
{
    protected static string $resource = NewsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back_to_dashboard')
                ->label(__('common.back_to_dashboard'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url('/admin')
                ->tooltip(__('common.back_to_dashboard_tooltip')),
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('admin.news.status.all'))
                ->icon('heroicon-m-newspaper'),
            'published' => Tab::make(__('admin.news.status.published'))
                ->modifyQueryUsing(fn ($query) => $query->published())
                ->icon('heroicon-m-check-circle')
                ->badge(fn () => \App\Models\News::published()->count()),
            'draft' => Tab::make(__('admin.news.status.draft'))
                ->modifyQueryUsing(fn ($query) => $query->where('is_visible', false)->orWhereNull('published_at'))
                ->icon('heroicon-m-document-text')
                ->badge(fn () => \App\Models\News::where('is_visible', false)->orWhereNull('published_at')->count()),
            'featured' => Tab::make(__('admin.news.status.featured'))
                ->modifyQueryUsing(fn ($query) => $query->featured())
                ->icon('heroicon-m-star')
                ->badge(fn () => \App\Models\News::featured()->count()),
        ];
    }
}
