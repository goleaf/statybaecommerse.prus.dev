<?php declare(strict_types=1);

namespace App\Livewire\Pages\Category;

use App\Models\Category as CategoryModel;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.templates.app')]
class Index extends Component
{
    /**
     * Build a nested category tree with translated names and slugs.
     */
    protected function buildTree(Collection $nodes): Collection
    {
        return $nodes->map(function (CategoryModel $cat) {
            $children = CategoryModel::query()
                ->where('parent_id', $cat->id)
                ->where('is_enabled', true)
                ->orderBy('position')
                ->get();

            return [
                'id' => $cat->id,
                'name' => $cat->trans('name') ?? $cat->name,
                'slug' => $cat->trans('slug') ?? $cat->slug,
                'children' => $children->isNotEmpty() ? $this->buildTree($children) : collect(),
            ];
        });
    }

    public function render(): View
    {
        $locale = app()->getLocale();
        $roots = Cache::remember("categories:roots:{$locale}", now()->addMinutes(60), function () {
            return CategoryModel::query()
                ->whereNull('parent_id')
                ->where('is_enabled', true)
                ->orderBy('position')
                ->get();
        });

        $tree = Cache::remember("categories:tree:{$locale}", now()->addMinutes(60), function () use ($roots) {
            return $this->buildTree($roots);
        });

        return view('livewire.pages.category.index', [
            'roots' => $roots,
            'tree' => $tree,
        ])->title(__('Categories'));
    }
}
