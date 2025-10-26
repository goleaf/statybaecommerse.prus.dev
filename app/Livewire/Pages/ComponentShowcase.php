<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Livewire\Concerns\WithNotifications;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * ComponentShowcase
 *
 * Livewire component for ComponentShowcase with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property string $testInput
 * @property string $testSelect
 * @property bool   $showModal
 * @property-read EloquentCollection<int, Product> $featuredProducts
 * @property-read EloquentCollection<int, Category> $categories
 * @property-read EloquentCollection<int, Brand> $brands
 */
#[Layout('components.layouts.base')]
final class ComponentShowcase extends Component
{
    use WithNotifications;

    public string $testInput = '';

    public string $testSelect = '';

    public bool $showModal = false;

    /**
     * Handle testNotification functionality with proper error handling.
     */
    public function testNotification(string $type): void
    {
        match ($type) {
            'success' => $this->notifySuccess('Success notification!', 'Success'),
            'error'   => $this->notifyError('Error notification!', 'Error'),
            'warning' => $this->notifyWarning('Warning notification!', 'Warning'),
            'info'    => $this->notifyInfo('Info notification!', 'Info'),
            default   => $this->notifyInfo('Info notification!', 'Info'),
        };
    }

    /**
     * Handle toggleModal functionality with proper error handling.
     */
    public function toggleModal(): void
    {
        $this->showModal = ! $this->showModal;
    }

    /**
     * Handle featuredProducts functionality with proper error handling.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Product>
     */
    #[Computed]
    public function featuredProducts(): EloquentCollection
    {
        // Enforce showcase integrity by only surfacing products that are fully configured for the storefront widgets.
        return Product::query()
            ->with(['brand', 'media', 'prices'])
            ->where('is_visible', true)
            ->where('is_featured', true)
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->whereNotNull('price')
            ->where('price', '>', 0)
            ->limit(4)
            ->get();
    }

    /**
     * Handle categories functionality with proper error handling.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Category>
     */
    #[Computed]
    public function categories(): EloquentCollection
    {
        // Filter categories aggressively so the UI never renders incomplete taxonomy data.
        return Category::query()
            ->where('is_visible', true)
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->limit(3)
            ->get();
    }

    /**
     * Handle brands functionality with proper error handling.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Brand>
     */
    #[Computed]
    public function brands(): EloquentCollection
    {
        // Guarantee that only well-defined brands appear in the carousel widgets.
        return Brand::query()
            ->where('is_enabled', true)
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->limit(3)
            ->get();
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.pages.component-showcase', ['featuredProducts' => $this->featuredProducts, 'categories' => $this->categories, 'brands' => $this->brands]);
    }
}
