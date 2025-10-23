@props([
    'icon' => 'rewards',
    'message',
])

<li {{ $attributes->class('flex items-start gap-3') }}>
    <div class="mt-1 flex h-7 w-7 items-center justify-center rounded-full bg-white/15">
        <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            @switch($icon)
                @case('rewards')
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    @break

                @case('checkout')
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l3 2" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 22a10 10 0 100-20 10 10 0 000 20z" />
                    @break

                @case('records')
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m6 14h2a2 2 0 002-2V7a2 2 0 00-2-2h-2" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9h12M7 21H5a2 2 0 01-2-2V9" />
                    @break
            @endswitch
        </svg>
    </div>
    <span>{{ $message }}</span>
</li>
