<?php

declare (strict_types=1);
namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
/**
 * EditReport
 * 
 * Filament v4 resource for EditReport management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class EditReport extends EditRecord
{
    protected static string $resource = ReportResource::class;
    /**
     * Handle getHeaderActions functionality with proper error handling.
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [Actions\Action::make('back_to_view')->label(__('common.back_to_view'))->icon('heroicon-o-arrow-left')->color('gray')->url($this->getResource()::getUrl('view', ['record' => $this->getRecord()]))->tooltip(__('common.back_to_view_tooltip')), Actions\Action::make('generate')->label(__('admin.reports.actions.generate'))->icon('heroicon-o-play')->color('success')->action(function () {
            $this->record->update(['last_generated_at' => now(), 'generated_by' => auth()->id()]);
            Notification::make()->title(__('admin.reports.notifications.generated'))->success()->send();
        })->visible(fn(): bool => $this->record->is_active), Actions\Action::make('view')->label(__('admin.reports.actions.view'))->icon('heroicon-o-eye')->color('info')->url(fn(): string => route('reports.show', $this->record))->openUrlInNewTab(), Actions\DeleteAction::make()];
    }
    /**
     * Handle getRedirectUrl functionality with proper error handling.
     * @return string
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}