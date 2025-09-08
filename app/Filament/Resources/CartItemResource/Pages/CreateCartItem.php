<?php declare(strict_types=1);

namespace App\Filament\Resources\CartItemResource\Pages;

use App\Filament\Resources\CartItemResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateCartItem extends CreateRecord
{
    protected static string $resource = CartItemResource::class;
}
