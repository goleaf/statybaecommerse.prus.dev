<?php declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\Legal as LegalModel;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.templates.light')]
class Legal extends Component
{
    public string $slug;

    public ?LegalModel $page = null;

    public function mount(string $slug): void
    {
        $this->slug = $slug;
        $locale = app()->getLocale();
        $this->page = LegalModel::query()
            ->where('is_enabled', true)
            ->where(function ($q) use ($slug, $locale) {
                $q
                    ->where('slug', $slug)
                    ->orWhereExists(function ($sq) use ($slug, $locale) {
                        $sq
                            ->selectRaw('1')
                            ->from('sh_legal_translations as t')
                            ->whereColumn('t.legal_id', 'sh_legals.id')
                            ->where('t.locale', $locale)
                            ->where('t.slug', $slug);
                    });
            })
            ->first();

        if (is_null($this->page)) {
            $fallback = new LegalModel();
            $fallback->title = __('Legal page');
            $fallback->content = __('This legal page is not available yet.');
            $this->page = $fallback;
        } else {
            $canonical = $this->page->translations()->where('locale', $locale)->value('slug') ?: $this->page->slug;
            if ($canonical && $canonical !== $slug) {
                redirect()->to(route('legal.show', ['locale' => $locale, 'slug' => $canonical]), 301)->send();
                exit;
            }
        }
    }

    public function render(): View
    {
        return view('livewire.pages.legal', [
            'page' => $this->page,
        ])->title($this->page?->title ?? __('Legal'));
    }
}
