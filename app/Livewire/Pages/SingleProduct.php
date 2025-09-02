<?php declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * @property-read ProductVariant $selectedVariant
 */
#[Layout('layouts.templates.app')]
class SingleProduct extends Component
{
    public ?Product $product = null;

    public function mount(string $slug): void
    {
        $locale = app()->getLocale();
        $product = Product::with([
            'media',
            'prices' => function ($query): void {
                $query->whereRelation('currency', 'code', current_currency());
            },
            'prices.currency',
            'inventoryHistories',
            'categories' => function ($query): void {
                $query->select('id', 'name');
            }
        ])
            ->withCount('variants')
            ->where(function ($q) use ($slug, $locale) {
                $q
                    ->where('slug', $slug)
                    ->orWhereExists(function ($sq) use ($slug, $locale) {
                        $sq
                            ->selectRaw('1')
                            ->from('sh_product_translations as t')
                            ->whereColumn('t.product_id', 'sh_products.id')
                            ->where('t.locale', $locale)
                            ->where('t.slug', $slug);
                    });
            })
            ->firstOrFail();

        abort_unless($product->isPublished(), 404);

        $canonical = $product->translations()->where('locale', $locale)->value('slug') ?: $product->slug;
        if ($canonical && $canonical !== $slug) {
            redirect()->to(route('product.show', ['locale' => $locale, 'slug' => $canonical]), 301)->send();
            exit;
        }

        $this->product = $product;
    }

    public function render(): View
    {
        return view('livewire.pages.single-product')
            ->title($this->product->trans('name') ?? $this->product->name);
    }
}
