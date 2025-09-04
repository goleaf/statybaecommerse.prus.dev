<?php declare(strict_types=1);

namespace App\Livewire\Components;

use App\Livewire\Concerns\WithCart;
use App\Livewire\Concerns\WithNotifications;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class AdvancedCheckout extends Component
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
        
        if (auth()->check()) {
            /** @var User $user */
            $user = auth()->user();
            $this->firstName = $user->first_name ?? '';
            $this->lastName = $user->last_name ?? '';
            $this->email = $user->email;
            $this->phone = $user->phone ?? '';
        }
    }

    public function loadCartData(): void
    {
        $this->cartItems = $this->getCartItems();
        $this->calculateTotals();
    }

    public function calculateTotals(): void
    {
        $this->subtotal = collect($this->cartItems)->sum(fn($item) => $item['price'] * $item['quantity']);
        $this->taxAmount = $this->subtotal * 0.21; // 21% VAT
        $this->shippingAmount = $this->subtotal > 50 ? 0 : 5.99; // Free shipping over €50
        $this->discountAmount = 0; // Apply discount logic here
        $this->total = $this->subtotal + $this->taxAmount + $this->shippingAmount - $this->discountAmount;
    }

    public function nextStep(): void
    {
        $this->validateCurrentStep();
        
        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function goToStep(int $step): void
    {
        if ($step >= 1 && $step <= $this->totalSteps) {
            $this->currentStep = $step;
        }
    }

    public function validateCurrentStep(): void
    {
        match ($this->currentStep) {
            1 => $this->validate([
                'firstName' => 'required|string|max:255',
                'lastName' => 'required|string|max:255',
                'email' => 'required|email|max:255',
            ]),
            2 => $this->validate([
                'billingAddress' => 'required|string|max:255',
                'billingCity' => 'required|string|max:100',
                'billingPostalCode' => 'required|string|max:20',
                'billingCountry' => 'required|string|max:100',
            ]),
            3 => $this->validate([
                'paymentMethod' => 'required|string',
            ]),
            4 => $this->validate([
                'agreeToTerms' => 'accepted',
            ]),
        };
    }

    public function updatedSameAsBilling(): void
    {
        if ($this->sameAsBilling) {
            $this->shippingAddress = $this->billingAddress;
            $this->shippingCity = $this->billingCity;
            $this->shippingPostalCode = $this->billingPostalCode;
            $this->shippingCountry = $this->billingCountry;
        }
    }

    public function placeOrder(): void
    {
        $this->validateCurrentStep();

        if (empty($this->cartItems)) {
            $this->notifyError(__('ecommerce.empty_cart'));
            return;
        }

        try {
            // Create order
            $order = Order::create([
                'number' => 'ORD-' . strtoupper(uniqid()),
                'user_id' => auth()->id(),
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $this->paymentMethod,
                'subtotal' => $this->subtotal,
                'tax_amount' => $this->taxAmount,
                'shipping_amount' => $this->shippingAmount,
                'discount_amount' => $this->discountAmount,
                'total' => $this->total,
                'currency' => 'EUR',
                'billing_address' => [
                    'first_name' => $this->firstName,
                    'last_name' => $this->lastName,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'address' => $this->billingAddress,
                    'city' => $this->billingCity,
                    'postal_code' => $this->billingPostalCode,
                    'country' => $this->billingCountry,
                ],
                'shipping_address' => $this->sameAsBilling ? null : [
                    'address' => $this->shippingAddress,
                    'city' => $this->shippingCity,
                    'postal_code' => $this->shippingPostalCode,
                    'country' => $this->shippingCountry,
                ],
            ]);

            // Create order items
            foreach ($this->cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'name' => $item['name'],
                    'sku' => $item['sku'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total' => $item['price'] * $item['quantity'],
                ]);
            }

            // Clear cart
            $this->clearCart();

            // Redirect to order confirmation
            $this->redirect(route('order.confirmation', $order->number));

        } catch (\Exception $e) {
            $this->notifyError(__('ecommerce.order_creation_failed'));
        }
    }

    public function render(): View
    {
        return view('livewire.components.advanced-checkout');
    }
}
