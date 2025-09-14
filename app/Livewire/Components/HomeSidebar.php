<?php

declare (strict_types=1);
namespace App\Livewire\Components;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
/**
 * HomeSidebar
 * 
 * Livewire component for HomeSidebar with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 */
final class HomeSidebar extends Component
{
    /**
     * Handle categoryTree functionality with proper error handling.
     */
    #[Computed]
    public function categoryTree()
    {
        $locale = app()->getLocale();
        return Cache::remember("home:category_tree:{$locale}", now()->addMinutes(30), function () {
            $roots = Category::query()->with(['translations' => fn($q) => $q->where('locale', app()->getLocale()), 'children.translations'])->visible()->roots()->ordered()->get();
            $mapNode = function ($cat) use (&$mapNode) {
                return ['id' => $cat->id, 'slug' => method_exists($cat, 'trans') ? $cat->trans('slug') ?? $cat->slug : $cat->slug, 'name' => method_exists($cat, 'trans') ? $cat->trans('name') ?? $cat->name : $cat->name, 'children' => $cat->children->map(fn($ch) => $mapNode($ch))];
            };
            return $roots->map(fn($root) => $mapNode($root));
        });
    }
    /**
     * Render the Livewire component view with current state.
     * @return View
     */
    public function render(): View
    {
        return view('livewire.components.home-sidebar');
    }
}