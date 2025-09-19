<?php declare(strict_types=1);

namespace App\Filament\Resources\SystemResource\Pages;

use App\Filament\Resources\SystemResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Cache;

final class EditSystem extends EditRecord
{
    protected static string $resource = SystemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
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
            Action::make('reset_to_default')
                ->label('Reset to Default')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () {
                    $record = $this->getRecord();
                    if ($record->default_value) {
                        $record->update(['value' => $record->default_value]);
                        $this->notify('success', 'Setting reset to default value');
                    }
                })
                ->visible(fn() => !empty($this->getRecord()->default_value)),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Clear cache when value is updated
        if (isset($data['value'])) {
            $record = $this->getRecord();
            Cache::forget($record->cache_key ?? $record->key);
        }

        return $data;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'System setting updated successfully';
    }
}
