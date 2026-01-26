<section class="relative overflow-hidden bg-sage"
         x-data="{
             currentSlide: @entangle('currentSlide'),
             autoPlay: @entangle('autoPlay'),
             interval: @entangle('autoPlayInterval'),
             slides: {{ $this->sliders->count() }},
             init() {
                 if (this.autoPlay && this.slides > 1) {
                     setInterval(() => {
                         if (this.autoPlay) {
                             this.nextSlide();
                         }
                     }, this.interval);
                 }
             },
             nextSlide() {
                 this.currentSlide = this.currentSlide >= this.slides - 1 ? 0 : this.currentSlide + 1;
             },
             prevSlide() {
                 this.currentSlide = this.currentSlide <= 0 ? this.slides - 1 : this.currentSlide - 1;
             },
             goToSlide(index) {
                 this.currentSlide = index;
             }
         }"
        role="region"
        aria-roledescription="carousel"
        aria-label="{{ __('messages.home_slider_tagline') }}">
    
    @if($this->sliders->count() > 0)
        <div class="space-y-14 py-16">
            <!-- Hero Text Section - Simple 2 Line Layout -->
            <div class="w-full px-4 sm:px-6 lg:px-8">
                <!-- Line 1: Tagline with autoplay toggle -->
                <div class="mb-8 flex items-center justify-between">
                    <h1 class="uppercase text-4xl font-medium text-dark" style="font-family: 'Montserrat', sans-serif;">
                        {{ __('messages.home_slider_tagline') }}
                    </h1>
                    @if($this->sliders->count() > 1)
                        <button @click="autoPlay = !autoPlay" 
                                class="inline-flex items-center gap-2 px-3 py-2 rounded-full border border-dark text-dark transition-colors duration-200"
                                :class="autoPlay ? 'bg-sage' : 'bg-transparent'"
                                :aria-pressed="autoPlay">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!autoPlay">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6l4-3-4-3z"></path>
                            </svg>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="autoPlay">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6"></path>
                            </svg>
                            <span class="text-sm font-semibold" x-show="!autoPlay">{{ __('messages.home_slider_autoplay_start') }}</span>
                            <span class="text-sm font-semibold" x-show="autoPlay">{{ __('messages.home_slider_autoplay_stop') }}</span>
                        </button>
                    @endif
                </div>
                
                <!-- Line 2: Description + Large Title -->
                <div class="flex flex-col md:flex-row items-end gap-8 md:gap-16">
                    <div class="w-full md:w-1/4">
                        <p class="text-sm leading-tight text-dark" style="font-family: 'Montserrat', sans-serif;">
                            {{ __('messages.home_slider_description') }}
                        </p>
                    </div>
                    <div class="w-full md:w-3/4 text-center">
                        <h2 class="uppercase font-bold text-3xl sm:text-4xl md:text-5xl lg:text-[3rem] xl:text-[3.5rem] 2xl:text-[4rem] tracking-widest leading-none text-dark" style="font-family: 'Montserrat', sans-serif;">
                            {{ __('messages.home_slider_title') }}
                        </h2>
                    </div>
                </div>
            </div>
            
        <!-- Slider Container -->
            <div class="relative w-full h-max aspect-[16/9] md:aspect-[16/6] overflow-hidden">
                @if($this->sliders->count() > 1)
                    <div class="pointer-events-none absolute top-1/2 left-0 right-0 z-30 -translate-y-1/2 transform flex items-center justify-between px-4 md:px-6">
                        <button @click="prevSlide()" 
                                class="pointer-events-auto bg-white/90 hover:bg-white text-dark p-3 md:p-4 rounded-full transition-all duration-300 shadow-lg hover:shadow-xl backdrop-blur-sm border-2 border-dark/20 hover:border-dark/40"
                                aria-label="{{ __('messages.home_slider_previous_slide') }}">
                            <svg class="w-6 h-6 md:w-7 md:h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <button @click="nextSlide()" 
                                class="pointer-events-auto bg-white/90 hover:bg-white text-dark p-3 md:p-4 rounded-full transition-all duration-300 shadow-lg hover:shadow-xl backdrop-blur-sm border-2 border-dark/20 hover:border-dark/40"
                                aria-label="{{ __('messages.home_slider_next_slide') }}">
                            <svg class="w-6 h-6 md:w-7 md:h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                @endif
                @foreach($this->sliders as $index => $slider)
                    <div class="absolute inset-0 transition-opacity duration-1000 ease-in-out"
                         :class="{ 'opacity-100': currentSlide === {{ $index }}, 'opacity-0': currentSlide !== {{ $index }} }">

                    <!-- Background Image -->
                    @php($bgUrl = $slider->getImageUrl('slider_large') ?? $slider->getImageUrl('slider'))
                        @if($bgUrl)
                            <img src="{{ $bgUrl }}" 
                                 class="w-full h-full object-cover" 
                                 alt="{{ $slider->getTranslatedTitle() }}"
                                 loading="lazy">
                        @else
                            <div class="w-full h-full bg-gray-300 flex items-center justify-center">
                                <span class="text-gray-500">{{ __('messages.home_slider_placeholder_alt') }}</span>
                            </div>
                    @endif

                    <!-- Overlay -->
                        <div class="absolute inset-0 bg-black bg-opacity-30"></div>

                        <!-- Slider Content -->
                        <div class="absolute inset-0 z-20 flex items-center justify-center">
                            <div class="max-w-4xl mx-auto text-center px-4">
                            <div class="space-y-6">
                                <!-- Title -->
                                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold leading-tight text-white">
                                    {{ $slider->getTranslatedTitle() }}
                                </h1>

                                <!-- Description -->
                                    @if($slider->getTranslatedDescription())
                                        <p class="text-sm sm:text-base lg:text-lg max-w-3xl mx-auto leading-tight text-white opacity-90">
                                        {{ $slider->getTranslatedDescription() }}
                                    </p>
                                @endif

                                <!-- Button -->
                                    @if($slider->getTranslatedButtonText() && $slider->button_url)
                                    <div class="pt-4">
                                        <a href="{{ $slider->button_url }}"
                                               class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white rounded-full hover:opacity-90 transition-colors duration-300 shadow-lg bg-dark">
                                            {{ $slider->getTranslatedButtonText() }}
                                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
                
                <!-- Decorative Elements -->
                <div class="hidden md:block absolute z-10 h-48 aspect-square rotate-45 -top-32 right-[30%] bg-sage"></div>
                <div class="hidden md:block absolute z-10 h-96 aspect-square rotate-45 -bottom-60 -left-60 bg-sage"></div>
            </div>
        </div>

        <!-- Dots Navigation -->
        @if($this->sliders->count() > 1)
            <div class="absolute bottom-6 left-1/2 transform -translate-x-1/2 z-10 flex space-x-2">
                @foreach($this->sliders as $index => $slider)
                    <button @click="goToSlide({{ $index }})"
                            class="w-3 h-3 rounded-full transition-all duration-300 bg-dark"
                            :class="currentSlide === {{ $index }} ? 'opacity-100' : 'opacity-50'">
                        <span class="sr-only">{{ __('messages.home_slider_tagline') }} {{ $index + 1 }}</span>
                    </button>
                @endforeach
            </div>
        @endif
    @else
        <!-- No sliders message -->
        <div class="space-y-14 py-16">
            <!-- Hero Text Section - Simple 2 Line Layout -->
            <div class="w-full px-4 sm:px-6 lg:px-8">
                <!-- Line 1: Tagline -->
                <div class="mb-8">
                    <h1 class="uppercase text-4xl font-medium text-dark" style="font-family: 'Montserrat', sans-serif;">
                        {{ __('messages.home_slider_tagline') }}
                    </h1>
                </div>
                
                <!-- Line 2: Description + Large Title -->
                <div class="flex flex-col md:flex-row items-end gap-8 md:gap-16">
                    <div class="w-full md:w-1/4">
                        <p class="text-sm leading-tight text-dark" style="font-family: 'Montserrat', sans-serif;">
                            {{ __('messages.home_slider_description') }}
                        </p>
                    </div>
                    <div class="w-full md:w-3/4 text-center">
                        <h2 class="uppercase font-bold text-3xl sm:text-4xl md:text-5xl lg:text-[3rem] xl:text-[3.5rem] 2xl:text-[4rem] tracking-widest leading-none text-dark" style="font-family: 'Montserrat', sans-serif;">
                            {{ __('messages.home_slider_title') }}
                        </h2>
                    </div>
                </div>
            </div>
            
            <!-- Placeholder Image -->
            <div class="relative w-full h-max aspect-[16/9] md:aspect-[16/6] overflow-hidden">
                <div class="w-full h-full bg-gray-300 flex items-center justify-center">
                    <span class="text-gray-500">{{ __('messages.home_slider_placeholder_alt') }}</span>
                </div>
                
                <!-- Decorative Elements -->
                <div class="hidden md:block absolute z-10 h-48 aspect-square rotate-45 -top-32 right-[30%] bg-sage"></div>
                <div class="hidden md:block absolute z-10 h-96 aspect-square rotate-45 -bottom-60 -left-60 bg-sage"></div>
            </div>
        </div>
    @endif
</section>
