<?php

declare (strict_types=1);
namespace App\Livewire\Components;

use App\Models\Category;
use App\Support\FeatureState;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;
/**
 * Navigation
 * 
 * Livewire component for Navigation with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 */
class Navigation extends Component
{
    /**
     * Handle categories functionality with proper error handling.
     * @return Collection
     */
    #[Computed]
    public function categories(): Collection
    {
        $features = config('app-features.features', []);
        $categoryFeature = $features['category'] ?? null;
        $enableCategory = $categoryFeature instanceof FeatureState ? $categoryFeature === FeatureState::Enabled : (is_string($categoryFeature) ? strtolower($categoryFeature) === strtolower(FeatureState::Enabled->value) : (bool) $categoryFeature);
        if (!$enableCategory || !class_exists(Category::class)) {
            return collect();
        }
        $locale = app()->getLocale();
        $cacheKey = "nav:categories:roots:{$locale}";
        return Cache::remember($cacheKey, now()->addMinutes(30), function () {
            $query = Category::query();
            if (method_exists(Category::class, 'isRoot')) {
                $query = $query->isRoot();
            } else {
                $query->whereNull('parent_id');
            }
            // Apply enabled scope or fallback column
            if (method_exists(Category::class, 'scopeEnabled')) {
                $query = $query->scopes(['enabled']);
            } else {
                $query->where('is_enabled', true);
            }
            return $query->orderBy('position')->get();
        });
    }
    /**
     * Render the Livewire component view with current state.
     */
    public function render()
    {
        return view('livewire.components.navigation');
    }
}