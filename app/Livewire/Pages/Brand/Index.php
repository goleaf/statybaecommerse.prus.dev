<?php declare(strict_types=1);

namespace App\Livewire\Pages\Brand;

use App\Livewire\Shared\BasePageComponent;
use App\Livewire\Concerns\WithFilters;
use App\Models\Brand;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;

final class Index extends BasePageComponent
{
    use WithFilters;

    #[Computed]
    public function brands()
    {
        $query = Brand::query()
            ->with([
                'translations' => function ($q) {
                    $q->where('locale', app()->getLocale());
                },
                'media'
            ])
            ->where('is_enabled', true)
            ->withCount('products');

        // Apply search filter using trait method
        $query = $this->applySearchFilters($query);

        // Apply sorting using trait method
        $query = $this->applySorting($query);

        return $query->paginate(12);
    }

    protected function getPageTitle(): string
    {
        return $this->trans('shared.brands');
    }

    protected function getPageDescription(): ?string
    {
        return __('Browse all our trusted brand partners and discover quality products');
    }

    public function render(): View
    {
        return view('livewire.pages.brand.index')
            ->title(__('translations.brands') . ' - ' . config('app.name'));
    }
}
