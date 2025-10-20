<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use App\Support\ErrorCode;
use Illuminate\Http\Response;

final class InventoryUnavailableException extends DomainException
{
    public function __construct(string $sku)
    {
        parent::__construct(
            errorCode: ErrorCode::InventoryInsufficient,
            context: ['sku' => $sku],
            status: Response::HTTP_CONFLICT,
        );
    }
}
