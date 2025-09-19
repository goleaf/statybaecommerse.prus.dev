<?php declare(strict_types=1);

namespace App\Filament\Resources\UserProductInteractionResource\Pages;

use App\Filament\Resources\UserProductInteractionResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateUserProductInteraction extends CreateRecord
{
    protected static string $resource = UserProductInteractionResource::class;
}
