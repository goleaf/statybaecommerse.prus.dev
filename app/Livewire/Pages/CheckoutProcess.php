<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class CheckoutProcess extends Component
{
    #[Validate('required|string|max:255')]
    public string $billingFirstName = '';

    #[Validate('required|string|max:255')]
    public string $billingLastName = '';

    #[Validate('required|email|max:255')]
    public string $billingEmail = '';

    #[Validate('required|string|max:255')]
    public string $billingPhone = '';

    #[Validate('required|string|max:255')]
    public string $billingAddress = '';

    #[Validate('required|string|max:255')]
    public string $billingCity = '';

    #[Validate('required|string|max:10')]
    public string $billingPostalCode = '';

    #[Validate('nullable|string|max:255')]
    public string $billingCompany = '';

    public bool $sameAsShipping = true;

    #[Validate('nullable|string|max:255')]
    public string $shippingFirstName = '';

    #[Validate('nullable|string|max:255')]
    public string $shippingLastName = '';

    #[Validate('nullable|string|max:255')]
    public string $shippingAddress = '';

    #[Validate('nullable|string|max:255')]
    public string $shippingCity = '';

    #[Validate('nullable|string|max:10')]
    public string $shippingPostalCode = '';

    #[Validate('nullable|string|max:255')]
    public string $shippingCompany = '';

    #[Validate('nullable|string')]
    public string $notes = '';

    public int $currentStep = 1;

    public function mount(): void
    {
        if (auth()->check()) {
            $user = auth()->user();
            $this->billingFirstName = $user->first_name ?? '';
            $this->billingLastName = $user->last_name ?? '';
            $this->billingEmail = $user->email;
            $this->billingPhone = $user->phone_number ?? '';
        }
    }

    public function nextStep(): void
    {
        $this->validateCurrentStep();

        if ($this->currentStep < 3) {
            $this->currentStep++;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function validateCurrentStep(): void
    {
        match ($this->currentStep) {
            1 => $this->validate([
                'billingFirstName' => 'required|string|max:255',
                'billingLastName' => 'required|string|max:255',
                'billingEmail' => 'required|email|max:255',
                'billingPhone' => 'required|string|max:255',
                'billingAddress' => 'required|string|max:255',
                'billingCity' => 'required|string|max:255',
                'billingPostalCode' => 'required|string|max:10',
            ]),
            2 => $this->sameAsShipping ? null : $this->validate([
                'shippingFirstName' => 'required|string|max:255',
                'shippingLastName' => 'required|string|max:255',
                'shippingAddress' => 'required|string|max:255',
                'shippingCity' => 'required|string|max:255',
                'shippingPostalCode' => 'required|string|max:10',
            ]),
            default => null,
        };
    }

    public function placeOrder(): void
    {
        $this->validate();

        $cartItems = $this->getCartItems();

        if ($cartItems->isEmpty()) {
            $this->addError('cart', 'Jūsų krepšelis tuščias');

            return;
        }

        DB::transaction(function () use ($cartItems) {
            $order = $this->createOrder($cartItems);
            $this->createOrderItems($order, $cartItems);
            $this->clearCart();

            session()->flash('order_number', $order->number);
            $this->redirect(route('order.confirmation', $order->number));
        });
    }

    private function createOrder($cartItems): Order
    {
        $subtotal = $cartItems->sum(fn ($item) => $item->price * $item->quantity);
        $taxAmount = $subtotal * 0.21; // Lithuanian VAT
        $shippingAmount = $subtotal > 100 ? 0 : 5.99; // Free shipping over €100
        $total = $subtotal + $taxAmount + $shippingAmount;

        return Order::create([
            'number' => 'LT-'.strtoupper(uniqid()),
            'user_id' => auth()->id(),
            'status' => 'pending',
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'discount_amount' => 0,
            'total' => $total,
            'currency' => 'EUR',
            'billing_address' => $this->getBillingAddress(),
            'shipping_address' => $this->getShippingAddress(),
            'notes' => $this->notes,
        ]);
    }

    private function createOrderItems(Order $order, $cartItems): void
    {
        foreach ($cartItems as $cartItem) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $cartItem->product_id,
                'product_name' => $cartItem->product->name,
                'product_sku' => $cartItem->product->sku,
                'quantity' => $cartItem->quantity,
                'price' => $cartItem->price,
                'total' => $cartItem->price * $cartItem->quantity,
            ]);
        }
    }

    private function getCartItems()
    {
        return CartItem::with('product')
            ->where('session_id', Session::getId())
            ->get();
    }

    private function clearCart(): void
    {
        CartItem::where('session_id', Session::getId())->delete();
    }

    private function getBillingAddress(): array
    {
        return [
            'first_name' => $this->billingFirstName,
            'last_name' => $this->billingLastName,
            'company' => $this->billingCompany,
            'email' => $this->billingEmail,
            'phone' => $this->billingPhone,
            'address' => $this->billingAddress,
            'city' => $this->billingCity,
            'postal_code' => $this->billingPostalCode,
            'country' => 'Lithuania',
        ];
    }

    private function getShippingAddress(): array
    {
        if ($this->sameAsShipping) {
            return $this->getBillingAddress();
        }

        return [
            'first_name' => $this->shippingFirstName,
            'last_name' => $this->shippingLastName,
            'company' => $this->shippingCompany,
            'address' => $this->shippingAddress,
            'city' => $this->shippingCity,
            'postal_code' => $this->shippingPostalCode,
            'country' => 'Lithuania',
        ];
    }

    public function render(): View
    {
        return view('livewire.pages.checkout-process', [
            'cartItems' => $this->getCartItems(),
            'subtotal' => $this->getCartItems()->sum(fn ($item) => $item->price * $item->quantity),
        ]);
    }
}
