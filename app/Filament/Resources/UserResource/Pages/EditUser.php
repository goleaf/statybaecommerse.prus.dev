<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

final class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('verify_email')
                ->label(__('users.actions.verify_email'))
                ->icon('heroicon-o-shield-check')
                ->action(function () {
                    $this->record->update(['email_verified_at' => now()]);
                    Notification::make()
                        ->title(__('users.messages.email_verified_success'))
                        ->success()
                        ->send();
                })
                ->visible(fn (): bool => is_null($this->record->email_verified_at)),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('users.messages.updated_successfully'))
            ->body(__('users.messages.updated_successfully_description'));
    }
}
