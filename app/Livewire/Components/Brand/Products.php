<?php declare(strict_types=1);

namespace App\Livewire\Components\Brand;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

class Products extends Component
{
    public int $brandId;

    #[Url]
    public int $page = 1;

    public function mount(int $brandId): void
    {
        $this->brandId = $brandId;
    }

    public function getProductsProperty(): LengthAwarePaginator
    {
        return Product::query()
            ->select(['id', 'slug', 'name', 'summary', 'brand_id', 'published_at'])
            ->with([
                'brand:id,slug,name',
                'media',
                'prices' => function ($pq) {
                    $pq->whereRelation('currency', 'code', current_currency());
                },
                'prices.currency:id,code',
            ])
            ->withCount('variants')
            ->where('brand_id', $this->brandId)
            ->where('is_visible', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at')
            ->paginate(12);
    }

    public function render(): View
    {
        return view('livewire.components.brand.products', [
            'products' => $this->products,
        ]);
    }
}
