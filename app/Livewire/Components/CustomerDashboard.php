<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Livewire\Concerns\WithCart;
use App\Livewire\Concerns\WithNotifications;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * CustomerDashboard
 *
 * Livewire component for CustomerDashboard with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property User $user
 */
final class CustomerDashboard extends Component
{
    use WithCart {
        addToCart as performAddToCart;
    }
    use WithNotifications;
    use WithPagination;

    public User $user;

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(): void
    {
        $this->user = auth()->user();
    }

    /**
     * Handle stats functionality with proper error handling.
     */
    #[Computed]
    public function stats(): array
    {
        return [
            'total_orders' => $this->user->orders()->count(),
            // Treat legacy "completed" records as delivered so dashboards remain accurate during migration.
            'completed_orders' => $this->user->orders()->whereIn('status', ['delivered', 'completed'])->count(),
            'pending_orders'   => $this->user->orders()->where('status', 'pending')->count(),
            'total_spent'      => $this->user->orders()->whereIn('status', ['delivered', 'completed'])->sum('total'),
            // Reviews are no longer supported, so keep the legacy stat stable at zero.
            'reviews_written'  => 0,
            'member_since'     => $this->user->created_at->format('Y'),
            'last_order'       => $this->user->orders()->latest()->first()?->created_at?->diffForHumans(),
        ];
    }

    /**
     * Handle recentOrders functionality with proper error handling.
     */
    #[Computed]
    public function recentOrders(): Collection
    {
        return $this->user->orders()->with(['items.product'])->latest()->limit(5)->get();
    }

    /**
     * Handle addToCart functionality with proper error handling.
     */
    public function addToCart(int $productId): void
    {
        $this->performAddToCart($productId, 1, __('ecommerce.added_to_cart'));
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.components.customer-dashboard', [
            'stats'        => $this->stats,
            'recentOrders' => $this->recentOrders,
        ]);
    }
}
