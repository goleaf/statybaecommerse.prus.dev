<?php declare(strict_types=1);

namespace App\Livewire\Home;

use App\Models\Collection as ProductCollection;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class CollectionsShowcase extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    #[Computed]
    public function collections(): EloquentCollection
    {
        $cacheKey = sprintf('home:collections:%s', app()->getLocale());

        return Cache::remember($cacheKey, 300, function () {
            return ProductCollection::query()
                ->with('media')
                ->withCount(['products'])
                ->visible()
                ->active()
                ->ordered()
                ->get();
        });
    }

    public function collectionsSchema(Schema $schema): Schema
    {
        return $schema->components([
            ViewEntry::make('collections')
                ->label('')
                ->view('livewire.home.partials.collections-grid')
                ->viewData(fn(): array => [
                    'collections' => $this->collections(),
                ]),
        ]);
    }

    public function render(): View
    {
        return view('livewire.home.collections-showcase');
    }
}
