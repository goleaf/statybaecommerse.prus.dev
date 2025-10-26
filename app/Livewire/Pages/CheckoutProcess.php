<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * CheckoutProcess orchestrates the four-step checkout wizard.
 *
 * The component itself only tracks which step should be visible and
 * relays cross-step events (like shipping recalculations) to the
 * specialised Livewire child components responsible for the heavy
 * lifting. Keeping the state lean here prevents duplicated pricing
 * logic and ensures the order summary stays authoritative.
 */
final class CheckoutProcess extends Component
{
    /**
     * Numeric index of the active step.
     *
     * 1 => billing & shipping addresses
     * 2 => delivery option selection
     * 3 => payment method selection
     * 4 => final order review
     */
    public int $step = 1;

    /**
     * Allow the UI to jump to a specific step while clamping the range.
     */
    public function toStep(int $targetStep): void
    {
        // Clamp the target step so we never render beyond the wizard bounds.
        $this->step = max(1, min(4, $targetStep));

        // Notify listening components (like the order summary) that the
        // active step changed, enabling them to adjust their own UI state.
        $this->dispatch('checkout-step-changed', step: $this->step);
    }

    /**
     * When the delivery component persists a shipping option we can move
     * forward to the payment selection automatically.
     */
    #[On('shippingOptionSaved')]
    public function handleShippingSaved(): void
    {
        $this->toStep(3);
    }

    /**
     * React to downstream recalculation events so the order summary knows
     * to refresh its totals (shipping rate changes, discounts, etc.).
     */
    #[On('cart-price-update')]
    #[On('orderTotalsRecalculated')]
    public function handleTotalsChanged(): void
    {
        // Emit a dedicated event consumed by the order summary component
        // which triggers a `$refresh` without forcing a full page rerender.
        $this->dispatch('refreshOrderSummary');
    }

    /**
     * Render the blade template responsible for laying out the wizard.
     */
    public function render(): View
    {
        return view('livewire.pages.checkout-process');
    }
}
