@props([
    'paginator',
    'showInfo' => true,
])

@if ($paginator->hasPages())
    <nav class="flex items-center justify-between border-t border-ash/30 bg-dark px-4 py-3 sm:px-6" aria-label="{{ __('Pagination Navigation') }}">
        @if($showInfo)
            <div class="flex flex-1 justify-between sm:hidden">
                @if ($paginator->onFirstPage())
                    <span class="relative inline-flex items-center rounded-md border border-ash/40 bg-dark px-4 py-2 text-sm font-medium text-ash">
                        {{ __('Previous') }}
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center rounded-md border border-ash/40 bg-dark px-4 py-2 text-sm font-medium text-sage hover:bg-[#1a1a1a]">
                        {{ __('Previous') }}
                    </a>
                @endif

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="relative ml-3 inline-flex items-center rounded-md border border-ash/40 bg-dark px-4 py-2 text-sm font-medium text-sage hover:bg-[#1a1a1a]">
                        {{ __('Next') }}
                    </a>
                @else
                    <span class="relative ml-3 inline-flex items-center rounded-md border border-ash/40 bg-dark px-4 py-2 text-sm font-medium text-ash">
                        {{ __('Next') }}
                    </span>
                @endif
            </div>
        @endif

        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            @if($showInfo)
                <div>
                    <p class="text-sm text-ash">
                        {{ __('Showing') }}
                        <span class="font-medium text-sage">{{ $paginator->firstItem() ?? 0 }}</span>
                        {{ __('to') }}
                        <span class="font-medium text-sage">{{ $paginator->lastItem() ?? 0 }}</span>
                        {{ __('of') }}
                        <span class="font-medium text-sage">{{ $paginator->total() }}</span>
                        {{ __('results') }}
                    </p>
                </div>
            @endif

            <div>
                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span class="relative inline-flex items-center rounded-l-md px-2 py-2 text-ash ring-1 ring-inset ring-ash/40 bg-dark">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-ash ring-1 ring-inset ring-ash/40 bg-dark hover:bg-[#1a1a1a]">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="relative z-10 inline-flex items-center bg-sage px-4 py-2 text-sm font-semibold text-dark focus:z-20">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-sage ring-1 ring-inset ring-ash/40 bg-dark hover:bg-[#1a1a1a]">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-ash ring-1 ring-inset ring-ash/40 bg-dark hover:bg-[#1a1a1a]">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @else
                        <span class="relative inline-flex items-center rounded-r-md px-2 py-2 text-ash ring-1 ring-inset ring-ash/40 bg-dark">
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
