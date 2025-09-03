<?php declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

final class ProductGallery extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filter = 'all'; // all, with_images, generated_only

    protected $queryString = [
        'search' => ['except' => ''],
        'filter' => ['except' => 'all'],
        'page' => ['except' => 1],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function products()
    {
        return Product::query()
            ->with(['media', 'brand'])
            ->where('is_visible', true)
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->filter === 'with_images', function ($query) {
                $query->whereHas('media', function ($q) {
                    $q->where('collection_name', 'images');
                });
            })
            ->when($this->filter === 'generated_only', function ($query) {
                $query->whereHas('media', function ($q) {
                    $q->where('collection_name', 'images')
                      ->whereJsonContains('custom_properties->generated', true);
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(12);
    }

    #[Computed]
    public function totalImages(): int
    {
        return Product::query()
            ->whereHas('media', function ($q) {
                $q->where('collection_name', 'images');
            })
            ->withCount(['media' => function ($q) {
                $q->where('collection_name', 'images');
            }])
            ->get()
            ->sum('media_count');
    }

    #[Computed]
    public function generatedImages(): int
    {
        return \Spatie\MediaLibrary\MediaCollections\Models\Media::query()
            ->where('collection_name', 'images')
            ->whereJsonContains('custom_properties->generated', true)
            ->count();
    }

    public function render()
    {
        return view('livewire.pages.product-gallery')
            ->layout('components.layouts.base', [
                'title' => __('translations.product_images')
            ]);
    }
}
