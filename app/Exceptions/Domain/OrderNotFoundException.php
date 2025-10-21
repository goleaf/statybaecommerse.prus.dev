<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use App\Support\ErrorCodes;

final class OrderNotFoundException extends DomainException
{
    public function __construct(string $orderNumber)
    {
        parent::__construct(
            errorCode: ErrorCodes::ORDER_NOT_FOUND,
            translationKey: 'exceptions.orders.not_found',
            context: ['order' => $orderNumber],
            status: 404,
        );
    }
}
