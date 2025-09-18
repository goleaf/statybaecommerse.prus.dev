<div class="relative">
    <div class="flex gap-6 overflow-x-auto pb-4 snap-x snap-mandatory scrollbar-thin scrollbar-thumb-white/20">
        @foreach ($collections as $collection)
            @php
                $image = $collection->getBannerUrl('lg') ?: $collection->getImageUrl('lg') ?: $collection->getImageUrl();
                $fallbackColor = match ($collection->id % 5) {
                    0 => 'from-fuchsia-500/70 via-purple-500/70 to-indigo-500/70',
                    1 => 'from-sky-500/70 via-cyan-500/70 to-blue-500/70',
                    2 => 'from-lime-500/70 via-emerald-500/70 to-teal-500/70',
                    3 => 'from-amber-500/70 via-orange-500/70 to-rose-500/70',
                    default => 'from-slate-600/70 via-slate-700/70 to-slate-800/70',
                };
            @endphp
            <a href="{{ route('collections.show', $collection) }}"
               class="group relative flex w-80 min-w-[20rem] max-w-xs snap-start flex-col overflow-hidden rounded-3xl border border-white/10 bg-white/5 shadow-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                <div class="relative h-56 w-full overflow-hidden">
                    @if ($image)
                        <img src="{{ $image }}" alt="{{ $collection->name }}"
                             class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                    @else
                        <div class="h-full w-full bg-gradient-to-br {{ $fallbackColor }} flex items-center justify-center text-3xl font-semibold text-white">
                            {{ mb_substr($collection->name, 0, 2) }}
                        </div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/20 to-transparent"></div>
                    <div class="absolute bottom-5 left-5 right-5 space-y-2 text-white">
                        <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">
                            {{ __('Коллекция') }}
                        </span>
                        <h3 class="text-xl font-semibold leading-tight line-clamp-2">
                            {{ $collection->getTranslatedName() ?? $collection->name }}
                        </h3>
                    </div>
                </div>
                <div class="space-y-3 px-6 py-6">
                    @if ($collection->getTranslatedDescription())
                        <p class="text-sm text-white/70 line-clamp-3">
                            {{ $collection->getTranslatedDescription() }}
                        </p>
                    @endif
                    <div class="flex items-center justify-between text-xs font-medium text-white/70">
                        <span class="inline-flex items-center gap-1">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 12h18M3 17h18" />
                            </svg>
                            {{ trans_choice('{0}Нет товаров|{1}1 товар|[2,*]:count товаров', $collection->products_count, ['count' => $collection->products_count]) }}
                        </span>
                        <span class="inline-flex items-center gap-1 text-white/80">
                            {{ __('Открыть подборку') }}
                            <svg class="h-3.5 w-3.5 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</div>
