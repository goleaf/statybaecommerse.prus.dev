<?php

declare(strict_types=1);

namespace App\Support;

use ReflectionClass;

/**
 * Defines stable machine-readable error codes for application-level failures.
 *
 * These constants allow controllers, services, and API responses to surface
 * consistent identifiers that can be mapped to localized messages or handled
 * by automated clients.
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
     * Returned when a cart quantity update violates business rules or limits.
     */
    public const E_CART_INVALID_QUANTITY = 'cart.invalid_quantity';

    /**
     * Returned when a cart update fails because the requested stock level is unavailable.
     */
    public const E_CART_STOCK_UNAVAILABLE = 'cart.stock_unavailable';

    /**
     * Returned when a supplied discount or coupon code is unknown.
     */
    public const E_DISCOUNT_CODE_INVALID = 'discount.invalid_code';

    /**
     * Returned when a discount code is known but cannot be redeemed because it has expired.
     */
    public const E_DISCOUNT_CODE_EXPIRED = 'discount.expired';

    /**
     * Returned when a discount code has reached its total or per-customer usage threshold.
     */
    public const E_DISCOUNT_USAGE_LIMIT_REACHED = 'discount.usage_limit_reached';

    /**
     * Returned when a state transition is attempted on an order that violates the workflow.
     */
    public const E_ORDER_STATE_INVALID = 'order.state_invalid';

    /**
     * Returned when a customer attempts to cancel an order that is not eligible for cancellation.
     */
    public const E_ORDER_CANCELLATION_NOT_ALLOWED = 'order.cancellation_not_allowed';

    /**
     * Returned when the payment gateway declines or fails to authorise an order payment attempt.
     */
    public const E_ORDER_PAYMENT_FAILED = 'order.payment_failed';

    /**
     * Returned when an operation expects an open order but the order is already completed.
     */
    public const E_ORDER_ALREADY_COMPLETED = 'order.already_completed';

    /**
     * Returned when the configured payment provider cannot be contacted or responds with an error.
     */
    public const E_PAYMENT_PROVIDER_UNAVAILABLE = 'payment.provider_unavailable';

    /**
     * Returned when the selected payment option is not supported for the current order context.
     */
    public const E_PAYMENT_METHOD_NOT_SUPPORTED = 'payment.method_not_supported';

    /**
     * Returned when the requested inventory level is insufficient for the desired operation.
     */
    public const E_INVENTORY_INSUFFICIENT_STOCK = 'inventory.insufficient_stock';

    /**
     * Returned when stock cannot be allocated because the specified inventory location is unusable.
     */
    public const E_INVENTORY_LOCATION_UNAVAILABLE = 'inventory.location_unavailable';

    /**
     * Returned when a referenced customer account does not exist.
     */
    public const E_CUSTOMER_NOT_FOUND = 'customer.not_found';

    /**
     * Returned when a customer must verify their account before continuing.
     */
    public const E_CUSTOMER_UNVERIFIED = 'customer.unverified';

    /**
     * Returned when authentication fails because supplied credentials are incorrect.
     */
    public const E_AUTH_INVALID_CREDENTIALS = 'auth.invalid_credentials';

    /**
     * Returned when sign-in fails because the account has been locked or suspended.
     */
    public const E_AUTH_ACCOUNT_LOCKED = 'auth.account_locked';

    /**
     * Returned when a shipment cannot be created with the selected shipping method.
     */
    public const E_SHIPPING_METHOD_NOT_AVAILABLE = 'shipping.method_not_available';

    /**
     * Returned when a supplied shipping address fails validation checks.
     */
    public const E_SHIPPING_ADDRESS_INVALID = 'shipping.address_invalid';

    /**
     * Returned when a search query is rejected for being too short to produce meaningful results.
     */
    public const E_SEARCH_QUERY_TOO_SHORT = 'search.query_too_short';

    /**
     * Returned when a search request completes successfully but no results match the criteria.
     */
    public const E_SEARCH_RESULT_NOT_FOUND = 'search.result_not_found';

    /**
     * Returned when an export resource cannot be located or has expired.
     */
    public const E_EXPORT_NOT_FOUND = 'export.not_found';

    /**
     * Returned when the requested notification cannot be found for the acting user.
     */
    public const E_NOTIFICATION_NOT_FOUND = 'notification.not_found';

    /**
     * Retrieve every declared error code as a list for validation or documentation.
     *
     * @return list<string>
     */
    public static function all(): array
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
}
