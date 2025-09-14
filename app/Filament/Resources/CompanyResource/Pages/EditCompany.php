<?php

declare(strict_types=1);

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

final class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_subscribers')
                ->label('View Subscribers')
                ->icon('heroicon-o-users')
                ->color('info')
                ->url(fn (): string => 
                    route('filament.admin.resources.subscribers.index', ['tableFilters' => [
                        'company' => ['value' => $this->record->name]
                    ]])
                )
                ->openUrlInNewTab(),

            Actions\DeleteAction::make(),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Company updated successfully')
            ->success()
            ->body('The company information has been updated.');
    }
}
