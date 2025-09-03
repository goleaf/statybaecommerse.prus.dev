<?php declare(strict_types=1);

namespace App\Livewire\Shared;

use App\Livewire\Concerns\WithCart;
use App\Livewire\Concerns\WithNotifications;
use App\Services\Shared\CacheService;
use App\Services\Shared\TranslationService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.templates.app')]
abstract class BasePageComponent extends Component
{
    use WithCart, WithNotifications;

    protected CacheService $cacheService;
    protected TranslationService $translationService;

    public function boot(): void
    {
        $this->cacheService = app(CacheService::class);
        $this->translationService = app(TranslationService::class);
    }

    /**
     * Get the page title for SEO
     */
    abstract protected function getPageTitle(): string;

    /**
     * Get the page description for SEO
     */
    protected function getPageDescription(): ?string
    {
        return null;
    }

    /**
     * Get the page keywords for SEO
     */
    protected function getPageKeywords(): ?string
    {
        return null;
    }

    /**
     * Get cache key for this component
     */
    protected function getCacheKey(string $suffix = ''): string
    {
        $class = class_basename(static::class);
        $locale = app()->getLocale();
        $currency = current_currency();
        
        return strtolower("{$class}.{$locale}.{$currency}.{$suffix}");
    }

    /**
     * Cache data with automatic key generation
     */
    protected function cache(string $key, callable $callback, int $ttl = 3600): mixed
    {
        return $this->cacheService->rememberDefault($this->getCacheKey($key), $callback, $ttl);
    }

    /**
     * Get localized translation
     */
    protected function trans(string $key, array $replace = []): string
    {
        return $this->translationService->getTranslation($key, app()->getLocale(), $replace);
    }

    /**
     * Handle wishlist toggle
     */
    public function toggleWishlist(int $productId): void
    {
        if (!auth()->check()) {
            $this->redirect(route('login'));
            return;
        }

        $user = auth()->user();
        $wishlist = $user->wishlist ?? [];

        if (in_array($productId, $wishlist)) {
            $wishlist = array_values(array_filter($wishlist, fn($id) => $id !== $productId));
            $this->notifySuccess($this->trans('shared.product_removed_from_wishlist'));
        } else {
            $wishlist[] = $productId;
            $this->notifySuccess($this->trans('shared.product_added_to_wishlist'));
        }

        $user->update(['wishlist' => $wishlist]);
        $this->dispatch('wishlist-updated');
    }

    /**
     * Handle comparison toggle
     */
    public function addToCompare(int $productId): void
    {
        $compareProducts = session('compare_products', []);
        
        if (count($compareProducts) >= 4) {
            $this->notifyWarning($this->trans('shared.compare_limit_reached', ['max' => 4]));
            return;
        }

        if (in_array($productId, $compareProducts)) {
            $this->notifyInfo($this->trans('shared.product_already_in_comparison'));
            return;
        }

        $compareProducts[] = $productId;
        session(['compare_products' => $compareProducts]);

        $this->notifySuccess($this->trans('shared.product_added_to_comparison'));
        $this->dispatch('compare-updated');
    }

    /**
     * Get common meta data for the page
     */
    protected function getMetaData(): array
    {
        return [
            'title' => $this->getPageTitle(),
            'description' => $this->getPageDescription(),
            'keywords' => $this->getPageKeywords(),
            'canonical' => url()->current(),
            'locale' => app()->getLocale(),
        ];
    }

    /**
     * Render the component view
     */
    abstract public function render(): View;
}
