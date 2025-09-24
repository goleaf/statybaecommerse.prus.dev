<?php declare(strict_types=1);

namespace App\Livewire\Home;

use App\Livewire\Concerns\WithCart;
use App\Livewire\Concerns\WithNotifications;
use App\Models\Product;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class ProductShelf extends Component implements HasSchemas
{
    use InteractsWithSchemas;
    use WithCart;
    use WithNotifications;

    public string $preset = 'featured';

    public string $title = '';

    public ?string $subtitle = null;

    public int $limit = 8;

    public function mount(string $preset = 'featured', string $title = '', ?string $subtitle = null, int $limit = 8): void
    {
        $this->preset = $preset;
        $this->limit = max(4, $limit);

        $sectionKey = in_array($this->preset, ['latest', 'sale', 'trending', 'featured'], true)
            ? $this->preset
            : 'featured';

        $this->title = $title !== ''
            ? $title
            : __('frontend/home.products.sections.' . $sectionKey . '.title');

        $this->subtitle = $subtitle ?? __('frontend/home.products.sections.' . $sectionKey . '.subtitle');
    }

    #[Computed]
    public function products(): EloquentCollection
    {
        $cacheKey = sprintf('home:shelf:%s:%d:%s', $this->preset, $this->limit, app()->getLocale());

        return Cache::remember($cacheKey, 60, function (): EloquentCollection {
            $query = Product::query()
                ->with(['brand', 'media', 'categories'])
                ->withAvg(['reviews as average_rating' => fn($q) => $q->where('is_approved', true)], 'rating')
                ->withCount(['reviews' => fn($q) => $q->where('is_approved', true)])
                ->where('is_visible', true)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->whereNull('deleted_at');

            $query = match ($this->preset) {
                'latest' => $query->orderByDesc('published_at'),
                'sale' => $query
                    ->where(function ($saleQuery): void {
                        $saleQuery
                            ->whereNotNull('sale_price')
                            ->whereColumn('sale_price', '<', 'price')
                            ->orWhere(function ($compareQuery): void {
                                $compareQuery
                                    ->whereNotNull('compare_price')
                                    ->whereColumn('compare_price', '>', 'price');
                            });
                    })
                    ->orderByDesc('updated_at')
                    ->orderByDesc('published_at'),
                'trending' => $query
                    ->withSum('orderItems as orders_quantity', 'quantity')
                    ->orderByDesc('orders_quantity')
                    ->orderByDesc('reviews_count')
                    ->orderByDesc('published_at'),
                default => $query
                    ->where('is_featured', true)
                    ->orderBy('sort_order')
                    ->orderByDesc('published_at'),
            };

            return $query->limit($this->limit)->get();
        });
    }

    public function productShelf(Schema $schema): Schema
    {
        return $schema->components([
            ViewEntry::make('products')
                ->label('')
                ->view('livewire.home.partials.product-shelf')
                ->viewData(fn(): array => [
                    'products' => $this->products(),
                    'title' => $this->title,
                    'subtitle' => $this->subtitle,
                    'preset' => $this->preset,
                ]),
        ]);
    }

    public function render(): View
    {
        return view('livewire.home.product-shelf');
    }
}
