<?php

declare(strict_types=1);

namespace App\Filament\Resources\VariantPricingRuleResource\Pages;

use App\Filament\Resources\VariantPricingRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditVariantPricingRule extends EditRecord
{
    protected static string $resource = VariantPricingRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
