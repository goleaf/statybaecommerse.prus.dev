<?php

declare (strict_types=1);
namespace App\Livewire\Pages\Account;

use App\Models\Review;
use Illuminate\Contracts\View\View;
use Livewire\Component;
/**
 * Reviews
 * 
 * Livewire component for Reviews with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 * @property mixed $reviews
 */
final class Reviews extends Component
{
    public $reviews;
    /**
     * Initialize the Livewire component with parameters.
     * @return void
     */
    public function mount(): void
    {
        $user = auth()->user();
        $this->reviews = collect();
        if ($user) {
            $this->reviews = Review::query()->where('user_id', $user->id)->latest()->limit(200)->get();
        }
    }
    /**
     * Render the Livewire component view with current state.
     * @return View
     */
    public function render(): View
    {
        return view('livewire.pages.account.reviews');
    }
}