<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountConditionResource\Pages;

use App\Filament\Resources\DiscountConditionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

final class CreateDiscountCondition extends CreateRecord
{
    protected static string $resource = DiscountConditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back_to_list')
                ->label(__('common.back_to_list'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url($this->getResource()::getUrl('index'))
                ->tooltip(__('common.back_to_list_tooltip')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('discount_conditions.notifications.created');
    }
}
