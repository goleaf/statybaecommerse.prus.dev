<?php declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Livewire\Concerns\WithNotifications;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.templates.app')]
final class ComponentShowcase extends Component
{
    use WithNotifications;

    public string $testInput = '';
    public string $testSelect = '';
    public bool $showModal = false;

    public function testNotification(string $type): void
    {
        match($type) {
            'success' => $this->notifySuccess('Success notification!', 'Success'),
            'error' => $this->notifyError('Error notification!', 'Error'),
            'warning' => $this->notifyWarning('Warning notification!', 'Warning'),
            'info' => $this->notifyInfo('Info notification!', 'Info'),
        };
    }

    public function toggleModal(): void
    {
        $this->showModal = !$this->showModal;
    }

    public function getFeaturedProductsProperty()
    {
        return Product::query()
            ->with(['brand', 'media', 'prices'])
            ->where('is_visible', true)
            ->where('is_featured', true)
            ->limit(4)
            ->get();
    }

    public function getCategoriesProperty()
    {
        return Category::query()
            ->where('is_visible', true)
            ->limit(3)
            ->get();
    }

    public function getBrandsProperty()
    {
        return Brand::query()
            ->where('is_enabled', true)
            ->limit(3)
            ->get();
    }

    public function render(): View
    {
        return view('livewire.pages.component-showcase');
    }
}
