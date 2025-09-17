@props([
    'title' => null,
    'maxWidth' => 'md', // sm, md, lg, xl, 2xl
    'closeable' => true,
    'show' => false,
])

@php
    $maxWidthClasses = match ($maxWidth) {
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
        default => 'sm:max-w-md',
    };
@endphp

<div
     x-data="{ show: @js($show) }"
     x-show="show"
     x-on:close-modal.window="show = false"
     x-on:open-modal.window="show = true"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    {{-- Backdrop --}}
    <div
         x-show="show"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-500/75 transition-opacity"
         @if ($closeable) @click="show = false" @endif></div>

    {{-- Modal --}}
    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div
             x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all {{ $maxWidthClasses }} sm:w-full dark:bg-gray-800">
            @if ($title || $closeable)
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        @if ($title)
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                                {{ $title }}
                            </h3>
                        @endif

                        @if ($closeable)
                            <button
                                    @click="show = false"
                                    class="rounded-md p-1 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-gray-500 dark:hover:text-gray-400">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Content --}}
            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 dark:bg-gray-800">
                {{ $slot }}
            </div>

            {{-- Footer --}}
            @if (isset($footer))
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 dark:bg-gray-700">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('modal', () => ({
                show: false,
                open() {
                    this.show = true
                },
                close() {
                    this.show = false
                }
            }))
        })
    </script>
@endpush
