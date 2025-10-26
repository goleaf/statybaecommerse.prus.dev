@once
    @push('scripts')
        <script nonce="{{ csp_nonce() }}">
            document.addEventListener('alpine:init', () => {
                // Shared Alpine helper that powers debounced search forms across
                // the storefront. It keeps the sanitisation logic aligned with
                // the backend implementation while providing an ergonomic
                // auto-submit experience for shoppers.
                Alpine.data('debouncedSearchForm', (config = {}) => ({
                    term: typeof config.initialQuery === 'string' ? config.initialQuery : '',
                    delay: Number(config.delay ?? 400),
                    minLength: Number(config.minLength ?? 2),
                    maxLength: Number(config.maxLength ?? 120),
                    autoSubmit: config.autoSubmit !== false,
                    allowEmptyAutoSubmit: config.allowEmptyAutoSubmit === true,
                    allowEmptyManualSubmit: config.allowEmptyManualSubmit !== false,
                    timer: null,
                    handleInput() {
                        this.term = this.clean(this.term);
                        this.$refs.queryField.value = this.term;

                        if (this.autoSubmit) {
                            this.queueSubmit();
                        }
                    },
                    queueSubmit() {
                        window.clearTimeout(this.timer);

                        if (!this.autoSubmit) {
                            return;
                        }

                        if (!this.allowEmptyAutoSubmit && (this.term === '' || this.term.length < this.minLength)) {
                            return;
                        }

                        this.timer = window.setTimeout(() => this.submitForm(false), this.delay);
                    },
                    manualSubmit() {
                        window.clearTimeout(this.timer);
                        this.submitForm(true);
                    },
                    submitForm(force) {
                        const sanitized = this.clean(this.term);
                        this.term = sanitized;
                        this.$refs.queryField.value = sanitized;

                        if (!force) {
                            if (!this.allowEmptyAutoSubmit && sanitized === '') {
                                return;
                            }

                            if (!this.allowEmptyAutoSubmit && sanitized.length < this.minLength) {
                                return;
                            }
                        } else if (!this.allowEmptyManualSubmit && sanitized === '') {
                            return;
                        }

                        this.$el.requestSubmit();
                    },
                    clean(value) {
                        if (typeof value !== 'string') {
                            return '';
                        }

                        const withoutTags = value.replace(/<[^>]*>?/g, ' ');
                        const normalised = withoutTags.replace(/[^\p{L}\p{N}\s\-_'\"@#.,()]/gu, ' ');
                        const collapsed = normalised.replace(/\s+/gu, ' ').trim();

                        return collapsed.slice(0, this.maxLength);
                    },
                    resetSearch() {
                        this.term = '';
                        this.$refs.queryField.value = '';

                        if (this.allowEmptyAutoSubmit) {
                            this.queueSubmit();
                        }
                    },
                }));
            });
        </script>
    @endpush
@endonce
