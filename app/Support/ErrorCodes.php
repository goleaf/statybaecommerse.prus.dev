<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

/**
 * Application-wide error code definitions for consistent API and UI messaging.
 *
 * Each code should be stable and machine-readable so that API clients and
 * front-end applications can implement reliable error handling logic.
 */
final class ErrorCodes
{
    /**
     * Returned when a requested product record cannot be located.
     */
    public const E_PRODUCT_NOT_FOUND = 'product.not_found';

    /**
     * Returned when a product exists but is inactive or otherwise hidden from sale.
     */
    public const E_PRODUCT_INACTIVE = 'product.inactive';

    /**
     * Returned when a product cannot be purchased due to zero available stock.
     */
    public const E_PRODUCT_OUT_OF_STOCK = 'product.out_of_stock';

    /**
     * Returned when the requested product variant or configuration is missing.
     */
    public const E_VARIANT_NOT_FOUND = 'variant.not_found';

    /**
     * Returned when a cart item identifier no longer exists within the customer's cart.
     */
    public const E_CART_ITEM_NOT_FOUND = 'cart.item_not_found';

    /**
     * Registry of error code descriptions keyed by the machine-readable code.
     *
     * @var array<string, string>
     */
    private const DEFINITIONS = [
        self::NOT_FOUND => 'Resource requested by the client could not be located.',
        self::SERVER_ERROR => 'Unexpected server exception occurred while handling the request.',
        self::VALIDATION_FAILED => 'Provided data failed validation checks.',
        self::UNAUTHORIZED => 'Request lacks valid authentication credentials.',
        self::FORBIDDEN => 'Authenticated request does not have permission to access the resource.',
    ];

    private function __construct()
    {
        /** @var array<string, string> $constants */
        $constants = (new ReflectionClass(self::class))->getConstants();

        return array_values($constants);
    }

    /**
     * Determine whether the provided code matches one of the declared constants.
     */
    public static function isValid(string $code): bool
    {
        return in_array($code, self::all(), true);
    }

    /**
     * Retrieve all registered error codes.
     *
     * @return list<string>
     */
    public static function all(): array
    {
        return array_keys(self::DEFINITIONS);
    }

    /**
     * Retrieve the descriptions for every registered error code.
     *
     * @return array<string, string>
     */
    public static function descriptions(): array
    {
        return self::DEFINITIONS;
    }

    /**
     * Get the human-readable description for a specific error code.
     */
    public static function describe(string $code): ?string
    {
        return self::DEFINITIONS[$code] ?? null;
    }

    /**
     * Determine if the provided error code is registered.
     */
    public static function isValid(string $code): bool
    {
        return array_key_exists($code, self::DEFINITIONS);
    }

    /**
     * Ensure the provided code is registered.
     *
     * @throws InvalidArgumentException when the code is unknown.
     */
    public static function assertValid(string $code): void
    {
        if (! self::isValid($code)) {
            throw new InvalidArgumentException(sprintf('Unknown error code "%s".', $code));
        }
    }
}
