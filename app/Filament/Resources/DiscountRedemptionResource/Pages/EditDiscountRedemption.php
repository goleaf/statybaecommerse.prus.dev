<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountRedemptionResource\Pages;

use App\Filament\Resources\DiscountRedemptionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDiscountRedemption extends EditRecord
{
    protected static string $resource = DiscountRedemptionResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return DiscountRedemptionResource::normalizePayload($data);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
