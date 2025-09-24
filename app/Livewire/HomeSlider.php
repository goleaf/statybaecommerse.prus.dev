<?php declare(strict_types=1);

namespace App\Livewire;

use App\Models\Slider;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class HomeSlider extends Component
{
    public int $currentSlide = 0;

    public bool $autoPlay = true;

    public int $autoPlayInterval = 5000;

    #[Computed]
    public function sliders(): Collection
    {
        $locale = app()->getLocale();

        return Cache::remember("home:sliders:{$locale}", 300, function () use ($locale) {
            return Slider::query()
                ->with(['translations' => fn($q) => $q->where('locale', $locale)])
                ->active()
                ->ordered()
                ->get();
        });
    }

    public function nextSlide(): void
    {
        $maxSlides = $this->sliders->count() - 1;
        $this->currentSlide = $this->currentSlide >= $maxSlides ? 0 : $this->currentSlide + 1;
    }

    public function previousSlide(): void
    {
        $maxSlides = $this->sliders->count() - 1;
        $this->currentSlide = $this->currentSlide <= 0 ? $maxSlides : $this->currentSlide - 1;
    }

    public function goToSlide(int $index): void
    {
        $this->currentSlide = $index;
    }

    public function toggleAutoPlay(): void
    {
        $this->autoPlay = !$this->autoPlay;
    }

    public function render(): View
    {
        return view('livewire.home-slider');
    }
}
