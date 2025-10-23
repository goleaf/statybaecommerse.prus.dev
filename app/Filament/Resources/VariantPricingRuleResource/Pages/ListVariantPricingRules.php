<?php

declare(strict_types=1);

namespace App\Filament\Resources\VariantPricingRuleResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\VariantPricingRuleResource;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

final class ListVariantPricingRules extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = VariantPricingRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
