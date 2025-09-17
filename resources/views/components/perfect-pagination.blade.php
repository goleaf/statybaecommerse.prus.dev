@props([
    'paginator' => null,
    'showInfo' => true,
    'showPerPage' => true,
    'perPageOptions' => [12, 24, 48, 96],
    'onEachSide' => 2,
])

@php
    $paginator = $paginator ?? request()->get('paginator');
    $currentPage = $paginator->currentPage() ?? 1;
    $lastPage = $paginator->lastPage() ?? 1;
    $perPage = $paginator->perPage() ?? 12;
    $total = $paginator->total() ?? 0;
    $from = $paginator->firstItem() ?? 0;
    $to = $paginator->lastItem() ?? 0;
    
    // Get onEachSide from paginator or use default
    $onEachSide = $paginator->onEachSide ?? $onEachSide ?? 2;
    
    // Calculate the range based on onEachSide
    $start = max(1, $currentPage - $onEachSide);
    $end = min($lastPage, $currentPage + $onEachSide);
    
    // Adjust range if we're near the beginning or end
    if ($end - $start < ($onEachSide * 2)) {
        if ($start == 1) {
            $end = min($lastPage, $start + ($onEachSide * 2));
        } else {
            $start = max(1, $end - ($onEachSide * 2));
        }
    }
@endphp

@if ($lastPage > 1)
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
        {{-- Pagination Info --}}
        @if ($showInfo)
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
                <div class="text-sm text-gray-700 mb-4 sm:mb-0">
                    {{ __('Showing') }}
                    <span class="font-medium text-gray-900">{{ $from }}</span>
                    {{ __('to') }}
                    <span class="font-medium text-gray-900">{{ $to }}</span>
                    {{ __('of') }}
                    <span class="font-medium text-gray-900">{{ $total }}</span>
                    {{ __('results') }}
                </div>

                {{-- Per Page Selector --}}
                @if ($showPerPage)
                    <div class="flex items-center gap-2">
                        <label for="per-page" class="text-sm text-gray-700">{{ __('Show') }}:</label>
                        <select id="per-page"
                                x-data="{ perPage: {{ $perPage }} }"
                                @change="window.location.href = updateUrlParam('per_page', $event.target.value)"
                                class="px-3 py-1 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            @foreach ($perPageOptions as $option)
                                <option value="{{ $option }}" {{ $perPage == $option ? 'selected' : '' }}>
                                    {{ $option }} {{ __('per page') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
        @endif

        {{-- Pagination Navigation --}}
        <nav class="flex items-center justify-center" aria-label="{{ __('Pagination') }}">
            <div class="flex items-center space-x-1">
                {{-- Previous Page --}}
                @if ($currentPage > 1)
                    <a href="{{ $paginator->previousPageUrl() }}"
                       class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-blue-600 hover:border-blue-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        {{ __('Previous') }}
                    </a>
                @else
                    <span class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        {{ __('Previous') }}
                    </span>
                @endif

                {{-- Page Numbers --}}
                <div class="flex items-center space-x-1">
                    {{-- First page --}}
                    @if ($start > 1)
                        <a href="{{ $paginator->url(1) }}"
                           class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-blue-600 hover:border-blue-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            1
                        </a>
                        @if ($start > 2)
                            <span class="px-3 py-2 text-sm font-medium text-gray-500">...</span>
                        @endif
                    @endif

                    {{-- Page range --}}
                    @for ($i = $start; $i <= $end; $i++)
                        @if ($i == $currentPage)
                            <span class="px-3 py-2 text-sm font-medium text-white bg-blue-600 border border-blue-600 rounded-lg shadow-sm">
                                {{ $i }}
                            </span>
                        @else
                            <a href="{{ $paginator->url($i) }}"
                               class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-blue-600 hover:border-blue-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                {{ $i }}
                            </a>
                        @endif
                    @endfor

                    {{-- Last page --}}
                    @if ($end < $lastPage)
                        @if ($end < $lastPage - 1)
                            <span class="px-3 py-2 text-sm font-medium text-gray-500">...</span>
                        @endif
                        <a href="{{ $paginator->url($lastPage) }}"
                           class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-blue-600 hover:border-blue-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            {{ $lastPage }}
                        </a>
                    @endif
                </div>

                {{-- Next Page --}}
                @if ($currentPage < $lastPage)
                    <a href="{{ $paginator->nextPageUrl() }}"
                       class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-blue-600 hover:border-blue-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        {{ __('Next') }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                @else
                    <span class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">
                        {{ __('Next') }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </span>
                @endif
            </div>
        </nav>

        {{-- Quick Jump --}}
        <div class="mt-6 flex items-center justify-center gap-2">
            <span class="text-sm text-gray-700">{{ __('Go to page') }}:</span>
            <input type="number"
                   min="1"
                   max="{{ $lastPage }}"
                   x-data="{ page: {{ $currentPage }} }"
                   x-model="page"
                   @keydown.enter="if(page >= 1 && page <= {{ $lastPage }}) window.location.href = '{{ $paginator->url(1) }}'.replace('page=1', 'page=' + page)"
                   class="w-16 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
            <button @click="if(page >= 1 && page <= {{ $lastPage }}) window.location.href = '{{ $paginator->url(1) }}'.replace('page=1', 'page=' + page)"
                    class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                {{ __('Go') }}
            </button>
        </div>
    </div>
@endif

<script>
    function updateUrlParam(param, value) {
        const url = new URL(window.location);
        if (value) {
            url.searchParams.set(param, value);
        } else {
            url.searchParams.delete(param);
        }
        return url.toString();
    }
</script>
