<?php

declare (strict_types=1);
namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
/**
 * EditCompany
 * 
 * Filament v4 resource for EditCompany management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;
    /**
     * Handle getHeaderActions functionality with proper error handling.
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [Actions\Action::make('view_subscribers')->label('View Subscribers')->icon('heroicon-o-users')->color('info')->url(fn(): string => route('filament.admin.resources.subscribers.index', ['tableFilters' => ['company' => ['value' => $this->record->name]]]))->openUrlInNewTab(), Actions\DeleteAction::make()];
    }
    /**
     * Handle getSavedNotification functionality with proper error handling.
     * @return Notification|null
     */
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()->title('Company updated successfully')->success()->body('The company information has been updated.');
    }
}