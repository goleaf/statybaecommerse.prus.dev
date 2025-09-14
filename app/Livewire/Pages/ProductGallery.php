<?php

declare (strict_types=1);
namespace App\Livewire\Pages;

use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
/**
 * ProductGallery
 * 
 * Livewire component for ProductGallery with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 * @property string $search
 * @property string $filter
 * @property mixed $queryString
 */
final class ProductGallery extends Component
{
    use WithPagination;
    public string $search = '';
    public string $filter = 'all';
    // all, with_images, generated_only
    protected $queryString = ['search' => ['except' => ''], 'filter' => ['except' => 'all']];
    /**
     * Handle updatingSearch functionality with proper error handling.
     * @return void
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }
    /**
     * Handle updatingFilter functionality with proper error handling.
     * @return void
     */
    public function updatingFilter(): void
    {
        $this->resetPage();
    }
    /**
     * Handle products functionality with proper error handling.
     */
    #[Computed]
    public function products()
    {
        return Product::query()->with(['media', 'brand'])->where('is_visible', true)->when($this->search, function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%');
        })->when($this->filter === 'with_images', function ($query) {
            $query->whereHas('media', function ($q) {
                $q->where('collection_name', 'images');
            });
        })->when($this->filter === 'generated_only', function ($query) {
            $query->whereHas('media', function ($q) {
                $q->where('collection_name', 'images')->whereJsonContains('custom_properties->generated', true);
            });
        })->orderBy('created_at', 'desc')->paginate(12);
    }
    /**
     * Handle totalImages functionality with proper error handling.
     * @return int
     */
    #[Computed]
    public function totalImages(): int
    {
        return Product::query()->whereHas('media', function ($q) {
            $q->where('collection_name', 'images');
        })->withCount(['media' => function ($q) {
            $q->where('collection_name', 'images');
        }])->get()->sum('media_count');
    }
    /**
     * Handle generatedImages functionality with proper error handling.
     * @return int
     */
    #[Computed]
    public function generatedImages(): int
    {
        return \Spatie\MediaLibrary\MediaCollections\Models\Media::query()->where('collection_name', 'images')->whereJsonContains('custom_properties->generated', true)->count();
    }
    /**
     * Render the Livewire component view with current state.
     */
    public function render()
    {
        return view('livewire.pages.product-gallery')->layout('components.layouts.base', ['title' => __('translations.product_images')]);
    }
}