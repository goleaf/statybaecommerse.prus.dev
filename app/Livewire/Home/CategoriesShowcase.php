<?php declare(strict_types=1);

namespace App\Livewire\Home;

use App\Models\Category;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class CategoriesShowcase extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    #[Computed]
    public function categoryList(): EloquentCollection
    {
        return Category::query()
            ->with('media')
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function categories(Schema $schema): Schema
    {
        return $schema->components([
            ViewEntry::make('categories')
                ->label('')
                ->view('livewire.home.partials.categories-grid')
                ->viewData(fn(): array => [
                    'categories' => $this->categoryList(),
                ]),
        ]);
    }

    public function render(): View
    {
        return view('livewire.home.categories-showcase');
    }
}
