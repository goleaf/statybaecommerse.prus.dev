<?php

declare(strict_types=1);

namespace App\Filament\Resources\CollectionRuleResource\Pages;

use App\Filament\Resources\CollectionRuleResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateCollectionRule extends CreateRecord
{
    protected static string $resource = CollectionRuleResource::class;
}
