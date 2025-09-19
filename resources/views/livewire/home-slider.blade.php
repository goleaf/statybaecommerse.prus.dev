<div class="relative overflow-hidden bg-gray-50" 
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
        <!-- Slider Container -->
        <div class="relative h-96 md:h-[500px] lg:h-[600px]">
            @foreach($this->sliders as $index => $slider)
                <div class="absolute inset-0 transition-opacity duration-1000 ease-in-out bg-var"
                     :class="{ 'opacity-100': currentSlide === {{ $index }}, 'opacity-0': currentSlide !== {{ $index }} }"
                     data-color="{{ $slider->background_color }}">
                    
                    <!-- Background Image -->
                    @if($slider->image)
                        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat bg-img-var" data-bg-img="{{ asset('storage/' . $slider->image) }}">
                        </div>
                    @endif
                    
                    <!-- Overlay -->
                    <div class="absolute inset-0 bg-black bg-opacity-30"></div>
                    
                    <!-- Content -->
                    <div class="relative z-10 flex items-center justify-center h-full">
                        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                            <div class="space-y-6">
                                <!-- Title -->
                                <h1 class="text-3xl sm:text-4xl lg:text-6xl font-bold leading-tight text-var" data-text-color="{{ $slider->text_color }}">
                                    {{ $slider->getTranslatedTitle() }}
                                </h1>
                                
                                <!-- Description -->
                                @if($slider->getTranslatedDescription())
                                    <p class="text-lg sm:text-xl lg:text-2xl max-w-3xl mx-auto leading-relaxed text-var opacity-90" data-text-color="{{ $slider->text_color }}">
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
                </div>
            @endforeach
        </div>
        
        <!-- Navigation Arrows -->
        @if($this->sliders->count() > 1)
            <!-- Previous Button -->
            <button @click="prevSlide()" 
                    class="absolute left-4 top-1/2 transform -translate-y-1/2 z-20 bg-white bg-opacity-20 hover:bg-opacity-30 text-white p-3 rounded-full transition-all duration-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            
            <!-- Next Button -->
            <button @click="nextSlide()" 
                    class="absolute right-4 top-1/2 transform -translate-y-1/2 z-20 bg-white bg-opacity-20 hover:bg-opacity-30 text-white p-3 rounded-full transition-all duration-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        @endif
        
        <!-- Dots Navigation -->
        @if($this->sliders->count() > 1)
            <div class="absolute bottom-6 left-1/2 transform -translate-x-1/2 z-20 flex space-x-2">
                @foreach($this->sliders as $index => $slider)
                    <button @click="goToSlide({{ $index }})" 
                            class="w-3 h-3 rounded-full transition-all duration-300"
                            :class="currentSlide === {{ $index }} ? 'bg-white' : 'bg-white bg-opacity-50'">
                    </button>
                @endforeach
            </div>
        @endif
        
        <!-- Auto-play Toggle -->
        @if($this->sliders->count() > 1)
            <div class="absolute top-4 right-4 z-20">
                <button @click="autoPlay = !autoPlay" 
                        class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white p-2 rounded-full transition-all duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!autoPlay">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h1m4 0h1m-6-8h8a2 2 0 012 2v8a2 2 0 01-2 2H8a2 2 0 01-2-2V8a2 2 0 012-2z"></path>
                    </svg>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="autoPlay">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6l4-3-4-3zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </button>
            </div>
        @endif
    @else
        <!-- No sliders message -->
        <div class="h-96 md:h-[500px] lg:h-[600px] flex items-center justify-center bg-gray-100">
            <div class="text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <p class="text-gray-500 text-lg">No slides available</p>
            </div>
        </div>
    @endif
</div>
