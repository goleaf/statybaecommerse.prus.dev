<?php

declare(strict_types=1);

namespace App\Livewire\Home;

use App\Models\Collection as ProductCollection;
use App\Services\Shared\CacheService as SharedCacheService;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\CacheTagHelper;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class CollectionsShowcase extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    #[Computed]
    public function collections(): EloquentCollection
    {
        $locale = app()->getLocale();

        return app(SharedCacheService::class)->rememberLong(
            CacheKeys::homeCollections($locale),
            function () use ($locale) {
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
            },
            CacheKeys::TTL_FIVE_MINUTES,
            CacheTagHelper::collections(),
        );
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
