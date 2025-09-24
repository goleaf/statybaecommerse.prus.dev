<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Livewire\Concerns\WithNotifications;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
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
 * @property bool $showModal
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
            'error' => $this->notifyError('Error notification!', 'Error'),
            'warning' => $this->notifyWarning('Warning notification!', 'Warning'),
            'info' => $this->notifyInfo('Info notification!', 'Info'),
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
     */
    #[Computed]
    public function featuredProducts(): Collection
    {
        return Product::query()->with(['brand', 'media', 'prices'])->where('is_visible', true)->where('is_featured', true)->limit(4)->get()->skipWhile(function ($product) {
            // Skip products that are not properly configured for showcase display
            return empty($product->name) || ! $product->is_visible || ! $product->is_featured || $product->price <= 0 || empty($product->slug);
        });
    }

    /**
     * Handle categories functionality with proper error handling.
     */
    #[Computed]
    public function categories(): Collection
    {
        return Category::query()->where('is_visible', true)->limit(3)->get()->skipWhile(function ($category) {
            // Skip categories that are not properly configured for showcase display
            return empty($category->name) || ! $category->is_visible || empty($category->slug);
        });
    }

    /**
     * Handle brands functionality with proper error handling.
     */
    #[Computed]
    public function brands(): Collection
    {
        return Brand::query()->where('is_enabled', true)->limit(3)->get()->skipWhile(function ($brand) {
            // Skip brands that are not properly configured for showcase display
            return empty($brand->name) || ! $brand->is_enabled || empty($brand->slug);
        });
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.pages.component-showcase', ['featuredProducts' => $this->featuredProducts, 'categories' => $this->categories, 'brands' => $this->brands]);
    }
}
