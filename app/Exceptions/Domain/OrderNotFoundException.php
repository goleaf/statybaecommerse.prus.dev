<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use App\Support\ErrorCode;
use Illuminate\Http\Response;

final class OrderNotFoundException extends DomainException
{
    public function __construct(string $orderNumber)
    {
        parent::__construct(
            errorCode: ErrorCode::OrderNotFound,
            context: ['order' => $orderNumber],
            status: Response::HTTP_NOT_FOUND,
        );
    }
}
