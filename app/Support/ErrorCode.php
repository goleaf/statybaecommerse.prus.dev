<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\Response;

/**
 * Enumerates stable application error codes that power structured API responses.
 */
enum ErrorCode: string
{
    case NotFound = 'error.not_found';
    case ServerError = 'error.server';
    case ValidationFailed = 'error.validation';
    case Unauthorized = 'error.unauthorized';
    case Forbidden = 'error.forbidden';
    case OrderNotFound = 'orders.not_found';
    case InventoryInsufficient = 'inventory.insufficient';

    /**
     * Resolve the translation key associated with the error code.
     */
    public function translationKey(): string
    {
        return match ($this) {
            self::OrderNotFound         => 'exceptions.orders.not_found',
            self::InventoryInsufficient => 'exceptions.inventory.insufficient',
            self::NotFound              => 'errors.not_found',
            self::ServerError           => 'errors.server_error',
            self::ValidationFailed      => 'errors.validation_failed',
            self::Unauthorized          => 'errors.unauthorized',
            self::Forbidden             => 'errors.forbidden',
        };
    }

    /**
     * Default HTTP status code the API should return for this error.
     */
    public function defaultStatus(): int
    {
        return match ($this) {
            self::NotFound, self::OrderNotFound => Response::HTTP_NOT_FOUND,
            self::ValidationFailed      => Response::HTTP_UNPROCESSABLE_ENTITY,
            self::Unauthorized          => Response::HTTP_UNAUTHORIZED,
            self::Forbidden             => Response::HTTP_FORBIDDEN,
            self::InventoryInsufficient => Response::HTTP_CONFLICT,
            self::ServerError           => Response::HTTP_INTERNAL_SERVER_ERROR,
        };
    }
}
