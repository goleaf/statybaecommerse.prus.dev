<div
     x-data="{
         locales: @js($locales ?? []),
         active: @js($active ?? ($locales[0] ?? 'en')),
         indicators: @js($indicators ?? []),
     }">
    <div class="border-b border-gray-200 dark:border-white/10">
        <div class="flex gap-2">
            @foreach ($locales ?? [] as $loc)
                <button
                        type="button"
                        class="px-3 py-2 text-sm rounded-t border-b-2"
                        :class="active === '{{ $loc }}' ? 'border-primary-600 text-primary-700 dark:text-primary-400' :
                            'border-transparent text-gray-600 dark:text-gray-300'"
                        x-on:click="active='{{ $loc }}'">
                    <span class="inline-flex items-center gap-1">
                        <span class="uppercase">{{ $loc }}</span>
                        <template x-if="indicators['{{ $loc }}'] === 'missing'">
                            <span class="inline-block w-2 h-2 rounded-full bg-red-500"
                                  title="{{ __('translation_missing') }}"></span>
                        </template>
                        <template x-if="indicators['{{ $loc }}'] === 'fallback'">
                            <span class="inline-block w-2 h-2 rounded-full bg-amber-500"
                                  title="{{ __('translation_fallback') }}"></span>
                        </template>
                    </span>
                </button>
            @endforeach
        </div>
    </div>

    <div class="mt-4">
        @foreach ($locales ?? [] as $loc)
            <div x-cloak x-show="active === '{{ $loc }}'">
                @php($locale = $loc)
                {{ $slot }}
            </div>
        @endforeach
    </div>
</div>
