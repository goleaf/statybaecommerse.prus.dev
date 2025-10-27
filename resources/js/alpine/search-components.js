// Alpine.js search component helpers extracted to satisfy strict CSP policies.
// Each factory returns a fresh component configuration that Alpine can consume
// without relying on inline script blocks inside Blade templates.
(function registerSearchComponents() {
    /**
     * Build a base search component with shared behaviour for desktop and mobile experiences.
     * @param {object} options - Configuration payload passed from Blade.
     * @param {object} options.entangle - Livewire entanglement proxies for reactive data.
     * @param {number} options.minQueryLength - Minimum characters required before showing results.
     * @returns {object}
     */
    function createBaseSearchComponent(options) {
        const {
            entangle = {},
            minQueryLength = 2,
        } = options || {};

        return {
            // Spread the entangled Livewire properties directly on the component state.
            ...entangle,
            minQueryLength,
            selectedIndex: entangle?.selectedIndex ?? -1,
            /**
             * Alpine lifecycle hook: automatically wires listeners once the component boots.
             * Uses function references so they can be removed later if Alpine tears down the DOM node.
             */
            init() {
                // Persist bound handlers to reuse when detaching listeners.
                this.__handleKeydown = (event) => this.handleKeydown(event);

                this.$watch('query', (value) => {
                    const length = typeof value === 'string' ? value.length : 0;
                    if (length < this.minQueryLength) {
                        this.showResults = false;
                        if (length === 0 && typeof this.showSuggestions !== 'undefined') {
                            this.showSuggestions = true;
                        }
                    }
                });

                this.$el.addEventListener('keydown', this.__handleKeydown);
            },
            /**
             * Alpine lifecycle hook available in v3+: remove listeners to avoid memory leaks when nodes disappear.
             */
            destroy() {
                if (this.__handleKeydown) {
                    this.$el.removeEventListener('keydown', this.__handleKeydown);
                }
            },
            /**
             * Helper for keyboard driven navigation in result lists.
             * @param {KeyboardEvent} event
             */
            handleKeydown(event) {
                if (!event) {
                    return;
                }

                const totalItems = this.showResults ? (this.results?.length ?? 0) : (this.suggestions?.length ?? 0);

                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    this.selectedIndex = Math.min(this.selectedIndex + 1, totalItems - 1);
                } else if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                } else if (event.key === 'Enter') {
                    event.preventDefault();
                    if (this.selectedIndex >= 0) {
                        if (this.showResults && Array.isArray(this.results)) {
                            const result = this.results[this.selectedIndex];
                            if (result) {
                                this.$wire?.selectResult(result);
                            }
                        } else if (this.showSuggestions && Array.isArray(this.suggestions)) {
                            const suggestion = this.suggestions[this.selectedIndex];
                            if (suggestion) {
                                this.$wire?.selectSuggestion(suggestion);
                            }
                        }
                    }
                } else if (event.key === 'Escape') {
                    this.closeDropdowns();
                }
            },
            /**
             * Convenience wrapper that collapses both result and suggestion dropdowns.
             */
            closeDropdowns() {
                this.showResults = false;
                if (typeof this.showSuggestions !== 'undefined') {
                    this.showSuggestions = false;
                }
                this.selectedIndex = -1;
            },
            /**
             * Public method used by templates to pick a specific result index when clicking list buttons.
             * @param {number} index
             */
            selectResultByIndex(index) {
                if (!Array.isArray(this.results)) {
                    return;
                }

                const target = this.results[index];
                if (target) {
                    this.$wire?.selectResult(target);
                }
            },
            /**
             * Public method used by templates to pick a suggestion entry.
             * @param {number} index
             */
            selectSuggestionByIndex(index) {
                if (!Array.isArray(this.suggestions)) {
                    return;
                }

                const target = this.suggestions[index];
                if (target) {
                    this.$wire?.selectSuggestion(target);
                }
            },
        };
    }

    /**
     * Desktop search widget used for header autocomplete components.
     * @param {object} options
     * @returns {object}
     */
    function createDesktopSearchComponent(options) {
        return createBaseSearchComponent(options);
    }

    /**
     * Mobile search widget extends the base behaviour with fullscreen specific helpers.
     * @param {object} options
     * @returns {object}
     */
    function createMobileSearchComponent(options) {
        const component = createBaseSearchComponent(options);

        return {
            ...component,
            /**
             * Override keyboard handler so Escape also exits the fullscreen overlay.
             * @param {KeyboardEvent} event
             */
            handleKeydown(event) {
                component.handleKeydown.call(this, event);
                if (event && event.key === 'Escape' && typeof this.isFullScreen !== 'undefined') {
                    this.isFullScreen = false;
                }
            },
            /**
             * Utility for templates to toggle fullscreen state without inline arrow functions.
             * @param {boolean} value
             */
            setFullScreen(value) {
                if (typeof this.isFullScreen === 'undefined') {
                    return;
                }

                this.isFullScreen = Boolean(value);
            },
        };
    }

    /**
     * Enhanced desktop search widget that exposes helpers for multi-section layouts.
     * @param {object} options
     * @returns {object}
     */
    function createAdvancedSearchComponent(options) {
        const component = createBaseSearchComponent(options);

        return {
            ...component,
            /**
             * Helper to determine whether we have results ready for rendering.
             * @returns {boolean}
             */
            hasResults() {
                return Array.isArray(this.results) && this.results.length > 0;
            },
            /**
             * Helper to determine whether suggestions should be shown.
             * @returns {boolean}
             */
            hasSuggestions() {
                return Array.isArray(this.suggestions) && this.suggestions.length > 0;
            },
        };
    }

    /**
     * Enhanced search widget adds filter lifecycle handling to the advanced behaviour.
     * @param {object} options
     * @returns {object}
     */
    function createEnhancedSearchComponent(options) {
        const component = createAdvancedSearchComponent(options);

        return {
            ...component,
            /**
             * Ensure filters collapse alongside result dropdowns on blur/escape events.
             */
            closeDropdowns() {
                component.closeDropdowns.call(this);
                if (typeof this.showFilters !== 'undefined') {
                    this.showFilters = false;
                }
            },
        };
    }

    // Expose factories globally so Alpine attribute expressions can call them without inline scripts.
    const api = {
        createDesktopSearchComponent,
        createMobileSearchComponent,
        createAdvancedSearchComponent,
        createEnhancedSearchComponent,
    };

    Object.entries(api).forEach(([key, factory]) => {
        if (typeof window !== 'undefined' && !window[key]) {
            window[key] = factory;
        }
    });
})();
