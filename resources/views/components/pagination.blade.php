@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination') }}" class="flex items-center justify-center">
        <ul class="inline-flex items-center gap-2">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li>
                    <span
                          class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-200 rounded-md cursor-not-allowed">
                        {{ __('pagination.previous') }}
                    </span>
                </li>
            @else
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                       class="inline-flex items-center px-3 py-2 text-sm font-medium text-primary-700 bg-white border border-gray-200 rounded-md hover:bg-primary-50 hover:border-primary-200">
                        {{ __('pagination.previous') }}
                    </a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- Ellipsis --}}
                @if (is_string($element))
                    <li>
                        <span class="inline-flex items-center px-3 py-2 text-sm text-gray-500">{{ $element }}</span>
                    </li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li>
                                <span
                                      class="inline-flex items-center px-3 py-2 text-sm font-semibold text-white bg-primary-600 border border-primary-600 rounded-md">
                                    {{ $page }}
                                </span>
                            </li>
                        @else
                            <li>
                                <a href="{{ $url }}"
                                   class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-md hover:bg-primary-50 hover:border-primary-200 hover:text-primary-700">
                                    {{ $page }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                       class="inline-flex items-center px-3 py-2 text-sm font-medium text-primary-700 bg-white border border-gray-200 rounded-md hover:bg-primary-50 hover:border-primary-200">
                        {{ __('pagination.next') }}
                    </a>
                </li>
            @else
                <li>
                    <span
                          class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-200 rounded-md cursor-not-allowed">
                        {{ __('pagination.next') }}
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif
