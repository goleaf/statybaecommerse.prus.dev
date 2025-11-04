@props([
    'cardPadding' => 'p-8 sm:p-10',
    'cardClass' => '',
    'maxWidth' => 'max-w-6xl',
])

@php
    /** @var Illuminate\View\ComponentSlot|null $aside */
    $hasAside = isset($aside) && ! $aside->isEmpty();
@endphp

<div {{ $attributes->class([
    'relative min-h-screen overflow-hidden bg-sage text-dark',
]) }}>
    <div class="relative z-10 flex min-h-screen items-center py-12 sm:py-16">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 {{ $maxWidth }}">
            <div @class([
                'grid grid-cols-1 gap-10',
                'md:grid-cols-2' => $hasAside,
                'lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]' => $hasAside,
                'lg:max-w-xl lg:mx-auto' => ! $hasAside,
            ])>
                @if($hasAside)
                    <div class="hidden md:flex flex-col justify-between rounded-3xl border border-ash bg-white/70 text-black p-10 shadow-2xl [&_*]:!text-black [&_svg]:!text-black">
                        {{ $aside }}
                    </div>
                @endif

                <div class="relative">
                    <div class="rounded-3xl border border-ash bg-white/95 text-slate-900 shadow-xl">
                        <div class="{{ $cardPadding }} {{ $cardClass }}">
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
