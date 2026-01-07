<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderItemResource\Pages;

use App\Filament\Resources\OrderItemResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateOrderItem extends CreateRecord
{
    protected static string $resource = OrderItemResource::class;
}
