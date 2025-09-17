/**
 * Enhanced Autocomplete Component
 * 
 * A standalone JavaScript component for autocomplete functionality
 * that can be used with any API endpoint.
 */
class AutocompleteComponent {
    constructor(options = {}) {
        this.options = {
            input: null,
            container: null,
            apiUrl: '/api/autocomplete/search',
            minLength: 2,
            debounceDelay: 300,
            maxResults: 10,
            showSuggestions: true,
            showRecent: true,
            showPopular: true,
            onSelect: null,
            onClear: null,
            placeholder: 'Search...',
            ...options
        };

        this.selectedIndex = -1;
        this.results = [];
        this.suggestions = [];
        this.isSearching = false;
        this.debounceTimer = null;

        this.init();
    }

    init() {
        if (!this.options.input) {
            throw new Error('Input element is required');
        }

        this.setupInput();
        this.setupContainer();
        this.loadSuggestions();
        this.bindEvents();
    }

    setupInput() {
        this.input = typeof this.options.input === 'string' 
            ? document.querySelector(this.options.input)
            : this.options.input;

        if (!this.input) {
            throw new Error('Input element not found');
        }

        this.input.setAttribute('autocomplete', 'off');
        this.input.setAttribute('role', 'combobox');
        this.input.setAttribute('aria-expanded', 'false');
        this.input.setAttribute('aria-autocomplete', 'list');
    }

    setupContainer() {
        if (this.options.container) {
            this.container = typeof this.options.container === 'string'
                ? document.querySelector(this.options.container)
                : this.options.container;
        } else {
            this.container = this.createContainer();
        }

        this.input.parentNode.appendChild(this.container);
    }

    createContainer() {
        const container = document.createElement('div');
        container.className = 'autocomplete-container';
        container.style.cssText = `
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1000;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            max-height: 20rem;
            overflow-y: auto;
            display: none;
        `;
        return container;
    }

    bindEvents() {
        this.input.addEventListener('input', this.handleInput.bind(this));
        this.input.addEventListener('keydown', this.handleKeydown.bind(this));
        this.input.addEventListener('focus', this.handleFocus.bind(this));
        this.input.addEventListener('blur', this.handleBlur.bind(this));

        // Close on outside click
        document.addEventListener('click', this.handleOutsideClick.bind(this));
    }

    handleInput(event) {
        const query = event.target.value.trim();
        
        clearTimeout(this.debounceTimer);
        
        if (query.length >= this.options.minLength) {
            this.debounceTimer = setTimeout(() => {
                this.performSearch(query);
            }, this.options.debounceDelay);
        } else {
            this.hideResults();
            if (query.length === 0 && this.options.showSuggestions) {
                this.showSuggestions();
            }
        }
    }

    handleKeydown(event) {
        const totalItems = this.results.length + this.suggestions.length;

        switch (event.key) {
            case 'ArrowDown':
                event.preventDefault();
                this.selectedIndex = Math.min(this.selectedIndex + 1, totalItems - 1);
                this.updateSelection();
                break;
            case 'ArrowUp':
                event.preventDefault();
                this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                this.updateSelection();
                break;
            case 'Enter':
                event.preventDefault();
                if (this.selectedIndex >= 0) {
                    this.selectItem(this.selectedIndex);
                }
                break;
            case 'Escape':
                this.hideResults();
                this.input.blur();
                break;
        }
    }

    handleFocus() {
        if (this.input.value.length === 0 && this.options.showSuggestions) {
            this.showSuggestions();
        }
    }

    handleBlur() {
        // Delay hiding to allow for clicks on results
        setTimeout(() => {
            this.hideResults();
        }, 150);
    }

    handleOutsideClick(event) {
        if (!this.input.contains(event.target) && !this.container.contains(event.target)) {
            this.hideResults();
        }
    }

    async performSearch(query) {
        this.isSearching = true;
        this.showLoading();

        try {
            const response = await fetch(`${this.options.apiUrl}?q=${encodeURIComponent(query)}&limit=${this.options.maxResults}`);
            const data = await response.json();

            if (data.success) {
                this.results = data.data;
                this.showResults();
            } else {
                this.showError(data.message || 'Search failed');
            }
        } catch (error) {
            console.error('Autocomplete search error:', error);
            this.showError('Search failed');
        } finally {
            this.isSearching = false;
        }
    }

    async loadSuggestions() {
        if (!this.options.showSuggestions) return;

        try {
            const response = await fetch('/api/autocomplete/suggestions');
            const data = await response.json();

            if (data.success) {
                this.suggestions = data.data;
            }
        } catch (error) {
            console.error('Failed to load suggestions:', error);
        }
    }

    showResults() {
        this.container.innerHTML = '';
        this.selectedIndex = -1;

        if (this.results.length === 0) {
            this.showNoResults();
            return;
        }

        const resultsList = document.createElement('div');
        resultsList.className = 'autocomplete-results';

        this.results.forEach((result, index) => {
            const item = this.createResultItem(result, index);
            resultsList.appendChild(item);
        });

        this.container.appendChild(resultsList);
        this.container.style.display = 'block';
        this.input.setAttribute('aria-expanded', 'true');
    }

    showSuggestions() {
        this.container.innerHTML = '';
        this.selectedIndex = -1;

        if (this.suggestions.length === 0) {
            this.container.style.display = 'none';
            return;
        }

        const suggestionsList = document.createElement('div');
        suggestionsList.className = 'autocomplete-suggestions';

        // Add header
        const header = document.createElement('div');
        header.className = 'autocomplete-header';
        header.textContent = 'Suggestions';
        suggestionsList.appendChild(header);

        this.suggestions.forEach((suggestion, index) => {
            const item = this.createSuggestionItem(suggestion, index);
            suggestionsList.appendChild(item);
        });

        this.container.appendChild(suggestionsList);
        this.container.style.display = 'block';
        this.input.setAttribute('aria-expanded', 'true');
    }

    createResultItem(result, index) {
        const item = document.createElement('div');
        item.className = 'autocomplete-item';
        item.setAttribute('data-index', index);
        item.setAttribute('role', 'option');

        item.innerHTML = `
            <div class="flex items-center space-x-3 p-3 hover:bg-gray-50 cursor-pointer">
                <div class="flex-shrink-0">
                    ${result.image ? `<img src="${result.image}" alt="${result.title}" class="w-8 h-8 object-cover rounded">` : ''}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium text-gray-900 truncate">${result.title}</h3>
                        ${result.formatted_price ? `<span class="text-sm font-semibold text-blue-600">${result.formatted_price}</span>` : ''}
                    </div>
                    ${result.subtitle ? `<p class="text-sm text-gray-500 truncate">${result.subtitle}</p>` : ''}
                    <div class="mt-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                            ${result.type}
                        </span>
                    </div>
                </div>
            </div>
        `;

        item.addEventListener('click', () => this.selectItem(index));
        item.addEventListener('mouseenter', () => {
            this.selectedIndex = index;
            this.updateSelection();
        });

        return item;
    }

    createSuggestionItem(suggestion, index) {
        const item = document.createElement('div');
        item.className = 'autocomplete-item';
        item.setAttribute('data-index', index);
        item.setAttribute('role', 'option');

        item.innerHTML = `
            <div class="flex items-center space-x-3 p-3 hover:bg-gray-50 cursor-pointer">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-medium text-gray-900 truncate">${suggestion.title}</h3>
                    ${suggestion.subtitle ? `<p class="text-sm text-gray-500 truncate">${suggestion.subtitle}</p>` : ''}
                </div>
            </div>
        `;

        item.addEventListener('click', () => this.selectSuggestion(index));
        item.addEventListener('mouseenter', () => {
            this.selectedIndex = index;
            this.updateSelection();
        });

        return item;
    }

    showLoading() {
        this.container.innerHTML = `
            <div class="flex items-center justify-center py-4">
                <div class="flex items-center space-x-2 text-gray-500">
                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-sm">Searching...</span>
                </div>
            </div>
        `;
        this.container.style.display = 'block';
    }

    showNoResults() {
        this.container.innerHTML = `
            <div class="px-4 py-8 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No results found</h3>
                <p class="mt-1 text-sm text-gray-500">Try different keywords</p>
            </div>
        `;
        this.container.style.display = 'block';
    }

    showError(message) {
        this.container.innerHTML = `
            <div class="px-4 py-8 text-center">
                <svg class="mx-auto h-12 w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Error</h3>
                <p class="mt-1 text-sm text-gray-500">${message}</p>
            </div>
        `;
        this.container.style.display = 'block';
    }

    updateSelection() {
        const items = this.container.querySelectorAll('.autocomplete-item');
        items.forEach((item, index) => {
            if (index === this.selectedIndex) {
                item.classList.add('bg-gray-50');
            } else {
                item.classList.remove('bg-gray-50');
            }
        });
    }

    selectItem(index) {
        const result = this.results[index];
        if (result && this.options.onSelect) {
            this.options.onSelect(result);
        }
        this.hideResults();
    }

    selectSuggestion(index) {
        const suggestion = this.suggestions[index];
        if (suggestion) {
            this.input.value = suggestion.title;
            this.input.dispatchEvent(new Event('input'));
        }
        this.hideResults();
    }

    hideResults() {
        this.container.style.display = 'none';
        this.selectedIndex = -1;
        this.input.setAttribute('aria-expanded', 'false');
    }

    clear() {
        this.input.value = '';
        this.hideResults();
        if (this.options.onClear) {
            this.options.onClear();
        }
    }

    destroy() {
        this.input.removeEventListener('input', this.handleInput);
        this.input.removeEventListener('keydown', this.handleKeydown);
        this.input.removeEventListener('focus', this.handleFocus);
        this.input.removeEventListener('blur', this.handleBlur);
        document.removeEventListener('click', this.handleOutsideClick);
        
        if (this.container && this.container.parentNode) {
            this.container.parentNode.removeChild(this.container);
        }
    }
}

// Export for use in modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AutocompleteComponent;
}

// Make available globally
window.AutocompleteComponent = AutocompleteComponent;
