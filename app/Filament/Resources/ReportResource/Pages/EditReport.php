<?php declare(strict_types=1);

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

final class EditReport extends EditRecord
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back_to_view')
                ->label(__('common.back_to_view'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url($this->getResource()::getUrl('view', ['record' => $this->getRecord()]))
                ->tooltip(__('common.back_to_view_tooltip')),
            Actions\Action::make('generate')
                ->label(__('admin.reports.actions.generate'))
                ->icon('heroicon-o-play')
                ->color('success')
                ->action(function () {
                    $this->record->update([
                        'last_generated_at' => now(),
                        'generated_by' => auth()->id(),
                    ]);

                    Notification::make()
                        ->title(__('admin.reports.notifications.generated'))
                        ->success()
                        ->send();
                })
                ->visible(fn(): bool => $this->record->is_active),
            Actions\Action::make('view')
                ->label(__('admin.reports.actions.view'))
                ->icon('heroicon-o-eye')
                ->color('info')
                ->url(fn(): string => route('reports.show', $this->record))
                ->openUrlInNewTab(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
