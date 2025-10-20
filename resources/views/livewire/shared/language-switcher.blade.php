<div class="text-sm">
    <div class="inline-flex items-center gap-2">
        @foreach ($links as $loc => $href)
            <form method="POST" action="{{ route('locale.switch') }}" class="inline">
                @csrf
                <input type="hidden" name="locale" value="{{ $loc }}">
                <input type="hidden" name="redirect_to" value="{{ $href }}">
                <button type="submit" @class([
                    'px-2 py-1 rounded',
                    'bg-gray-900 text-white' => $loc === $current,
                    'text-gray-700 hover:underline' => $loc !== $current,
                ])>
                    {{ strtoupper($loc) }}
                </button>
            </form>
        @endforeach
    </div>
    @isset($slot)
        {{ $slot }}
    @endisset
    @section('hreflang')
        @include('components.hreflang')
    @endsection
</div>
