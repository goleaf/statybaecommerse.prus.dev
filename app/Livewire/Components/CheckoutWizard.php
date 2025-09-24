<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Livewire\Components\Checkout\Delivery;
use App\Livewire\Components\Checkout\Payment;
use App\Livewire\Components\Checkout\Shipping;
use Spatie\LivewireWizard\Components\WizardComponent;

/**
 * CheckoutWizard
 *
 * Livewire component for CheckoutWizard with reactive frontend functionality, real-time updates, and user interaction handling.
 */
class CheckoutWizard extends WizardComponent
{
    /**
     * Handle steps functionality with proper error handling.
     */
    public function steps(): array
    {
        return [Shipping::class, Delivery::class, Payment::class];
    }
}
