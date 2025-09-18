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
    'relative min-h-screen overflow-hidden bg-slate-950 text-slate-50',
]) }}>
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-slate-900/95 to-indigo-950"></div>
        <div class="absolute -top-32 -left-24 h-80 w-80 rounded-full bg-indigo-600/25 blur-3xl"></div>
        <div class="absolute -bottom-40 -right-24 h-96 w-96 rounded-full bg-blue-500/20 blur-3xl"></div>
        <div class="absolute inset-0 opacity-[0.18]" style="background-image: radial-gradient(circle at 10% 20%, rgba(99,102,241,0.25) 0, transparent 55%), radial-gradient(circle at 90% 10%, rgba(56,189,248,0.15) 0, transparent 55%), radial-gradient(circle at 50% 80%, rgba(129,140,248,0.22) 0, transparent 60%);"></div>
        <div class="absolute inset-0 opacity-20" style="background-image: url('data:image/svg+xml,%3Csvg width="80" height="80" viewBox="0 0 80 80" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd" opacity="0.6"%3E%3Cg stroke="%23FFFFFF" stroke-opacity="0.08"%3E%3Cpath d="M0 79.5H79.5V0"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
    </div>

    <div class="relative z-10 flex min-h-screen items-center py-12 sm:py-16">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 {{ $maxWidth }}">
            <div @class([
                'grid grid-cols-1 gap-10',
                'lg:grid-cols-[minmax(0,1fr)_minmax(360px,420px)]' => $hasAside,
                'lg:max-w-xl lg:mx-auto' => ! $hasAside,
            ])>
                @if($hasAside)
                    <div class="hidden lg:flex flex-col justify-between rounded-3xl border border-white/15 bg-white/10 p-10 shadow-2xl backdrop-blur-xl">
                        {{ $aside }}
                    </div>
                @endif

                <div class="relative">
                    <div class="rounded-3xl border border-white/25 bg-white/90 text-slate-900 shadow-xl backdrop-blur-xl">
                        <div class="{{ $cardPadding }} {{ $cardClass }}">
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
