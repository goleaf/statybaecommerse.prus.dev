<?php

declare (strict_types=1);
namespace App\Livewire\Pages;

use Darryldecode\Cart\Facades\CartFacade;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
/**
 * Checkout
 * 
 * Livewire component for Checkout with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 * @property string|null $sessionKey
 */
#[Layout('components.layouts.templates.light')]
class Checkout extends Component
{
    public ?string $sessionKey = null;
    /**
     * Initialize the Livewire component with parameters.
     * @return void
     */
    public function mount(): void
    {
        $this->sessionKey = session()->getId();
        // @phpstan-ignore-next-line
        if (CartFacade::session($this->sessionKey)->isEmpty()) {
            if (session()->exists('checkout')) {
                session()->forget('checkout');
            }
            $this->redirect(route('home'), true);
        }
    }
    /**
     * Render the Livewire component view with current state.
     * @return View
     */
    public function render(): View
    {
        return view('livewire.pages.checkout', [
            'items' => CartFacade::session($this->sessionKey)->getContent(),
            // @phpstan-ignore-line
            'subtotal' => CartFacade::session($this->sessionKey)->getSubTotal(),
        ])->title(__('Proceed to checkout'));
    }
}