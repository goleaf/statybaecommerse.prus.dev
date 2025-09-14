<?php

declare(strict_types=1);

namespace App\Livewire\Pages\News;

use App\Models\News;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final /**
 * Index
 * 
 * Livewire component for reactive frontend functionality.
 */
class Index extends Component
{
    public function render(): View
    {
        $locale = app()->getLocale();

        $query = News::query()
            ->select('news.*')
            ->join('sh_news_translations as t', 't.news_id', '=', 'news.id')
            ->where('t.locale', $locale)
            ->where('news.is_visible', true)
            ->whereNotNull('news.published_at')
            ->where('news.published_at', '<=', now());

        $catSlug = request()->query('cat');
        if (is_string($catSlug) && $catSlug !== '') {
            $query->whereExists(function ($q) use ($locale, $catSlug) {
                $q
                    ->from('news_category_pivot as ncp')
                    ->join('sh_news_category_translations as ct', 'ct.news_category_id', '=', 'ncp.news_category_id')
                    ->whereColumn('ncp.news_id', 'news.id')
                    ->where('ct.locale', $locale)
                    ->where('ct.slug', $catSlug);
            });
        }

        $items = $query->orderByDesc('news.published_at')->paginate(12);

        return view('livewire.pages.news.index', compact('items'));
    }
}
