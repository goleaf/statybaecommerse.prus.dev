<?php

declare(strict_types=1);

namespace App\Filament\Resources\VariantPricingRuleResource\Pages;

use App\Filament\Resources\VariantPricingRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

final class CreateVariantPricingRule extends CreateRecord
{
    protected static string $resource = VariantPricingRuleResource::class;

}
