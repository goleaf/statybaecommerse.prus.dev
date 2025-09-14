<?php

declare (strict_types=1);
namespace App\Livewire\Modals;

use App\Actions\CountriesWithZone;
use App\Actions\CountryByZoneData;
use App\Actions\ZoneSessionManager;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Laravelcm\LivewireSlideOvers\SlideOverComponent;
use Livewire\Attributes\Computed;
/**
 * ZoneSelector
 * 
 * Livewire component for ZoneSelector with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 */
class ZoneSelector extends SlideOverComponent
{
    /**
     * Handle panelMaxWidth functionality with proper error handling.
     * @return string
     */
    public static function panelMaxWidth(): string
    {
        return 'lg';
    }
    /**
     * Handle countries functionality with proper error handling.
     * @return Collection
     */
    #[Computed]
    public function countries(): Collection
    {
        return (new CountriesWithZone())->handle();
    }
    /**
     * Handle selectZone functionality with proper error handling.
     * @param int $countryId
     * @return void
     */
    public function selectZone(int $countryId): void
    {
        /** @var CountryByZoneData $selectedZone */
        $selectedZone = $this->countries->firstWhere('countryId', $countryId);
        if ($selectedZone->countryId !== ZoneSessionManager::getSession()?->countryId) {
            ZoneSessionManager::setSession($selectedZone);
            session()->forget('checkout');
            $this->dispatch('zoneChanged');
        }
        $this->redirectIntended();
    }
    /**
     * Handle placeholder functionality with proper error handling.
     * @return string
     */
    public function placeholder(): string
    {
        return <<<'Blade'
            <div class="flex items-center gap-2">
                <x-shopper::skeleton class="w-6 h-5 rounded-none" />
                <x-shopper::skeleton class="w-10 h-3 rounded" />
            </div>
        Blade;
    }
    /**
     * Render the Livewire component view with current state.
     * @return View
     */
    public function render(): View
    {
        return view('livewire.modals.zone-selector');
    }
}