<?php

declare(strict_types=1);

namespace App\Livewire\Home;

use App\Data\Storefront\Home\CategoryShowcaseItemData;
use App\Models\Category;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\CacheTagHelper;
use App\Support\Cache\CacheTags;
use App\Support\Cache\TagAwareCache;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class CategoriesShowcase extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    #[Computed]
    public function categoryList(): Collection
    {
        $locale = app()->getLocale();
        $cacheKey = CacheKeys::homeCategoryTree($locale);

        $callback = static function () use ($locale): Collection {
            return Category::query()
                ->with(['media', 'translations' => function ($query) use ($locale): void {
                    $query->where('locale', $locale);
                }])
                ->withCount('products')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->map(static function (Category $category) use ($locale): CategoryShowcaseItemData {
                    // Pre-compute the storefront payload to keep cached entries serialisable.
                    return CategoryShowcaseItemData::fromModel($category, $locale);
                });
        };

        $tags = CacheTagHelper::merge(
            CacheTagHelper::categories(),
            CacheTagHelper::locale($locale),
            [CacheTags::home()]
        );

        return TagAwareCache::remember($cacheKey, CacheKeys::TTL_FIVE_MINUTES, $callback, $tags);
    }

    public function categories(Schema $schema): Schema
    {
        return $schema->components([
            ViewEntry::make('categories')
                ->label('')
                ->view('livewire.home.partials.categories-grid')
                ->viewData(fn (): array => [
                    'categories' => $this->categoryList(),
                ]),
        ]);
    }

    public function render(): View
    {
        return view('livewire.home.categories-showcase');
    }
}
