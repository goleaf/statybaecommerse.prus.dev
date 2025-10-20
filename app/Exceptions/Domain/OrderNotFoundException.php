<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

final class OrderNotFoundException extends DomainException
{
    public function __construct(string $orderNumber)
    {
        parent::__construct(
            errorCode: 'orders.not_found',
            translationKey: 'exceptions.orders.not_found',
            context: ['order' => $orderNumber],
            status: 404,
        );
    }
}
