<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Brand;

use App\Models\Brand;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final /**
 * Show
 * 
 * Livewire component for reactive frontend functionality.
 */
class Show extends Component
{
    public Brand $brand;

    public function mount(string $slug)
    {
        // Find brand by slug (check both main slug and translated slugs)
        $brand = Brand::query()
            ->where('slug', $slug)
            ->where('is_enabled', true)
            ->first();

        if (! $brand) {
            // Try to find by translated slug
            $brand = Brand::query()
                ->whereHas('translations', function ($query) use ($slug) {
                    $query->where('slug', $slug)
                        ->where('locale', app()->getLocale());
                })
                ->where('is_enabled', true)
                ->first();
        }

        if (! $brand) {
            abort(404);
        }

        // Check if we need to redirect to canonical slug
        $canonicalSlug = $this->getCanonicalSlug($brand);
        if ($canonicalSlug !== $slug) {
            $this->redirect(route('localized.brands.show', $canonicalSlug), 301);

            return;
        }

        $this->brand = $brand;
    }

    private function getCanonicalSlug(Brand $brand): string
    {
        // Get translated slug for current locale, fallback to main slug
        $translation = $brand->translations()
            ->where('locale', app()->getLocale())
            ->first();

        return $translation?->slug ?: $brand->slug;
    }

    public function render(): View
    {
        return view('livewire.pages.brand.show', [
            'brand' => $this->brand,
        ]);
    }
}
