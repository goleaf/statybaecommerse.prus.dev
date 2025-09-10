<aside class="hidden lg:block sticky top-24 space-y-6">
    <div class="rounded-lg border border-gray-200 bg-white p-4">
        <h3 class="mb-3 text-base font-semibold text-gray-900">{{ __('translations.categories') }}</h3>

        <div x-data="{ open: null }" class="space-y-2">
            @foreach ($this->categoryTree as $i => $node)
                <div class="border-b last:border-0">
                    <button type="button" class="w-full flex items-center justify-between py-2 text-left"
                            @click="open === {{ $i }} ? open = null : open = {{ $i }}">
                        <span class="text-sm text-gray-800">{{ $node['name'] }}</span>
                        <svg class="w-4 h-4 text-gray-500" :class="open === {{ $i }} ? 'rotate-180' : ''"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === {{ $i }}" x-collapse class="pb-2">
                        @if (!empty($node['children']) && count($node['children']))
                            <x-category.tree :nodes="collect($node['children'])" />
                        @else
                            <a href="{{ route('category.show', ['category' => $node['slug']]) }}"
                               class="block text-sm text-gray-700 hover:underline ml-2">
                                {{ __('translations.view_all') }}
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</aside>
