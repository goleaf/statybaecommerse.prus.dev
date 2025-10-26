@props([
    'paginator',
    'showInfo' => true,
])

@php
    // Capture the current query parameters so pagination links can carry the active filters forward.
    $queryParameters = request()->query();
    unset($queryParameters['page']);

    // Build a helper closure that appends the preserved query string to any generated pagination URL.
    $appendQueryString = static function (?string $url) use ($queryParameters): ?string {
        if ($url === null) {
            return null; // Gracefully handle disabled links such as the "Previous" button on the first page.
        }

        if ($queryParameters === []) {
            return $url; // Nothing to append when no filters are active.
        }

        $queryString = http_build_query($queryParameters);

        return $url . (str_contains($url, '?') ? '&' : '?') . $queryString;
    };
@endphp

@if ($paginator->hasPages())
    <nav class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 dark:border-gray-700 dark:bg-gray-800" aria-label="{{ __('Pagination Navigation') }}">
        @if($showInfo)
            <div class="flex flex-1 justify-between sm:hidden">
                @if ($paginator->onFirstPage())
                    <span class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400">
                        {{ __('Previous') }}
                    </span>
                @else
                    <a href="{{ $appendQueryString($paginator->previousPageUrl()) }}" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                        {{ __('Previous') }}
                    </a>
                @endif

                @if ($paginator->hasMorePages())
                    <a href="{{ $appendQueryString($paginator->nextPageUrl()) }}" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                        {{ __('Next') }}
                    </a>
                @else
                    <span class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400">
                        {{ __('Next') }}
                    </span>
                @endif
            </div>
        @endif

        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            @if($showInfo)
                <div>
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        {{ __('Showing') }}
                        <span class="font-medium">{{ $paginator->firstItem() ?? 0 }}</span>
                        {{ __('to') }}
                        <span class="font-medium">{{ $paginator->lastItem() ?? 0 }}</span>
                        {{ __('of') }}
                        <span class="font-medium">{{ $paginator->total() }}</span>
                        {{ __('results') }}
                    </p>
                </div>
            @endif

            <div>
                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 dark:ring-gray-600 dark:text-gray-500">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    @else
                        <a href="{{ $appendQueryString($paginator->previousPageUrl()) }}" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 dark:ring-gray-600 dark:text-gray-400 dark:hover:bg-gray-700">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="relative z-10 inline-flex items-center bg-blue-600 px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $appendQueryString($url) }}" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 dark:text-gray-300 dark:ring-gray-600 dark:hover:bg-gray-700">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $appendQueryString($paginator->nextPageUrl()) }}" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 dark:ring-gray-600 dark:text-gray-400 dark:hover:bg-gray-700">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @else
                        <span class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 dark:ring-gray-600 dark:text-gray-500">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    @endif
                </nav>
            </div>
        </div>
    </nav>
@endif
