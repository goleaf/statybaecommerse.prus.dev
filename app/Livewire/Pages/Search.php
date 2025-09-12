<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.templates.app')]
class Search extends Component
{
    #[Url]
    public string $q = '';

    #[Url]
    public ?string $sort = null;

    public function render(): View
    {
        $locale = app()->getLocale();

        $products = Product::query()
            ->select(['id', 'slug', 'name', 'summary', 'brand_id', 'published_at'])
            ->where('is_visible', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->when($this->q !== '', function ($q) use ($locale) {
                $term = '%'.str_replace(['%', '_'], ['\%', '\_'], $this->q).'%';
                $q->where(function ($w) use ($term, $locale) {
                    $w
                        ->where('name', 'like', $term)
                        ->orWhere('summary', 'like', $term)
                        ->orWhereExists(function ($sq) use ($term, $locale) {
                            $sq
                                ->selectRaw('1')
                                ->from('sh_product_translations as t')
                                ->whereColumn('t.product_id', 'sh_products.id')
                                ->where('t.locale', $locale)
                                ->where(function ($tw) use ($term) {
                                    $tw
                                        ->where('t.name', 'like', $term)
                                        ->orWhere('t.summary', 'like', $term);
                                });
                        });
                });
            })
            ->with([
                'brand:id,slug,name',
                'media',
                'prices' => function ($pq) {
                    $pq->whereRelation('currency', 'code', current_currency());
                },
                'prices.currency:id,code',
            ])
            ->withCount('variants');

        switch ($this->sort) {
            case 'name_asc':
                $products = $products->orderBy('name');
                break;
            case 'name_desc':
                $products = $products->orderByDesc('name');
                break;
            default:
                $products = $products->orderByDesc('published_at');
        }

        $products = $products->paginate(12);

        return view('livewire.pages.search', [
            'products' => $products,
            'term' => $this->q,
        ])->title(__('Search'));
    }
}
