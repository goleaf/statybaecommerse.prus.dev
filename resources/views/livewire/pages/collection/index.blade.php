@section('meta')
    <x-meta
            :title="__('Collections') . ' - ' . config('app.name')"
            :description="__('Browse product collections')"
            canonical="{{ url()->current() }}" />
@endsection

<div class="container mx-auto px-4 py-8" wire:loading.attr="aria-busy" aria-busy="false">
    @if (session('status'))
        <x-alert type="success" class="mb-4">{{ session('status') }}</x-alert>
    @endif
    @if (session('error'))
        <x-alert type="error" class="mb-4">{{ session('error') }}</x-alert>
    @endif
    @if ($errors->any())
        <x-alert type="error" class="mb-4">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif
    <h1 class="text-2xl font-semibold mb-6">{{ __('Collections') }}</h1>

    @if ($collections->isEmpty())
        <div class="text-slate-500">{{ __('No collections available.') }}</div>
    @else
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach ($collections as $collection)
                <a href="{{ route('collection.show', ['locale' => app()->getLocale(), 'slug' => $collection->trans('slug') ?? $collection->slug]) }}"
                   class="block border rounded-lg p-4 hover:shadow-sm">
                    <div class="aspect-square bg-gray-50 flex items-center justify-center mb-3">
                        @php($thumb = $collection->getFirstMediaUrl(config('shopper.media.storage.thumbnail_collection')) ?: ($collection->getFirstMediaUrl(config('shopper.media.storage.collection_name'), 'small') ?: $collection->getFirstMediaUrl(config('shopper.media.storage.collection_name'))))
                        @if ($thumb)
                            <img loading="lazy" src="{{ $thumb }}"
                                 alt="{{ $collection->trans('name') ?? $collection->name }}"
                                 class="max-h-24 object-contain" />
                        @endif
                    </div>
                    <div class="text-base font-medium">{{ $collection->trans('name') ?? $collection->name }}</div>
                    @if ($collection->trans('description') ?? $collection->description)
                        <div class="mt-1 text-sm text-slate-500 line-clamp-2">
                            {{ \Illuminate\Support\Str::limit(strip_tags($collection->trans('description') ?? $collection->description), 100) }}
                        </div>
                    @endif
                </a>
            @endforeach
        </div>
    @endif
</div>
