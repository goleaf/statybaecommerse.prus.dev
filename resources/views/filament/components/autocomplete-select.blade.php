@php
    $viewData = $getViewData();
@endphp

<div
     x-data="autocompleteSelect({
         searchable: @js($viewData['searchable']),
         multiple: @js($viewData['multiple']),
         minSearchLength: @js($viewData['minSearchLength']),
         maxSearchResults: @js($viewData['maxSearchResults']),
         searchField: @js($viewData['searchField']),
         valueField: @js($viewData['valueField']),
         labelField: @js($viewData['labelField']),
         modelClass: @js($viewData['modelClass']),
         searchResults: @js($viewData['searchResults']),
         searchQuery: @js($viewData['searchQuery']),
         state: $wire.{{ $getStatePath() }},
     })"
     class="space-y-2">
    <div class="relative">
        <input
               type="text"
               x-model="searchQuery"
               x-on:input.debounce.300ms="performSearch()"
               x-on:focus="showDropdown = true"
               x-on:blur="hideDropdown()"
               placeholder="{{ $getPlaceholder() }}"
               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
               :class="{ 'border-red-500': $wire.errors.has('{{ $getStatePath() }}') }" />

        <div
             x-show="showDropdown && searchResults.length > 0"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="transform opacity-0 scale-95"
             x-transition:enter-end="transform opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="transform opacity-100 scale-100"
             x-transition:leave-end="transform opacity-0 scale-95"
             class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
            <template x-for="result in searchResults" :key="result.value">
                <div
                     x-on:click="selectResult(result)"
                     class="px-4 py-2 cursor-pointer hover:bg-gray-100 border-b border-gray-100 last:border-b-0">
                    <div class="font-medium text-gray-900" x-text="result.label"></div>
                    <div class="text-sm text-gray-500" x-show="result.data && result.data.description"
                         x-text="result.data.description"></div>
                </div>
            </template>

            <div x-show="searchResults.length === 0 && searchQuery.length >= minSearchLength"
                 class="px-4 py-2 text-gray-500 text-sm">
                {{ __('No results found') }}
            </div>
        </div>
    </div>

    <!-- Selected Items Display (for multiple selection) -->
    <div x-show="multiple && selectedItems.length > 0" class="flex flex-wrap gap-2">
        <template x-for="item in selectedItems" :key="item.value">
            <div class="inline-flex items-center px-2 py-1 bg-primary-100 text-primary-800 rounded-md text-sm">
                <span x-text="item.label"></span>
                <button
                        type="button"
                        x-on:click="removeItem(item)"
                        class="ml-1 text-primary-600 hover:text-primary-800">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                              clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    <!-- Hidden input for form submission -->
    <input type="hidden" x-model="state" name="{{ $getName() }}" />

    @error($getStatePath())
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

@push('scripts')
    <script>
        function autocompleteSelect(config) {
            return {
                searchable: config.searchable,
                multiple: config.multiple,
                minSearchLength: config.minSearchLength,
                maxSearchResults: config.maxSearchResults,
                searchField: config.searchField,
                valueField: config.valueField,
                labelField: config.labelField,
                modelClass: config.modelClass,
                searchResults: config.searchResults || [],
                searchQuery: config.searchQuery || '',
                selectedItems: config.multiple ? (config.state ? JSON.parse(config.state) : []) : [],
                showDropdown: false,
                state: config.state,

                init() {
                    // Initialize with existing state
                    if (this.state && !this.multiple) {
                        this.loadExistingValue();
                    }
                },

                async performSearch() {
                    if (!this.searchable || this.searchQuery.length < this.minSearchLength) {
                        this.searchResults = [];
                        return;
                    }

                    try {
                        const response = await fetch('/api/autocomplete-search', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content'),
                            },
                            body: JSON.stringify({
                                model_class: this.modelClass,
                                search_field: this.searchField || this.labelField,
                                search_query: this.searchQuery,
                                value_field: this.valueField,
                                label_field: this.labelField,
                                limit: this.maxSearchResults,
                            })
                        });

                        if (response.ok) {
                            const data = await response.json();
                            this.searchResults = data.results || [];
                        } else {
                            this.searchResults = [];
                        }
                    } catch (error) {
                        console.error('Autocomplete search error:', error);
                        this.searchResults = [];
                    }
                },

                selectResult(result) {
                    if (this.multiple) {
                        if (!this.selectedItems.find(item => item.value === result.value)) {
                            this.selectedItems.push(result);
                        }
                        this.state = JSON.stringify(this.selectedItems);
                    } else {
                        this.state = result.value;
                        this.searchQuery = result.label;
                        this.showDropdown = false;
                    }
                },

                removeItem(item) {
                    this.selectedItems = this.selectedItems.filter(selectedItem => selectedItem.value !== item.value);
                    this.state = JSON.stringify(this.selectedItems);
                },

                hideDropdown() {
                    setTimeout(() => {
                        this.showDropdown = false;
                    }, 200);
                },

                loadExistingValue() {
                    // Load existing value when editing
                    if (this.state && !this.multiple) {
                        // This would need to be implemented based on your specific needs
                        // You might need to make an API call to get the label for the current value
                    }
                }
            }
        }
    </script>
@endpush

