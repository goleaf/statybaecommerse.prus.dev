@props([
    'currentStep' => 1,
    'steps' => [
        'cart' => [
            'number' => 1,
            'title' => 'Shopping Cart',
            'icon' =>
                'M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01',
        ],
        'shipping' => [
            'number' => 2,
            'title' => 'Shipping',
            'icon' =>
                'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z',
        ],
        'payment' => [
            'number' => 3,
            'title' => 'Payment',
            'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
        ],
        'review' => ['number' => 4, 'title' => 'Review', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
    ],
])

@php
    $stepKeys = array_keys($steps);
    $currentStepIndex = array_search($currentStep, array_column($steps, 'number'));
@endphp

<div class="checkout-steps">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
            @foreach ($steps as $key => $step)
                @php
                    $isActive = $step['number'] === $currentStep;
                    $isCompleted = $step['number'] < $currentStep;
                    $isUpcoming = $step['number'] > $currentStep;
                @endphp

                <div class="flex items-center {{ $loop->last ? '' : 'flex-1' }}">
                    {{-- Step Circle --}}
                    <div class="flex flex-col items-center">
                        <div class="relative">
                            {{-- Step Number/Icon --}}
                            <div
                                 class="w-12 h-12 rounded-full flex items-center justify-center border-2 transition-all duration-300
                                {{ $isActive
                                    ? 'bg-blue-600 border-blue-600 text-white'
                                    : ($isCompleted
                                        ? 'bg-green-600 border-green-600 text-white'
                                        : 'bg-white border-gray-300 text-gray-400') }}">

                                @if ($isCompleted)
                                    {{-- Checkmark for completed steps --}}
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M5 13l4 4L19 7"></path>
                                    </svg>
                                @else
                                    {{-- Step icon --}}
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="{{ $step['icon'] }}"></path>
                                    </svg>
                                @endif
                            </div>

                            {{-- Active Step Indicator --}}
                            @if ($isActive)
                                <div class="absolute -inset-1 bg-blue-600 rounded-full opacity-20 animate-pulse"></div>
                            @endif
                        </div>

                        {{-- Step Title --}}
                        <div class="mt-3 text-center">
                            <p
                               class="text-sm font-medium transition-colors duration-300
                                {{ $isActive ? 'text-blue-600' : ($isCompleted ? 'text-green-600' : 'text-gray-500') }}">
                                {{ __($step['title']) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ __('Step') }} {{ $step['number'] }}
                            </p>
                        </div>
                    </div>

                    {{-- Connector Line --}}
                    @if (!$loop->last)
                        <div
                             class="flex-1 h-0.5 mx-4 mt-6
                            {{ $isCompleted ? 'bg-green-600' : 'bg-gray-300' }}">
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Progress Bar --}}
        <div class="mt-8">
            <div class="bg-gray-200 rounded-full h-2 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-green-500 h-full transition-all duration-500 ease-out"
                     style="width: {{ ($currentStep / count($steps)) * 100 }}%"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-500 mt-2">
                <span>{{ __('Progress') }}</span>
                <span>{{ $currentStep }} {{ __('of') }} {{ count($steps) }}</span>
            </div>
        </div>
    </div>
</div>
