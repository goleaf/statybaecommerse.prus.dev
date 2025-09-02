<div class="text-sm">
    <div class="inline-flex items-center gap-2">
        @foreach ($links as $loc => $href)
            <a href="{{ $href }}" @class([
                'px-2 py-1 rounded',
                'bg-gray-900 text-white' => $loc === $current,
                'text-gray-700 hover:underline' => $loc !== $current,
            ])>
                {{ strtoupper($loc) }}
            </a>
        @endforeach
    </div>
    @isset($slot)
        {{ $slot }}
    @endisset
    @section('hreflang')
        @include('components.hreflang')
    @endsection
</div>
