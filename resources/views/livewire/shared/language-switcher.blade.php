<div class="text-sm">
    <div class="inline-flex items-center gap-2">
        @foreach ($this->links as $link)
            <form method="POST" action="{{ route('locale.switch') }}" class="inline">
                @csrf
                <input type="hidden" name="locale" value="{{ $link['locale'] }}">
                <input type="hidden" name="redirect_to" value="{{ $link['url'] }}">
                <button type="submit" @class([
                    'px-2 py-1 rounded',
                    'bg-gray-900 text-white' => $link['active'],
                    'text-gray-700 hover:underline' => ! $link['active'],
                ])>
                    {{ $link['label'] }}
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
