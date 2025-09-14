<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountConditionResource\Pages;

use App\Filament\Resources\DiscountConditionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final /**
 * ViewDiscountCondition
 * 
 * Filament resource for admin panel management.
 */
class ViewDiscountCondition extends ViewRecord
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
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('test_condition')
                ->label(__('discount_conditions.actions.test_condition'))
                ->icon('heroicon-o-beaker')
                ->color('info')
                ->form([
                    \Filament\Forms\Components\TextInput::make('test_value')
                        ->label(__('discount_conditions.fields.test_value'))
                        ->required()
                        ->helperText(__('discount_conditions.helpers.test_value')),
                ])
                ->action(function (array $data): void {
                    $matches = $this->record->matches($data['test_value']);
                    $message = $matches
                        ? __('discount_conditions.messages.condition_matches')
                        : __('discount_conditions.messages.condition_does_not_match');

                    \Filament\Notifications\Notification::make()
                        ->title($message)
                        ->success()
                        ->send();
                }),
        ];
    }
}
