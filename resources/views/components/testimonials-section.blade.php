@props([
    'testimonials' => null,
    'title' => null,
    'subtitle' => null,
    'showAvatars' => true,
    'autoplay' => true,
    'autoplayInterval' => 5000,
    'maxItems' => null,
])

@php
    $title = $title ?? __('ui.what_our_customers_say');
    $subtitle = $subtitle ?? '';
    $testimonials =
        $testimonials ??
        collect([
            [
                'name' => __('frontend.testimonials.defaults.customer_1.name'),
                'location' => __('frontend.testimonials.defaults.customer_1.location'),
                'text' => __('ui.the_quality_of_products_exceeded_my_expectations_fast_shipping_and_excellent_customer_service_i_will_definitely_order_again',
                ),
                'avatar' => null,
                'product' => __('frontend.testimonials.defaults.customer_1.product'),
                'verified' => true,
            ],
            [
                'name' => __('frontend.testimonials.defaults.customer_2.name'),
                'location' => __('frontend.testimonials.defaults.customer_2.location'),
                'text' => __('ui.outstanding_customer_support_and_the_product_arrived_in_perfect_condition_highly_recommended_for_anyone_looking_for_quality_items',
                ),
                'avatar' => null,
                'product' => __('frontend.testimonials.defaults.customer_2.product'),
                'verified' => true,
            ],
            [
                'name' => __('frontend.testimonials.defaults.customer_3.name'),
                'location' => __('frontend.testimonials.defaults.customer_3.location'),
                'text' => __('ui.great_selection_of_products_and_competitive_prices_the_checkout_process_was_smooth_and_i_received_my_order_quickly',
                ),
                'avatar' => null,
                'product' => __('frontend.testimonials.defaults.customer_3.product'),
                'verified' => true,
            ],
            [
                'name' => __('frontend.testimonials.defaults.customer_4.name'),
                'location' => __('frontend.testimonials.defaults.customer_4.location'),
                'text' => __('ui.excellent_shopping_experience_from_start_to_finish_the_product_descriptions_were_accurate_and_the_quality_is_top_notch',
                ),
                'avatar' => null,
                'product' => __('frontend.testimonials.defaults.customer_4.product'),
                'verified' => true,
            ],
            [
                'name' => __('frontend.testimonials.defaults.customer_5.name'),
                'location' => __('frontend.testimonials.defaults.customer_5.location'),
                'text' => __('ui.ive_been_a_customer_for_over_a_year_now_and_im_always_impressed_with_the_service_fast_delivery_and_great_products'),
                'avatar' => null,
                'product' => __('frontend.testimonials.defaults.customer_5.product'),
                'verified' => true,
            ],
            [
                'name' => __('frontend.testimonials.defaults.customer_6.name'),
                'location' => __('frontend.testimonials.defaults.customer_6.location'),
                'text' => __('ui.good_variety_of_products_and_reasonable_prices_the_customer_service_team_was_very_helpful_when_i_had_questions',
                ),
                'avatar' => null,
                'product' => __('frontend.testimonials.defaults.customer_6.product'),
                'verified' => true,
            ],
        ]);

    if ($maxItems) {
        $testimonials = $testimonials->take($maxItems);
    }
@endphp

<div class="testimonials-section" x-data="testimonialsSection()" x-init="init()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="text-center mb-12">
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">{{ $title }}</h2>
            @if ($subtitle !== '')
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">{{ $subtitle }}</p>
            @endif
        </div>

        {{-- Testimonials Carousel --}}
        <div class="relative">
            {{-- Navigation Arrows --}}
            <button @click="previousSlide()"
                    class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 z-10 w-12 h-12 bg-white rounded-full shadow-large border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition-colors duration-200"
                    :disabled="currentSlide === 0">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>

            <button @click="nextSlide()"
                    class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 z-10 w-12 h-12 bg-white rounded-full shadow-large border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition-colors duration-200"
                    :disabled="currentSlide >= maxSlides - 1">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>

            {{-- Testimonials Container --}}
            <div class="overflow-hidden">
                <div class="flex transition-transform duration-500 ease-in-out translate-x-var"
                     :data-transform="`-${currentSlide * 100}%`">
                    @foreach ($testimonials->chunk(3) as $chunk)
                        <div class="w-full flex-shrink-0">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach ($chunk as $testimonial)
                                    <div
                                         class="bg-white border border-gray-200 rounded-2xl p-6 hover:shadow-large transition-shadow duration-300">
                                        @if ($testimonial['verified'])
                                            <div class="mb-4">
                                                <span class="inline-flex items-center gap-1 text-xs text-green-600 font-medium">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                              d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                              clip-rule="evenodd" />
                                                    </svg>
                                                    {{ __('ui.verified') }}
                                                </span>
                                            </div>
                                        @endif

                                        {{-- Testimonial Text --}}
                                        <blockquote class="text-gray-700 mb-6 leading-relaxed">
                                            "{{ $testimonial['text'] }}"
                                        </blockquote>

                                        {{-- Product Info --}}
                                        @if (isset($testimonial['product']))
                                            <div class="mb-4">
                                                <span class="text-sm text-gray-500">{{ __('messages.product') }}:</span>
                                                <span
                                                      class="text-sm font-medium text-gray-900 ml-1">{{ $testimonial['product'] }}</span>
                                            </div>
                                        @endif

                                        {{-- Customer Info --}}
                                        <div class="flex items-center">
                                            @if ($showAvatars)
                                                <div
                                                     class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center flex-shrink-0">
                                                    @if ($testimonial['avatar'])
                                                        <img src="{{ $testimonial['avatar'] }}"
                                                             alt="{{ $testimonial['name'] }}"
                                                             class="w-12 h-12 rounded-full object-cover">
                                                    @else
                                                        <span class="text-white font-semibold text-lg">
                                                            {{ substr($testimonial['name'], 0, 1) }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif

                                            <div class="ml-4">
                                                <h4 class="font-semibold text-gray-900">{{ $testimonial['name'] }}</h4>
                                                <p class="text-sm text-gray-600">{{ $testimonial['location'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Dots Indicator --}}
        <div class="flex justify-center mt-8 space-x-2">
            @for ($i = 0; $i < ceil($testimonials->count() / 3); $i++)
                <button @click="currentSlide = {{ $i }}"
                        :class="currentSlide === {{ $i }} ? 'bg-blue-600' : 'bg-gray-300'"
                        class="w-3 h-3 rounded-full transition-colors duration-200"></button>
            @endfor
        </div>

        {{-- Stats Section --}}
        <div class="mt-16 grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="text-center">
                <div class="text-4xl font-bold text-blue-600 mb-2">{{ $testimonials->count() }}+</div>
                <div class="text-gray-600">{{ __('ui.happy_customers') }}</div>
            </div>
            <div class="text-center">
                <div class="text-4xl font-bold text-blue-600 mb-2">98%</div>
                <div class="text-gray-600">{{ __('ui.satisfaction_rate') }}</div>
            </div>
        </div>
    </div>
</div>

<script>
    function testimonialsSection() {
        return {
            currentSlide: 0,
            maxSlides: {{ ceil($testimonials->count() / 3) }},
            autoplayInterval: null,

            init() {
                if ({{ $autoplay ? 'true' : 'false' }}) {
                    this.startAutoplay();
                }
            },

            nextSlide() {
                if (this.currentSlide < this.maxSlides - 1) {
                    this.currentSlide++;
                } else {
                    this.currentSlide = 0;
                }
            },

            previousSlide() {
                if (this.currentSlide > 0) {
                    this.currentSlide--;
                } else {
                    this.currentSlide = this.maxSlides - 1;
                }
            },

            startAutoplay() {
                this.autoplayInterval = setInterval(() => {
                    this.nextSlide();
                }, {{ $autoplayInterval }});
            },

            stopAutoplay() {
                if (this.autoplayInterval) {
                    clearInterval(this.autoplayInterval);
                    this.autoplayInterval = null;
                }
            }
        }
    }
</script>
