<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use App\Support\ErrorCodes;

final class InventoryUnavailableException extends DomainException
{
    public function __construct(string $sku)
    {
        parent::__construct(
            errorCode: ErrorCodes::INVENTORY_INSUFFICIENT,
            translationKey: 'exceptions.inventory.insufficient',
            context: ['sku' => $sku],
            status: 409,
        );
    }
}
