{{--
    Shared filter sidebar partial for categories index.
    Variables: $brands, $collections, $activeSearch, $activeBrand, $activeCollection
--}}
<form method="GET" action="{{ route('frontend.categories.index') }}" class="space-y-6">

    {{-- Search --}}
    <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm">
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-700">
            {{ __('messages.search') }}
        </h3>
        <div class="flex rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 focus-within:border-emerald-500 focus-within:ring-1 focus-within:ring-emerald-500">
            <input
                type="search"
                name="q"
                value="{{ $activeSearch }}"
                placeholder="{{ __('frontend.filters.search_placeholder') }}"
                class="flex-1 border-none bg-transparent text-sm text-gray-800 placeholder-gray-400 focus:outline-none"
                aria-label="{{ __('messages.search') }}"
            >
            <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7 7 0 1010 17a7 7 0 006.65-4.35z" />
            </svg>
        </div>
    </div>

    {{-- Brand filter --}}
    @if ($brands->isNotEmpty())
        <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm">
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-700">
                {{ __('frontend.search.brand') }}
            </h3>
            <ul class="space-y-1 text-sm">
                <li>
                    <label class="flex cursor-pointer items-center gap-2 rounded-xl px-2 py-1.5 transition hover:bg-gray-50 {{ $activeBrand === '' ? 'font-semibold text-emerald-700' : 'text-gray-700' }}">
                        <input
                            type="radio"
                            name="brand"
                            value=""
                            @checked($activeBrand === '')
                            class="size-4 accent-emerald-600"
                        >
                        {{ __('frontend.search.all_brands') }}
                    </label>
                </li>
                @foreach ($brands as $brand)
                    <li>
                        <label class="flex cursor-pointer items-center gap-2 rounded-xl px-2 py-1.5 transition hover:bg-gray-50 {{ $activeBrand === $brand->slug ? 'font-semibold text-emerald-700' : 'text-gray-700' }}">
                            <input
                                type="radio"
                                name="brand"
                                value="{{ $brand->slug }}"
                                @checked($activeBrand === $brand->slug)
                                class="size-4 accent-emerald-600"
                            >
                            {{ $brand->name }}
                        </label>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Collection filter --}}
    @if ($collections->isNotEmpty())
        <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm">
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-700">
                {{ __('frontend.search.collections') }}
            </h3>
            <ul class="space-y-1 text-sm">
                <li>
                    <label class="flex cursor-pointer items-center gap-2 rounded-xl px-2 py-1.5 transition hover:bg-gray-50 {{ $activeCollection === '' ? 'font-semibold text-purple-700' : 'text-gray-700' }}">
                        <input
                            type="radio"
                            name="collection"
                            value=""
                            @checked($activeCollection === '')
                            class="size-4 accent-purple-600"
                        >
                        {{ __('ui.all') }}
                    </label>
                </li>
                @foreach ($collections as $collection)
                    <li>
                        <label class="flex cursor-pointer items-center gap-2 rounded-xl px-2 py-1.5 transition hover:bg-gray-50 {{ $activeCollection === $collection->slug ? 'font-semibold text-purple-700' : 'text-gray-700' }}">
                            <input
                                type="radio"
                                name="collection"
                                value="{{ $collection->slug }}"
                                @checked($activeCollection === $collection->slug)
                                class="size-4 accent-purple-600"
                            >
                            {{ $collection->name }}
                        </label>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Actions --}}
    <div class="flex flex-col gap-2">
        <button
            type="submit"
            class="w-full rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
        >
            {{ __('frontend.search.search_action') }}
        </button>

        @if ($activeSearch || $activeBrand || $activeCollection)
            <a
                href="{{ route('frontend.categories.index') }}"
                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-center text-sm font-semibold text-gray-600 shadow-sm transition hover:bg-gray-50"
            >
                {{ __('frontend.search.clear_filters') }}
            </a>
        @endif
    </div>
</form>
