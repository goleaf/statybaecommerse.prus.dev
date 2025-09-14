<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductHistoryResource\Pages;

use App\Filament\Resources\ProductHistoryResource;
use App\Filament\Resources\ProductHistoryResource\Widgets\ProductHistoryStatsWidget;
use App\Filament\Resources\ProductHistoryResource\Widgets\RecentProductChangesWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

final class ListProductHistories extends ListRecords
{
    protected static string $resource = ProductHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('export')
                ->label('Export History')
                ->icon('heroicon-o-arrow-down-tray')
                ->action('exportHistory')
                ->color('success'),
            Actions\Action::make('cleanup')
                ->label('Cleanup Old Records')
                ->icon('heroicon-o-trash')
                ->action('cleanupOldRecords')
                ->color('warning')
                ->requiresConfirmation()
                ->modalDescription('This will delete history records older than 1 year. This action cannot be undone.'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ProductHistoryStatsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            RecentProductChangesWidget::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All History')
                ->badge(fn () => $this->getModel()::count()),

            'recent' => Tab::make('Recent (7 days)')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('created_at', '>=', now()->subDays(7)))
                ->badge(fn () => $this->getModel()::where('created_at', '>=', now()->subDays(7))->count()),

            'significant' => Tab::make('Significant Changes')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('field_name', [
                    'price', 'sale_price', 'stock_quantity', 'status', 'is_visible'
                ]))
                ->badge(fn () => $this->getModel()::whereIn('field_name', [
                    'price', 'sale_price', 'stock_quantity', 'status', 'is_visible'
                ])->count()),

            'price_changes' => Tab::make('Price Changes')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('action', 'price_changed'))
                ->badge(fn () => $this->getModel()::where('action', 'price_changed')->count()),

            'stock_updates' => Tab::make('Stock Updates')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('action', 'stock_updated'))
                ->badge(fn () => $this->getModel()::where('action', 'stock_updated')->count()),

            'status_changes' => Tab::make('Status Changes')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('action', 'status_changed'))
                ->badge(fn () => $this->getModel()::where('action', 'status_changed')->count()),
        ];
    }

    public function exportHistory(): void
    {
        $this->redirect(route('admin.product-history.export'));
    }

    public function cleanupOldRecords(): void
    {
        $deletedCount = $this->getModel()::where('created_at', '<', now()->subYear())->delete();
        
        $this->notify('success', "Cleaned up {$deletedCount} old history records.");
    }
}
