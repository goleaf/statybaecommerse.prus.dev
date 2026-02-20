@props([
    'faqs' => null,
    'title' => null,
    'subtitle' => null,
    'showSearch' => true,
    'maxItems' => null,
])

@php
    $title = $title ?? __('messages.frontend');
    $subtitle = $subtitle ?? __('messages.frontend');
    $faqs =
        $faqs ??
        collect([
            [
                'question' => __('frontend.faq.questions.track_order'),
                'answer' => __('frontend.faq.answers.track_order'),
                'category' => 'shipping',
            ],
            [
                'question' => __('frontend.faq.questions.payment_methods'),
                'answer' => __('frontend.faq.answers.payment_methods'),
                'category' => 'payment',
            ],
            [
                'question' => __('frontend.faq.questions.return_policy'),
                'answer' => __('frontend.faq.answers.return_policy'),
                'category' => 'returns',
            ],
            [
                'question' => __('frontend.faq.questions.shipping_time'),
                'answer' => __('frontend.faq.answers.shipping_time'),
                'category' => 'shipping',
            ],
            [
                'question' => __('frontend.faq.questions.international_shipping'),
                'answer' => __('frontend.faq.answers.international_shipping'),
                'category' => 'shipping',
            ],
            [
                'question' => __('frontend.faq.questions.contact_support'),
                'answer' => __('frontend.faq.answers.contact_support'),
                'category' => 'support',
            ],
        ]);

    if ($maxItems) {
        $faqs = $faqs->take($maxItems);
    }

    $categories = $faqs->pluck('category')->unique()->values();
@endphp

<div class="faq-section" x-data="faqSection()">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="text-center mb-12">
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">{{ $title }}</h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">{{ $subtitle }}</p>
        </div>

        {{-- Search Bar --}}
        @if ($showSearch)
            <div class="mb-8">
                <div class="relative max-w-md mx-auto">
                    <input type="text"
                           x-model="searchQuery"
                           @input="filterFAQs()"
                           placeholder="{{ __('frontend.faq.search_placeholder') }}"
                           class="w-full px-4 py-3 pl-10 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder:text-gray-500">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        @endif

        {{-- Category Filters --}}
        @if ($categories->count() > 1)
            <div class="mb-8">
                <div class="flex flex-wrap justify-center gap-2">
                    <button @click="selectedCategory = 'all'; filterFAQs()"
                            :class="selectedCategory === 'all' ? 'bg-blue-600 text-white' :
                                'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                        {{ __('messages.frontend') }}
                    </button>
                    @foreach ($categories as $category)
                        <button @click="selectedCategory = '{{ $category }}'; filterFAQs()"
                                :class="selectedCategory === '{{ $category }}' ? 'bg-blue-600 text-white' :
                                    'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                            {{ __(sprintf('frontend.faq.categories.%s', $category)) }}
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- FAQ Items --}}
        <div class="space-y-4">
            @foreach ($faqs as $index => $faq)
                <div x-show="isVisible({{ $index }})"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-95"
                     class="bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-medium transition-shadow duration-200"
                     data-category="{{ $faq['category'] }}"
                     data-question="{{ strtolower($faq['question']) }}"
                     data-answer="{{ strtolower($faq['answer']) }}">

                    <button @click="toggleFAQ({{ $index }})"
                            class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors duration-200">
                        <h3 class="text-lg font-semibold text-gray-900 pr-4">{{ $faq['question'] }}</h3>
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-gray-500 transition-transform duration-200"
                                 :class="{ 'rotate-180': openFAQs.includes({{ $index }}) }"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </button>

                    <div x-show="openFAQs.includes({{ $index }})"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 max-h-0"
                         x-transition:enter-end="opacity-100 max-h-96"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 max-h-96"
                         x-transition:leave-end="opacity-0 max-h-0"
                         class="overflow-hidden">
                        <div class="px-6 pb-4">
                            <div class="border-t border-gray-100 pt-4">
                                <p class="text-gray-700 leading-relaxed">{{ $faq['answer'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- No Results Message --}}
        <div x-show="filteredCount === 0"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="text-center py-12">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                </path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('messages.frontend') }}</h3>
            <p class="text-gray-600 mb-4">{{ __('messages.frontend') }}</p>
            <button @click="clearFilters()" class="text-blue-600 hover:text-blue-700 font-medium">
                {{ __('messages.frontend') }}
            </button>
        </div>

        {{-- Contact Support --}}
        <div class="mt-12 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-8 text-center">
            <h3 class="text-xl font-bold text-gray-900 mb-4">{{ __('messages.frontend') }}</h3>
            <p class="text-gray-600 mb-6">
                {{ __('messages.frontend') }}</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ \Illuminate\Support\Facades\Route::has('frontend.contact.index') ? route('frontend.contact.index') : url('/contact') }}"
                   class="btn-gradient px-6 py-3 rounded-xl font-semibold">
                    {{ __('messages.frontend') }}
                </a>
                <a href="mailto:support@example.com"
                   class="border-2 border-gray-300 text-gray-700 px-6 py-3 rounded-xl font-semibold hover:border-gray-400 hover:bg-gray-50 transition-colors duration-200">
                    {{ __('messages.frontend') }}
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    function faqSection() {
        return {
            openFAQs: [],
            searchQuery: '',
            selectedCategory: 'all',
            filteredCount: {{ $faqs->count() }},

            init() {
                // Initialize with first FAQ open
                if (this.openFAQs.length === 0) {
                    this.openFAQs = [0];
                }
            },

            toggleFAQ(index) {
                if (this.openFAQs.includes(index)) {
                    this.openFAQs = this.openFAQs.filter(i => i !== index);
                } else {
                    this.openFAQs.push(index);
                }
            },

            isVisible(index) {
                const element = document.querySelector(
                    `[data-category][data-question][data-answer]:nth-child(${index + 1})`);
                if (!element) return true;

                const category = element.getAttribute('data-category');
                const question = element.getAttribute('data-question');
                const answer = element.getAttribute('data-answer');

                // Check category filter
                if (this.selectedCategory !== 'all' && category !== this.selectedCategory) {
                    return false;
                }

                // Check search query
                if (this.searchQuery) {
                    const query = this.searchQuery.toLowerCase();
                    return question.includes(query) || answer.includes(query);
                }

                return true;
            },

            filterFAQs() {
                // Count visible FAQs
                let count = 0;
                const faqElements = document.querySelectorAll('[data-category][data-question][data-answer]');

                faqElements.forEach((element, index) => {
                    if (this.isVisible(index)) {
                        count++;
                    }
                });

                this.filteredCount = count;
            },

            clearFilters() {
                this.searchQuery = '';
                this.selectedCategory = 'all';
                this.filterFAQs();
            }
        }
    }
</script>
