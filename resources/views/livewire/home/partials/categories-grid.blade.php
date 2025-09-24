@php
    $chunked = $categories->chunk(12);
@endphp

<div class="space-y-12">
    @foreach ($chunked as $index => $chunk)
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 lg:gap-6">
            @foreach ($chunk as $category)
                @php
                    $image = $category->getFirstMediaUrl('images', 'image-lg') ?: $category->getFirstMediaUrl('images');
                    $fallbackColor = match ($category->id % 6) {
                        0 => 'from-indigo-500/70 to-purple-500/70',
                        1 => 'from-blue-500/70 to-cyan-500/70',
                        2 => 'from-amber-500/70 to-orange-500/70',
                        3 => 'from-emerald-500/70 to-teal-500/70',
                        4 => 'from-rose-500/70 to-pink-500/70',
                        default => 'from-slate-500/70 to-slate-700/70',
                    };
                @endphp
                <a href="{{ route('localized.categories.show', ['locale' => app()->getLocale(), 'category' => $category->slug ?? $category]) }}"
                   class="group relative flex flex-col overflow-hidden rounded-3xl border border-white/10 bg-white/5 shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                    <div class="relative aspect-[4/3] overflow-hidden">
                        @if ($image)
                            <img src="{{ $image }}" alt="{{ $category->name }}"
                                 class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                        @else
                            <div
                                 class="h-full w-full bg-gradient-to-br {{ $fallbackColor }} flex items-center justify-center text-white text-4xl font-semibold">
                                {{ mb_substr($category->name, 0, 1) }}
                            </div>
                        @endif

                        <div
                             class="absolute inset-0 bg-gradient-to-t from-slate-900/70 via-slate-900/20 to-transparent opacity-90">
                        </div>

                        <div class="absolute bottom-4 left-4 right-4 space-y-2 text-white">
                            <h3 class="text-lg font-semibold leading-tight line-clamp-2">
                                {{ $category->name }}
                            </h3>
                            <div class="flex items-center justify-between text-xs font-medium text-white/80">
                                <span class="inline-flex items-center gap-1">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M3 7h18M3 12h18M3 17h18" />
                                    </svg>
                                    {{ trans_choice('{0}Нет товаров|{1}1 товар|[2,*]:count товаров', $category->products_count, ['count' => $category->products_count]) }}
                                </span>
                                <span class="inline-flex items-center gap-1 text-white/70">
                                    {{ __('Открыть') }}
                                    <svg class="h-3.5 w-3.5 transition-transform duration-300 group-hover:translate-x-1"
                                         fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </div>

                    @if ($category->short_description)
                        <p class="px-6 pb-6 pt-4 text-sm text-white/70 line-clamp-2">
                            {{ $category->short_description }}
                        </p>
                    @endif
                </a>
            @endforeach
        </div>
    @endforeach
</div>
