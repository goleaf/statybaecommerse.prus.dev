{{-- Mobile-Responsive Table Component for Filament Admin --}}
@props([
    'records' => [],
    'columns' => [],
    'actions' => [],
    'bulkActions' => [],
    'searchable' => true,
    'filterable' => true,
])

<div class="fi-mobile-table-container">
    {{-- Mobile Table Header --}}
    <div class="fi-mobile-table-header bg-white border-b border-gray-200 p-4 lg:hidden">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">
                {{ $title ?? __('messages.admin) }}
            </h3>
            <div class="flex items-center space-x-2">
                {{-- Mobile Search Toggle --}}
                @if($searchable)
                    <button 
                        type="button"
                        class="p-2 text-gray-400 hover:text-gray-600 rounded-md"
                        onclick="toggleMobileSearch()"
                        aria-label="{{ __('admin.table.toggle_search') }}"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                @endif

                {{-- Mobile Filter Toggle --}}
                @if($filterable)
                    <button 
                        type="button"
                        class="p-2 text-gray-400 hover:text-gray-600 rounded-md"
                        onclick="toggleMobileFilters()"
                        aria-label="{{ __('admin.table.toggle_filters') }}"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
                        </svg>
                    </button>
                @endif

                {{-- Mobile View Toggle --}}
                <button 
                    type="button"
                    class="p-2 text-gray-400 hover:text-gray-600 rounded-md"
                    onclick="toggleMobileView()"
                    aria-label="{{ __('admin.table.toggle_view') }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Search Bar --}}
        @if($searchable)
            <div id="mobile-search-bar" class="hidden mb-4">
                <div class="relative">
                    <input 
                        type="text"
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="{{ __('admin.table.search_placeholder') }}"
                        id="mobile-search-input"
                    >
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        @endif

        {{-- Mobile Filters --}}
        @if($filterable)
            <div id="mobile-filters" class="hidden space-y-3">
                <div class="grid grid-cols-1 gap-3">
                    {{-- Filter components would be dynamically inserted here --}}
                    <select class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">{{ __('admin.table.all_statuses') }}</option>
                        <option value="active">{{ __('messages.admin) }}</option>
                        <option value="inactive">{{ __('messages.admin) }}</option>
                    </select>
                </div>
                <div class="flex justify-between">
                    <button 
                        type="button"
                        class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900"
                        onclick="clearMobileFilters()"
                    >
                        {{ __('admin.table.clear_filters') }}
                    </button>
                    <button 
                        type="button"
                        class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700"
                        onclick="applyMobileFilters()"
                    >
                        {{ __('admin.table.apply_filters') }}
                    </button>
                </div>
            </div>
        @endif
    </div>

    {{-- Desktop Table (hidden on mobile) --}}
    <div class="hidden lg:block">
        <div class="fi-table-container overflow-x-auto">
            {{ $slot }}
        </div>
    </div>

    {{-- Mobile Card View --}}
    <div id="mobile-card-view" class="lg:hidden">
        @if(count($records) > 0)
            <div class="space-y-4 p-4">
                @foreach($records as $record)
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                        {{-- Card Header --}}
                        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    @if(count($bulkActions) > 0)
                                        <input 
                                            type="checkbox"
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                            value="{{ $record->id }}"
                                        >
                                    @endif
                                    <h4 class="text-sm font-medium text-gray-900">
                                        {{ $record->name ?? $record->title ?? "#" . $record->id }}
                                    </h4>
                                </div>
                                @if(count($actions) > 0)
                                    <div class="flex items-center space-x-1">
                                        <button 
                                            type="button"
                                            class="p-1 text-gray-400 hover:text-gray-600 rounded"
                                            onclick="toggleMobileActions({{ $record->id }})"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Card Content --}}
                        <div class="px-4 py-3">
                            <dl class="space-y-2">
                                @foreach($columns as $column)
                                    @if(isset($record->{$column['key']}))
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-gray-500">
                                                {{ $column['label'] }}
                                            </dt>
                                            <dd class="text-sm text-gray-900 text-right">
                                                {{ $record->{$column['key']} }}
                                            </dd>
                                        </div>
                                    @endif
                                @endforeach
                            </dl>
                        </div>

                        {{-- Mobile Actions Dropdown --}}
                        @if(count($actions) > 0)
                            <div id="mobile-actions-{{ $record->id }}" class="hidden border-t border-gray-200 bg-gray-50 px-4 py-2">
                                <div class="flex flex-wrap gap-2">
                                    @foreach($actions as $action)
                                        <button 
                                            type="button"
                                            class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                        >
                                            {{ $action['label'] }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            {{-- Empty State --}}
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('admin.table.no_records') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('admin.table.no_records_description') }}</p>
            </div>
        @endif
    </div>

    {{-- Mobile Bulk Actions Bar --}}
    @if(count($bulkActions) > 0)
        <div id="mobile-bulk-actions" class="hidden lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 shadow-lg">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">
                    <span id="selected-count">0</span> {{ __('messages.admin) }}
                </span>
                <div class="flex space-x-2">
                    @foreach($bulkActions as $action)
                        <button 
                            type="button"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            {{ $action['label'] }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Mobile Table JavaScript --}}
<script>
function toggleMobileSearch() {
    const searchBar = document.getElementById('mobile-search-bar');
    const isHidden = searchBar.classList.contains('hidden');
    
    if (isHidden) {
        searchBar.classList.remove('hidden');
        document.getElementById('mobile-search-input').focus();
    } else {
        searchBar.classList.add('hidden');
    }
}

function toggleMobileFilters() {
    const filters = document.getElementById('mobile-filters');
    filters.classList.toggle('hidden');
}

function toggleMobileView() {
    const cardView = document.getElementById('mobile-card-view');
    // This would toggle between card view and list view
    // Implementation depends on specific requirements
}

function toggleMobileActions(recordId) {
    const actions = document.getElementById(`mobile-actions-${recordId}`);
    actions.classList.toggle('hidden');
}

function clearMobileFilters() {
    // Clear all filter inputs
    const filters = document.getElementById('mobile-filters');
    const inputs = filters.querySelectorAll('select, input');
    inputs.forEach(input => {
        if (input.type === 'checkbox' || input.type === 'radio') {
            input.checked = false;
        } else {
            input.value = '';
        }
    });
}

function applyMobileFilters() {
    // Apply filters and refresh table
    // Implementation depends on specific filtering system
    toggleMobileFilters();
}

// Handle bulk action selection
document.addEventListener('change', function(event) {
    if (event.target.type === 'checkbox' && event.target.value) {
        updateBulkActionBar();
    }
});

function updateBulkActionBar() {
    const checkboxes = document.querySelectorAll('input[type="checkbox"][value]');
    const checked = Array.from(checkboxes).filter(cb => cb.checked);
    const bulkActions = document.getElementById('mobile-bulk-actions');
    const selectedCount = document.getElementById('selected-count');
    
    if (checked.length > 0) {
        bulkActions.classList.remove('hidden');
        selectedCount.textContent = checked.length;
    } else {
        bulkActions.classList.add('hidden');
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    const dropdowns = document.querySelectorAll('[id^="mobile-actions-"]');
    dropdowns.forEach(dropdown => {
        if (!dropdown.contains(event.target) && !event.target.closest(`[onclick*="${dropdown.id.split('-')[2]}"]`)) {
            dropdown.classList.add('hidden');
        }
    });
});
</script>

{{-- Mobile Table Styles --}}
<style>
.fi-mobile-table-container {
    -webkit-overflow-scrolling: touch;
}

.fi-mobile-table-container input,
.fi-mobile-table-container select,
.fi-mobile-table-container button {
    touch-action: manipulation;
    -webkit-tap-highlight-color: transparent;
}

.fi-mobile-table-container button:active {
    transform: scale(0.98);
}

@media (max-width: 768px) {
    .fi-mobile-table-container {
        margin-bottom: 80px; /* Space for bulk actions bar */
    }
}

/* Smooth transitions for mobile interactions */
.fi-mobile-table-container * {
    transition: background-color 0.2s ease, transform 0.1s ease;
}

/* Improve touch targets */
@media (hover: none) and (pointer: coarse) {
    .fi-mobile-table-container button,
    .fi-mobile-table-container input[type="checkbox"] {
        min-height: 44px;
        min-width: 44px;
    }
}
</style>