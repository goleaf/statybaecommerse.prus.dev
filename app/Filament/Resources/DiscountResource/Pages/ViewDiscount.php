<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountResource\Pages;

use App\Filament\Resources\DiscountResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewDiscount extends ViewRecord
{
    protected static string $resource = DiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('duplicate')
                ->label('Duplicate')
                ->icon('heroicon-o-document-duplicate')
                ->action(function () {
                    $newDiscount = $this->record->replicate();
                    $newDiscount->name = $this->record->name.' (Copy)';
                    $newDiscount->slug = $this->record->slug.'-copy';
                    $newDiscount->status = 'draft';
                    $newDiscount->usage_count = 0;
                    $newDiscount->save();

                    $this->redirect($this->getResource()::getUrl('edit', ['record' => $newDiscount]));
                })
                ->requiresConfirmation(),
        ];
    }
}
