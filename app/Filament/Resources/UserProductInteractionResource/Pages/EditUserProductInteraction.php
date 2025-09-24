<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserProductInteractionResource\Pages;

use App\Filament\Resources\UserProductInteractionResource;
use Filament\Resources\Pages\EditRecord;

final class EditUserProductInteraction extends EditRecord
{
    protected static string $resource = UserProductInteractionResource::class;
}
