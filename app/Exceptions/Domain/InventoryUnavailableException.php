<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

final class InventoryUnavailableException extends DomainException
{
    public function __construct(string $sku)
    {
        parent::__construct(
            errorCode: 'inventory.insufficient',
            translationKey: 'exceptions.inventory.insufficient',
            context: ['sku' => $sku],
            status: 409,
        );
    }
}
