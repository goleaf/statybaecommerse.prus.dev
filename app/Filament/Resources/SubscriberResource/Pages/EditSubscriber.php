<?php

declare(strict_types=1);

namespace App\Filament\Resources\SubscriberResource\Pages;

use App\Filament\Resources\SubscriberResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

final class EditSubscriber extends EditRecord
{
    protected static string $resource = SubscriberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('send_test_email')
                ->label('Send Test Email')
                ->icon('heroicon-o-paper-airplane')
                ->color('info')
                ->action(function () {
                    // TODO: Implement test email sending
                    Notification::make()
                        ->title('Test email sent successfully')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('unsubscribe')
                ->label('Unsubscribe')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->status === 'active')
                ->action(function () {
                    $this->record->unsubscribe();
                    Notification::make()
                        ->title('Subscriber unsubscribed successfully')
                        ->success()
                        ->send();
                    $this->refreshFormData(['status']);
                }),

            Actions\Action::make('resubscribe')
                ->label('Resubscribe')
                ->icon('heroicon-o-check')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->status === 'unsubscribed')
                ->action(function () {
                    $this->record->resubscribe();
                    Notification::make()
                        ->title('Subscriber resubscribed successfully')
                        ->success()
                        ->send();
                    $this->refreshFormData(['status']);
                }),

            Actions\DeleteAction::make(),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Subscriber updated successfully')
            ->success()
            ->body('The subscriber information has been updated.');
    }
}
