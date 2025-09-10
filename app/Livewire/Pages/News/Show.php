<?php declare(strict_types=1);

namespace App\Livewire\Pages\News;

use App\Models\News;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;

final class Show extends Component
{
    public string $slug;

    public function mount(string $slug): void
    {
        $this->slug = $slug;
    }

    public function render(): View
    {
        $locale = app()->getLocale();

        $record = News::query()
            ->select('news.*', 't.title as title', 't.summary as summary', 't.content as content', 't.slug as trans_slug')
            ->join('sh_news_translations as t', 't.news_id', '=', 'news.id')
            ->where('t.locale', $locale)
            ->where('t.slug', $this->slug)
            ->firstOrFail();

        abort_unless($record->isPublished(), 404);

        return view('livewire.pages.news.show', [
            'record' => $record,
        ]);
    }
}
