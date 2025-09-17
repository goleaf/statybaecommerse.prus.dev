@php
    $locale = app()->getLocale();
    $legals = \App\Models\Legal::query()->where('is_enabled', true)->orderBy('title')->get();
@endphp
<footer class="border-t mt-12 py-8">
    <div class="container mx-auto px-4 text-sm text-gray-600">
        <nav class="flex flex-wrap gap-4">
            @foreach ($legals as $legal)
                @php
                    $slug = $legal->translations()->where('locale', $locale)->value('slug') ?: $legal->slug;
                    $title = $legal->translations()->where('locale', $locale)->value('title') ?: $legal->title;
                @endphp
                <a href="{{ route('legal.show', ['locale' => $locale, 'slug' => $slug]) }}" class="hover:underline">
                    {{ $title }}
                </a>
            @endforeach
        </nav>
    </div>
    @yield('footer-extra')
</footer>
