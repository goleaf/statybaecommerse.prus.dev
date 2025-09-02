<?php declare(strict_types=1);

namespace App\Enums;

// Legacy Shopper trait removed - using native PHP enum features

/**
 * @method static string Stripe()
 * @method static string NotchPay()
 * @method static string Cash()
 */
enum PaymentType: string
{
    case Stripe = 'stripe';

    case NotchPay = 'notch-pay';

    case Cash = 'cash';
}
