<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Account;

use App\Models\UserWishlist;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final /**
 * Wishlist
 * 
 * Livewire component for reactive frontend functionality.
 */
class Wishlist extends Component
{
    public array $wishlists = [];

    public function mount(): void
    {
        $user = auth()->user();
        if ($user) {
            $this->wishlists = UserWishlist::query()
                ->where('user_id', $user->id)
                ->with(['items.product', 'items.variant'])
                ->orderByDesc('is_default')
                ->orderBy('name')
                ->get()
                ->toArray();
        }
    }

    public function render(): View
    {
        return view('livewire.pages.account.wishlist');
    }
}
