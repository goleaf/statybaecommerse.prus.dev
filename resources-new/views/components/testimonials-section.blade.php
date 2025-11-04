@props([
    'testimonials' => null,
    'title' => null,
    'subtitle' => null,
    'showRatings' => true,
    'showAvatars' => true,
    'autoplay' => true,
    'autoplayInterval' => 5000,
    'maxItems' => null,
])

@php
    $title = $title ?? __('What Our Customers Say');
    $subtitle = $subtitle ?? __('Read reviews from satisfied customers who love our products and service');
    $testimonials =
        $testimonials ??
        collect([
            [
                'name' => 'Sarah Johnson',
                'location' => 'New York, USA',
                'rating' => 5,
                'text' => __(
                    'The quality of products exceeded my expectations. Fast shipping and excellent customer service. I will definitely order again!',
                ),
                'avatar' => null,
                'product' => 'Premium Headphones',
                'verified' => true,
            ],
            [
                'name' => 'Michael Chen',
                'location' => 'London, UK',
                'rating' => 5,
                'text' => __(
                    'Outstanding customer support and the product arrived in perfect condition. Highly recommended for anyone looking for quality items.',
                ),
                'avatar' => null,
                'product' => 'Wireless Speaker',
                'verified' => true,
            ],
            [
                'name' => 'Emma Rodriguez',
                'location' => 'Madrid, Spain',
                'rating' => 4,
                'text' => __(
                    'Great selection of products and competitive prices. The checkout process was smooth and I received my order quickly.',
                ),
                'avatar' => null,
                'product' => 'Smart Watch',
                'verified' => true,
            ],
            [
                'name' => 'David Kim',
                'location' => 'Seoul, South Korea',
                'rating' => 5,
                'text' => __(
                    'Excellent shopping experience from start to finish. The product descriptions were accurate and the quality is top-notch.',
                ),
                'avatar' => null,
                'product' => 'Gaming Mouse',
                'verified' => true,
            ],
            [
                'name' => 'Lisa Thompson',
                'location' => 'Toronto, Canada',
                'rating' => 5,
                'text' => __(
                    'I\'ve been a customer for over a year now and I\'m always impressed with the service. Fast delivery and great products!',
                ),
                'avatar' => null,
                'product' => 'Bluetooth Earbuds',
                'verified' => true,
            ],
            [
                'name' => 'Ahmed Hassan',
                'location' => 'Dubai, UAE',
                'rating' => 4,
                'text' => __(
                    'Good variety of products and reasonable prices. The customer service team was very helpful when I had questions.',
                ),
                'avatar' => null,
                'product' => 'Phone Case',
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
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">{{ $subtitle }}</p>
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
                                        {{-- Rating --}}
                                        @if ($showRatings)
                                            <div class="flex items-center mb-4">
                                                <div class="flex items-center">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        <svg class="w-5 h-5 {{ $i <= $testimonial['rating'] ? 'text-yellow-400' : 'text-gray-300' }}"
                                                             fill="currentColor" viewBox="0 0 20 20">
                                                            <path
                                                                  d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                        </svg>
                                                    @endfor
                                                </div>
                                                @if ($testimonial['verified'])
                                                    <span
                                                          class="ml-2 inline-flex items-center gap-1 text-xs text-green-600 font-medium">
                                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd"
                                                                  d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                                  clip-rule="evenodd" />
                                                        </svg>
                                                        {{ __('Verified') }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif

                                        {{-- Testimonial Text --}}
                                        <blockquote class="text-gray-700 mb-6 leading-relaxed">
                                            "{{ $testimonial['text'] }}"
                                        </blockquote>

                                        {{-- Product Info --}}
                                        @if (isset($testimonial['product']))
                                            <div class="mb-4">
                                                <span class="text-sm text-gray-500">{{ __('Product') }}:</span>
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
        <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="text-4xl font-bold text-blue-600 mb-2">{{ $testimonials->count() }}+</div>
                <div class="text-gray-600">{{ __('Happy Customers') }}</div>
            </div>
            <div class="text-center">
                <div class="text-4xl font-bold text-blue-600 mb-2">
                    {{ number_format($testimonials->avg('rating'), 1) }}
                </div>
                <div class="text-gray-600">{{ __('Average Rating') }}</div>
            </div>
            <div class="text-center">
                <div class="text-4xl font-bold text-blue-600 mb-2">98%</div>
                <div class="text-gray-600">{{ __('Satisfaction Rate') }}</div>
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
