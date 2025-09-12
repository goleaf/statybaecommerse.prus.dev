<?php declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;

final class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back_to_list')
                ->label(__('common.back_to_list'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url($this->getResource()::getUrl('index'))
                ->tooltip(__('common.back_to_list_tooltip')),
            Actions\EditAction::make(),
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
}
