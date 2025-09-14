<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Account;

use App\Models\Review;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final /**
 * Reviews
 * 
 * Livewire component for reactive frontend functionality.
 */
class Reviews extends Component
{
    public $reviews;

    public function mount(): void
    {
        $user = auth()->user();
        $this->reviews = collect();

        if ($user) {
            $this->reviews = Review::query()->where('user_id', $user->id)->latest()->limit(200)->get();
        }
    }

    public function render(): View
    {
        return view('livewire.pages.account.reviews');
    }
}
