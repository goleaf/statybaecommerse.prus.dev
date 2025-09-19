<?php

declare(strict_types=1);

namespace App\Filament\Resources\VariantPricingRuleResource\Pages;

use App\Filament\Resources\VariantPricingRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListVariantPricingRules extends ListRecords
{
    protected static string $resource = VariantPricingRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
