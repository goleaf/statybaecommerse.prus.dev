<section class="relative bg-sage z-10 overflow-hidden" 
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
     }">
    
    @if($this->sliders->count() > 0)
        <div class="space-y-14 py-16">
            <!-- Hero Text Section - Simple 2 Line Layout -->
            <div class="w-full">
                <!-- Line 1: "Statyboms be rūpesčių" with autoplay toggle -->
                <div class="mb-8 flex items-center justify-between">
                    <h1 class="uppercase text-4xl font-medium text-dark font-montserrat">
                        {{ __('home_slider.tagline') }}
                    </h1>
                    @if($this->sliders->count() > 1)
                        <button @click="autoPlay = !autoPlay" 
                                class="inline-flex items-center gap-2 px-3 py-2 rounded-full border border-sage text-sage bg-transparent hover:bg-sage hover:text-dark transition-colors duration-200"
                                :aria-pressed="autoPlay">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!autoPlay">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6l4-3-4-3z"></path>
                            </svg>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="autoPlay">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6"></path>
                            </svg>
                            <span class="text-sm font-semibold" x-show="!autoPlay">{{ __('home_slider.autoplay_start') }}</span>
                            <span class="text-sm font-semibold" x-show="autoPlay">{{ __('home_slider.autoplay_stop') }}</span>
                        </button>
                    @endif
                </div>
                
                <!-- Line 2: Description + Large Title -->
                <div class="flex items-end gap-16">
                    <div class="w-1/4">
                        <p class="text-sm text-dark font-montserrat leading-tight">
                            {{ __('home_slider.description') }}
                        </p>
                    </div>
                    <div class="w-3/4 text-center">
                        <h2 class="uppercase font-bold text-3xl sm:text-4xl md:text-5xl lg:text-[3rem] xl:text-[3.5rem] 2xl:text-[4rem] text-dark font-montserrat tracking-widest leading-none">
                            {{ __('home_slider.title') }}
                        </h2>
                    </div>
                </div>
            </div>
            
            <!-- Slider Container -->
            <div class="relative w-full h-max aspect-[16/9] md:aspect-[16/6] overflow-hidden">
                @if($this->sliders->count() > 1)
                    <div class="pointer-events-none absolute top-1/2 left-0 right-0 z-20 -translate-y-1/2 transform flex items-center justify-between px-4">
                        <button @click="prevSlide()" class="pointer-events-auto bg-black/30 hover:bg-black/40 text-white p-3 rounded-full transition-all duration-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <button @click="nextSlide()" class="pointer-events-auto bg-black/30 hover:bg-black/40 text-white p-3 rounded-full transition-all duration-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                @endif
                @foreach($this->sliders as $index => $slider)
                    <div class="absolute inset-0 transition-opacity duration-1000 ease-in-out"
                         :class="{ 'opacity-100': currentSlide === {{ $index }}, 'opacity-0': currentSlide !== {{ $index }} }">
                        
                        <!-- Background Image -->
                        @if($slider->image)
                            <img src="{{ asset('storage/' . $slider->image) }}" 
                                 class="w-full h-full object-cover relative z-10" 
                                 alt="{{ $slider->getTranslatedTitle() }}">
                        @else
                            <img src="/images/banner/banner-1.jpg" 
                                 class="w-full h-full object-cover relative z-10" 
                                 alt="{{ __('home_slider.placeholder_alt') }}">
                    @endif

                        <!-- Overlay -->
                        <div class="absolute inset-0 bg-black bg-opacity-30 z-0"></div>
                        
                        <!-- Slider Content -->
                        <div class="absolute inset-0 z-10 flex items-center justify-center">
                            <div class="max-w-4xl mx-auto text-center">
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
                                               class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gray-800 rounded-full hover:bg-gray-700 transition-colors duration-300 shadow-lg">
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
                        
                        <!-- Decorative Elements -->
                        <div class="hidden md:block absolute z-10 bg-sage h-48 aspect-square rotate-45 -top-32 right-[30%]"></div>
                        <div class="hidden md:block absolute z-10 bg-sage h-96 aspect-square rotate-45 -bottom-60 -left-60"></div>
                </div>
            @endforeach
            </div>
        </div>

        
        
        <!-- Dots Navigation -->
        @if($this->sliders->count() > 1)
            <div class="absolute bottom-6 left-1/2 transform -translate-x-1/2 z-10 flex space-x-2">
                @foreach($this->sliders as $index => $slider)
                    <button @click="goToSlide({{ $index }})"
                            class="w-3 h-3 rounded-full transition-all duration-300"
                            :class="currentSlide === {{ $index }} ? 'bg-dark' : 'bg-dark bg-opacity-50'">
                    </button>
                @endforeach
            </div>
        @endif

        <!-- Auto-play Toggle moved into header above -->
    @else
        <!-- No sliders message -->
        <div class="space-y-14 py-16">
            <!-- Hero Text Section - Simple 2 Line Layout -->
            <div class="w-full">
                <!-- Line 1: "Statyboms be rūpesčių" -->
                <div class="mb-8">
                    <h1 class="uppercase text-4xl font-medium text-dark font-montserrat">
                        {{ __('home_slider.tagline') }}
                    </h1>
                </div>
                
                <!-- Line 2: Description + Large Title -->
                <div class="flex items-end gap-16">
                    <div class="w-1/4">
                        <p class="text-sm text-dark font-montserrat leading-tight">
                            {{ __('home_slider.description') }}
                        </p>
                    </div>
                    <div class="w-3/4 text-center">
                        <h2 class="uppercase font-bold text-3xl sm:text-4xl md:text-5xl lg:text-[3rem] xl:text-[3.5rem] 2xl:text-[4rem] text-dark font-montserrat tracking-widest leading-none">
                            {{ __('home_slider.title') }}
                        </h2>
                    </div>
                </div>
            </div>
            
            <!-- Placeholder Image -->
            <div class="relative w-full h-max aspect-[16/9] md:aspect-[16/6] overflow-hidden">
                <img src="/images/banner/banner-1.jpg" 
                     class="w-full h-full object-cover relative z-10" 
                     alt="{{ __('home_slider.placeholder_alt') }}">
                
                <!-- Decorative Elements -->
                <div class="hidden md:block absolute z-10 bg-sage h-48 aspect-square rotate-45 -top-32 right-[30%]"></div>
                <div class="hidden md:block absolute z-10 bg-sage h-96 aspect-square rotate-45 -bottom-60 -left-60"></div>
            </div>
        </div>
    @endif
</section>