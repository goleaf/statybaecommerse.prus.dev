<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsResource\Pages;

use App\Enums\ModerationState;
use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\NewsResource;
use App\Filament\Resources\NewsResource\Widgets\NewsPerformanceChart;
use App\Filament\Resources\NewsResource\Widgets\NewsResourceStats;
use App\Filament\WidgetTabs\Components\WidgetTab;
use App\Filament\WidgetTabs\Concerns\HasWidgetTabs;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;

final class ListNews extends BaseListRecords
{
        use HasWidgetTabs;

    protected static string $resource = NewsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            NewsResourceStats::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            NewsPerformanceChart::class,
        ];
    }

    public function getWidgetTabs(): array
    {
        return [
            'all'   => SchemaTab::make(__('news.tabs.all')),
            'draft' => SchemaTab::make(__('news.tabs.draft'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('moderation_state', ModerationState::Draft->value))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('moderation_state', ModerationState::Draft->value)->count()),
            'review' => WidgetTab::make(__('news.tabs.review'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('moderation_state', ModerationState::Review->value))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('moderation_state', ModerationState::Review->value)->count()),
            'published' => WidgetTab::make(__('news.tabs.published'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('moderation_state', ModerationState::Published->value))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('moderation_state', ModerationState::Published->value)->count()),
            'featured' => WidgetTab::make(__('news.tabs.featured'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_featured', true))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('is_featured', true)->count()),
            'breaking' => WidgetTab::make(__('news.tabs.breaking'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_breaking', true))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('is_breaking', true)->count()),
            'today' => WidgetTab::make(__('news.tabs.today'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('created_at', today()))
                ->value(fn () => $this->getResource()::getEloquentQuery()->whereDate('created_at', today())->count()),
            'this_week' => WidgetTab::make(__('news.tabs.this_week'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
                ->value(fn () => $this->getResource()::getEloquentQuery()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count()),
            'this_month' => WidgetTab::make(__('news.tabs.this_month'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]))
                ->value(fn () => $this->getResource()::getEloquentQuery()->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count()),
        ];
    }
}
