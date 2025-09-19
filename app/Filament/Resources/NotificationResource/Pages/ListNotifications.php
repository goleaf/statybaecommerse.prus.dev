<?php declare(strict_types=1);

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use App\Filament\Widgets\NotificationStatsWidget;
use App\Filament\Widgets\NotificationTrendsWidget;
use App\Filament\Widgets\NotificationTypesWidget;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;

class ListNotifications extends ListRecords
{
    protected static string $resource = NotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('notifications.tabs.all'))
                ->icon('heroicon-o-bell'),
            'unread' => Tab::make(__('notifications.tabs.unread'))
                ->icon('heroicon-o-exclamation-triangle')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereNull('read_at')),
            'read' => Tab::make(__('notifications.tabs.read'))
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereNotNull('read_at')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            NotificationStatsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            NotificationTrendsWidget::class,
            NotificationTypesWidget::class,
        ];
    }
}
