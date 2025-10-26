<?php

declare(strict_types=1);

namespace App\Livewire\Home;

use App\Data\Storefront\Home\CollectionShowcaseItemData;
use App\Models\Collection as ProductCollection;
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

final class CollectionsShowcase extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    /**
     * Resolve curated collections for the storefront grid while respecting the
     * underlying cache invalidation hooks for locale-aware payloads.
     */
    #[Computed]
    public function collections(): Collection
    {
        $locale = app()->getLocale();

        $cacheKey = CacheKeys::homeCollections($locale);

        $callback = static function () use ($locale): Collection {
            return ProductCollection::query()
                ->with(['media', 'translations' => function ($q) use ($locale) {
                    $q->where('locale', $locale);
                }])
                ->withCount(['products'])
                ->visible()
                ->active()
                ->ordered()
                ->get()
                ->map(static function (ProductCollection $collection) use ($locale): CollectionShowcaseItemData {
                    // Convert collection models into serialisable DTOs for the cached payload.
                    return CollectionShowcaseItemData::fromModel($collection, $locale);
                });
        };

        $tags = CacheTagHelper::merge(
            CacheTagHelper::collections(),
            CacheTagHelper::locale($locale),
            [CacheTags::home()]
        );

        return TagAwareCache::remember($cacheKey, CacheKeys::TTL_FIVE_MINUTES, $callback, $tags);
    }

    /**
     * Maintain support for property-style access used in older Blade snippets.
     */
    public function getCollectionsProperty(): Collection
    {
        return $this->collections();
    }

    public function collectionsSchema(Schema $schema): Schema
    {
        return $schema->components([
            ViewEntry::make('collections')
                ->label('')
                ->view('livewire.home.partials.collections-grid')
                ->viewData(fn (): array => [
                    'collections' => $this->collections(),
                ]),
        ]);
    }

    public function render(): View
    {
        return view('livewire.home.collections-showcase');
    }
}
