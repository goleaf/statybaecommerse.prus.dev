<?php declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

final class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back_to_view')
                ->label(__('common.back_to_view'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url($this->getResource()::getUrl('view', ['record' => $this->getRecord()]))
                ->tooltip(__('common.back_to_view_tooltip')),
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('impersonate')
                ->label(__('admin.actions.impersonate'))
                ->icon('heroicon-o-user-circle')
                ->color('info')
                ->url(fn() => route('impersonate', $this->record->id))
                ->openUrlInNewTab()
                ->visible(fn() => auth()->user()?->is_admin ?? false),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('admin.notifications.user_updated');
    }
}
