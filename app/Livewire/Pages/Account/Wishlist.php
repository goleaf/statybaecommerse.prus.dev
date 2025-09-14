<?php

declare (strict_types=1);
namespace App\Livewire\Pages\Account;

use App\Models\UserWishlist;
use Illuminate\Contracts\View\View;
use Livewire\Component;
/**
 * Wishlist
 * 
 * Livewire component for Wishlist with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 * @property array $wishlists
 */
final class Wishlist extends Component
{
    public array $wishlists = [];
    /**
     * Initialize the Livewire component with parameters.
     * @return void
     */
    public function mount(): void
    {
        $user = auth()->user();
        if ($user) {
            $this->wishlists = UserWishlist::query()->where('user_id', $user->id)->with(['items.product', 'items.variant'])->orderByDesc('is_default')->orderBy('name')->get()->toArray();
        }
    }
    /**
     * Render the Livewire component view with current state.
     * @return View
     */
    public function render(): View
    {
        return view('livewire.pages.account.wishlist');
    }
}