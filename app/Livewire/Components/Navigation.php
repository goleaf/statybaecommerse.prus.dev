<?php declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Category;
use App\Support\FeatureState;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class Navigation extends Component
{
    public function render()
    {
        $features = config('app-features.features', []);

        $categoryFeature = $features['category'] ?? null;
        $enableCategory = $categoryFeature instanceof FeatureState
            ? $categoryFeature === FeatureState::Enabled
            : (is_string($categoryFeature)
                ? strtolower($categoryFeature) === strtolower(FeatureState::Enabled->value)
                : (bool) $categoryFeature);

        $categories = collect();
        if ($enableCategory && class_exists(Category::class)) {
            $locale = app()->getLocale();
            $cacheKey = "nav:categories:roots:{$locale}";
            $categories = Cache::remember($cacheKey, now()->addMinutes(30), function () {
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

        return view('livewire.components.navigation', [
            'categories' => $categories,
        ]);
    }
}
