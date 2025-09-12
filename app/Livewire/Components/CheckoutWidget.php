<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Livewire\Concerns\WithCart;
use App\Livewire\Concerns\WithNotifications;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class CheckoutWidget extends Component
{
    use WithCart, WithNotifications;

    public int $currentStep = 1;

    public int $totalSteps = 4;

    // Customer Information
    #[Validate('required|string|max:255')]
    public string $firstName = '';

    #[Validate('required|string|max:255')]
    public string $lastName = '';

    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('nullable|string|max:20')]
    public string $phone = '';

    // Billing Address
    #[Validate('required|string|max:255')]
    public string $billingAddress = '';

    #[Validate('required|string|max:100')]
    public string $billingCity = '';

    #[Validate('required|string|max:20')]
    public string $billingPostalCode = '';

    #[Validate('required|string|max:100')]
    public string $billingCountry = '';

    // Shipping Address
    public bool $sameAsBilling = true;

    #[Validate('required_if:sameAsBilling,false|string|max:255')]
    public string $shippingAddress = '';

    #[Validate('required_if:sameAsBilling,false|string|max:100')]
    public string $shippingCity = '';

    #[Validate('required_if:sameAsBilling,false|string|max:20')]
    public string $shippingPostalCode = '';

    #[Validate('required_if:sameAsBilling,false|string|max:100')]
    public string $shippingCountry = '';

    // Payment Information
    #[Validate('required|string')]
    public string $paymentMethod = 'card';

    public bool $agreeToTerms = false;

    public bool $subscribeNewsletter = false;

    // Order Summary
    public array $cartItems = [];

    public float $subtotal = 0.0;

    public float $taxAmount = 0.0;

    public float $shippingAmount = 0.0;

    public float $discountAmount = 0.0;

    public float $total = 0.0;

    public function mount(): void
    {
        $this->loadCartData();
    }

    public function render(): View
    {
        return view('livewire.components.advanced-checkout');
    }
}
