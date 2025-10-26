<?php

declare(strict_types=1);

namespace App\Livewire\Home;

use App\Models\Collection as ProductCollection;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\CacheTagHelper;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Cache\TaggableStore;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

final class CollectionsShowcase extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    /**
     * Resolve curated collections for the storefront grid while respecting the
     * underlying cache invalidation hooks for locale-aware payloads.
     */
    public function collections(): EloquentCollection
    {
        $locale = app()->getLocale();

        $store = Cache::getStore();

        $callback = function () use ($locale) {
            return ProductCollection::query()
                ->with('media')
                ->with(['translations' => function ($q) use ($locale) {
                    $q->where('locale', $locale);
                }])
                ->withCount(['products'])
                ->visible()
                ->active()
                ->ordered()
                ->get();
        };

        if ($store instanceof TaggableStore) {
            return Cache::tags(CacheTagHelper::merge(CacheTagHelper::collections(), CacheTagHelper::locale($locale)))
                ->remember(CacheKeys::homeCollections($locale), CacheKeys::TTL_FIVE_MINUTES, $callback);
        }

        return Cache::remember(CacheKeys::homeCollections($locale), CacheKeys::TTL_FIVE_MINUTES, $callback);
    }

    /**
     * Allow Livewire's property-style access (`$this->collections`) to reuse the
     * method above so tests and templates consistently hit the caching layer.
     */
    public function getCollectionsProperty(): EloquentCollection
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
