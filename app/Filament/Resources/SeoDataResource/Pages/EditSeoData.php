<?php declare(strict_types=1);

namespace App\Filament\Resources\SeoDataResource\Pages;

use App\Filament\Resources\SeoDataResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

final class EditSeoData extends EditRecord
{
    protected static string $resource = SeoDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back_to_view')
                ->label(__('common.back_to_view'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url($this->getResource()::getUrl('view', ['record' => $this->getRecord()]))
                ->tooltip(__('common.back_to_view_tooltip')),
            Actions\ViewAction::make()
                ->label(__('admin.seo_data.view')),
            Actions\DeleteAction::make()
                ->label(__('admin.seo_data.delete')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('admin.seo_data.notifications.updated');
    }
}
