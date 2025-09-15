<div class="voice-search relative" 
     x-data="{
         isListening: @entangle('isListening'),
         isSupported: @entangle('isSupported'),
         query: @entangle('query'),
         status: @entangle('status')
     }"
     x-init="
         // Check for voice recognition support
         if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
             isSupported = false;
         }
         
         // Listen for voice recognition events
         $wire.on('start-voice-recognition', () => {
             startVoiceRecognition();
         });
         
         $wire.on('stop-voice-recognition', () => {
             stopVoiceRecognition();
         });
         
         function startVoiceRecognition() {
             const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
             const recognition = new SpeechRecognition();
             
             recognition.continuous = false;
             recognition.interimResults = false;
             recognition.lang = '{{ app()->getLocale() }}-{{ strtoupper(app()->getLocale()) }}';
             
             recognition.onstart = function() {
                 isListening = true;
                 status = 'Listening...';
             };
             
             recognition.onresult = function(event) {
                 const transcript = event.results[0][0].transcript;
                 query = transcript;
                 $wire.processVoiceResult(transcript);
             };
             
             recognition.onerror = function(event) {
                 status = 'Error: ' + event.error;
                 isListening = false;
             };
             
             recognition.onend = function() {
                 isListening = false;
                 if (status === 'Listening...') {
                     status = '';
                 }
             };
             
             recognition.start();
         }
         
         function stopVoiceRecognition() {
             // Voice recognition will stop automatically
         }
     ">
    
    {{-- Voice Search Input --}}
    <div class="relative">
        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
            </svg>
        </div>
        
        <input
            wire:model.live.debounce.300ms="query"
            type="text"
            placeholder="{{ __('frontend.search.voice_placeholder') }}"
            class="block w-full pl-10 pr-20 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400"
            autocomplete="off"
            x-ref="searchInput"
        />
        
        {{-- Voice Button --}}
        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
            @if($isSupported)
                <button
                    wire:click="{{ $isListening ? 'stopListening' : 'startListening' }}"
                    type="button"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-full text-white transition-colors duration-200"
                    :class="isListening ? 'bg-red-500 hover:bg-red-600' : 'bg-blue-500 hover:bg-blue-600'"
                    :title="isListening ? '{{ __('frontend.search.stop_listening') }}' : '{{ __('frontend.search.start_listening') }}'"
                >
                    @if($isListening)
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                        </svg>
                    @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                    @endif
                </button>
            @else
                <div class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-300 text-gray-500" title="{{ __('frontend.search.voice_not_supported') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728" />
                    </svg>
                </div>
            @endif
        </div>
    </div>
    
    {{-- Status Message --}}
    <div x-show="status" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         class="mt-2 text-sm text-gray-600 dark:text-gray-400">
        <span x-text="status"></span>
    </div>
    
    {{-- Voice Search Tips --}}
    <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
        <div class="flex items-center space-x-4">
            <span>{{ __('frontend.search.voice_tip_1') }}</span>
            <span>{{ __('frontend.search.voice_tip_2') }}</span>
        </div>
    </div>
</div>
