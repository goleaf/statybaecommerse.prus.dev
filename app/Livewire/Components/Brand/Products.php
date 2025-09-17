<?php

declare (strict_types=1);
namespace App\Livewire\Components\Brand;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
/**
 * Products
 * 
 * Livewire component for Products with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 * @property int $brandId
 * @property int $page
 */
class Products extends Component
{
    public int $brandId;
    #[Url]
    public int $page = 1;
    /**
     * Initialize the Livewire component with parameters.
     * @param int $brandId
     * @return void
     */
    public function mount(int $brandId): void
    {
        $this->brandId = $brandId;
    }
    /**
     * Handle products functionality with proper error handling.
     * @return LengthAwarePaginator
     */
    #[Computed]
    public function products(): LengthAwarePaginator
    {
        return Product::query()->select(['id', 'slug', 'name', 'summary', 'brand_id', 'published_at'])->with(['brand:id,slug,name', 'media', 'prices' => function ($pq) {
            $pq->whereRelation('currency', 'code', current_currency());
        }, 'prices.currency:id,code'])->withCount('variants')->where('brand_id', $this->brandId)->where('is_visible', true)->whereNotNull('published_at')->where('published_at', '<=', now())->orderByDesc('published_at')->paginate(12);
    }
    /**
     * Render the Livewire component view with current state.
     * @return View
     */
    public function render(): View
    {
        return view('livewire.components.brand.products');
    }
}