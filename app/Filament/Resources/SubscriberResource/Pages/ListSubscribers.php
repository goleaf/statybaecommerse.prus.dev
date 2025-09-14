<?php

declare(strict_types=1);

namespace App\Filament\Resources\SubscriberResource\Pages;

use App\Filament\Resources\SubscriberResource;
use App\Filament\Resources\SubscriberResource\Widgets\SubscriberStatsWidget;
use App\Filament\Resources\SubscriberResource\Widgets\RecentSubscribersWidget;
use App\Filament\Resources\SubscriberResource\Widgets\SubscriberGrowthWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;

final class ListSubscribers extends ListRecords
{
    protected static string $resource = SubscriberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add Subscriber')
                ->icon('heroicon-o-plus'),

            Actions\Action::make('import')
                ->label('Import Subscribers')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('info')
                ->url(route('filament.admin.resources.subscribers.import')),

            Actions\Action::make('export_all')
                ->label('Export All')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action('exportAllSubscribers'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SubscriberStatsWidget::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Subscribers')
                ->badge(fn () => \App\Models\Subscriber::count()),

            'active' => Tab::make('Active')
                ->badge(fn () => \App\Models\Subscriber::active()->count())
                ->modifyQueryUsing(fn ($query) => $query->active()),

            'recent' => Tab::make('Recent (30 days)')
                ->badge(fn () => \App\Models\Subscriber::recent(30)->count())
                ->modifyQueryUsing(fn ($query) => $query->recent(30)),

            'unsubscribed' => Tab::make('Unsubscribed')
                ->badge(fn () => \App\Models\Subscriber::unsubscribed()->count())
                ->modifyQueryUsing(fn ($query) => $query->unsubscribed()),
        ];
    }

    public function exportAllSubscribers(): void
    {
        // TODO: Implement export all functionality
        \Filament\Notifications\Notification::make()
            ->title('Export started')
            ->success()
            ->send();
    }
}
