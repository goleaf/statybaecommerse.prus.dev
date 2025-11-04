<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-semibold mb-6">{{ __('frontend.navigation.news') }}</h1>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <aside class="lg:col-span-3">
            <h2 class="text-sm font-semibold text-gray-700 mb-3">{{ __('frontend.navigation.categories') }}</h2>
            <ul class="space-y-2">
                @php
                    $locale = app()->getLocale();
                    $cats = \Illuminate\Support\Facades\DB::table('news_categories')
                        ->join('sh_news_category_translations as ct', 'ct.news_category_id', '=', 'news_categories.id')
                        ->where('ct.locale', $locale)
                        ->where('news_categories.is_visible', true)
                        ->orderBy('news_categories.sort_order')
                        ->select(['news_categories.id', 'ct.name', 'ct.slug'])
                        ->get();
                    $active = request()->query('cat');
                @endphp
                <li>
                    <a href="{{ url()->current() }}"
                       class="text-sm {{ !$active ? 'font-semibold text-gray-900' : 'text-gray-700 hover:text-gray-900' }}">{{ __('shared.all') }}</a>
                </li>
                @foreach ($cats as $cat)
                    @php
                        $url = request()->fullUrlWithQuery(['cat' => $cat->slug]);
                    @endphp
                    <li>
                        <a href="{{ $url }}"
                           class="text-sm {{ $active === $cat->slug ? 'font-semibold text-gray-900' : 'text-gray-700 hover:text-gray-900' }}">{{ $cat->name }}</a>
                    </li>
                @endforeach
            </ul>
        </aside>

        <div class="lg:col-span-9">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach ($items as $item)
                    @php
                        $slug = \Illuminate\Support\Facades\DB::table('sh_news_translations')
                            ->where('news_id', $item->id)
                            ->where('locale', app()->getLocale())
                            ->value('slug');
                    @endphp
                    <a href="{{ app()->getLocale() === 'lt' ? url('/lt/naujienos/' . $slug) : url('/en/news/' . $slug) }}"
                       class="block p-4 border rounded hover:shadow">
                        <h2 class="text-lg font-medium">{{ $item->trans('title') }}</h2>
                        <p class="text-sm text-gray-600 mt-2">{{ $item->trans('summary') }}</p>
                        <p class="text-xs text-gray-500 mt-3">{{ optional($item->published_at)->format('Y-m-d') }}</p>
                    </a>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $items->appends(['cat' => request()->query('cat')])->links() }}
            </div>
        </div>
    </div>
</div>
