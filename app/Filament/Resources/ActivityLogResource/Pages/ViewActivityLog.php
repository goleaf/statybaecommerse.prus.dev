<?php

declare(strict_types=1);

namespace App\Filament\Resources\ActivityLogResource\Pages;

use App\Filament\Resources\ActivityLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewActivityLog extends ViewRecord
{
    protected static string $resource = ActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back_to_list')
                ->label(__('common.back_to_list'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url($this->getResource()::getUrl('index'))
                ->tooltip(__('common.back_to_list_tooltip')),
            Actions\Action::make('view_subject')
                ->label(__('admin.activity_logs.actions.view_subject'))
                ->icon('heroicon-o-eye')
                ->color('info')
                ->url(fn (): string => match ($this->record->subject_type) {
                    'App\Models\User' => route('filament.admin.resources.users.view', $this->record->subject_id),
                    'App\Models\Product' => route('filament.admin.resources.products.view', $this->record->subject_id),
                    'App\Models\Order' => route('filament.admin.resources.orders.view', $this->record->subject_id),
                    'App\Models\Category' => route('filament.admin.resources.categories.view', $this->record->subject_id),
                    'App\Models\Brand' => route('filament.admin.resources.brands.view', $this->record->subject_id),
                    default => '#',
                })
                ->openUrlInNewTab()
                ->visible(fn (): bool => $this->record->subject_id !== null),
        ];
    }
}
