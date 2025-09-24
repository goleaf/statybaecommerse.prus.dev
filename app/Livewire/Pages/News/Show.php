<?php

declare(strict_types=1);

namespace App\Livewire\Pages\News;

use App\Models\News;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Show
 *
 * Livewire component for Show with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property string $slug
 */
final class Show extends Component
{
    public string $slug;

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(string $slug): void
    {
        $this->slug = $slug;
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        $locale = app()->getLocale();
        $record = News::query()->select('news.*', 't.title as title', 't.summary as summary', 't.content as content', 't.slug as trans_slug')->join('sh_news_translations as t', 't.news_id', '=', 'news.id')->where('t.locale', $locale)->where('t.slug', $this->slug)->firstOrFail();
        abort_unless($record->isPublished(), 404);

        return view('livewire.pages.news.show', ['record' => $record]);
    }
}
