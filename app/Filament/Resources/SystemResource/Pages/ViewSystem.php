<?php declare(strict_types=1);

namespace App\Filament\Resources\SystemResource\Pages;

use App\Filament\Resources\SystemResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Cache;

final class ViewSystem extends ViewRecord
{
    protected static string $resource = SystemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
            Action::make('clear_cache')
                ->label('Clear Cache')
                ->icon('heroicon-o-trash')
                ->color('warning')
                ->action(function () {
                    $record = $this->getRecord();
                    Cache::forget($record->cache_key ?? $record->key);

                    $this->notify('success', 'Cache cleared successfully');
                })
                ->visible(fn() => !empty($this->getRecord()->cache_key)),
            Action::make('refresh_value')
                ->label('Refresh Value')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->action(function () {
                    $record = $this->getRecord();
                    $record->touch();

                    $this->notify('success', 'Value refreshed successfully');
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SystemResource\Widgets\SystemSettingStatsWidget::class,
        ];
    }
}
