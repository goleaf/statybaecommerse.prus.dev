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
     * Code for resources that cannot be found (HTTP 404).
     */
    public const NOT_FOUND = 'error.not_found';

    /**
     * Code for unexpected server failures (HTTP 500).
     */
    public const SERVER_ERROR = 'error.server';

    /**
     * Code for validation failures when provided data is invalid.
     */
    public const VALIDATION_FAILED = 'error.validation';

    /**
     * Code for requests made without proper authentication.
     */
    public const UNAUTHORIZED = 'error.unauthorized';

    /**
     * Code for requests that are authenticated but lack permission.
     */
    public const FORBIDDEN = 'error.forbidden';

    /**
     * Code returned when an order record cannot be located.
     */
    public const ORDER_NOT_FOUND = 'orders.not_found';

    /**
     * Code returned when available inventory cannot satisfy a request.
     */
    public const INVENTORY_INSUFFICIENT = 'inventory.insufficient';

    /**
     * Code returned when the authenticated user's profile cannot be resolved.
     */
    public const PROFILE_UNAVAILABLE = 'profile.unavailable';

    /**
     * Code returned when checkout cannot proceed due to an empty cart.
     */
    public const CHECKOUT_CART_EMPTY = 'checkout.cart_empty';

    /**
     * Registry of error code descriptions keyed by the machine-readable code.
     *
     * @var array<string, string>
     */
    private const DEFINITIONS = [
        self::NOT_FOUND              => 'Resource requested by the client could not be located.',
        self::SERVER_ERROR           => 'Unexpected server exception occurred while handling the request.',
        self::VALIDATION_FAILED      => 'Provided data failed validation checks.',
        self::UNAUTHORIZED           => 'Request lacks valid authentication credentials.',
        self::FORBIDDEN              => 'Authenticated request does not have permission to access the resource.',
        self::ORDER_NOT_FOUND        => 'Requested order record is missing.',
        self::INVENTORY_INSUFFICIENT => 'Inventory could not satisfy the requested quantity.',
        self::PROFILE_UNAVAILABLE    => 'Authenticated user profile is unavailable.',
        self::CHECKOUT_CART_EMPTY    => 'Checkout aborted because the cart does not contain any items.',
    ];

    private function __construct() {}

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
