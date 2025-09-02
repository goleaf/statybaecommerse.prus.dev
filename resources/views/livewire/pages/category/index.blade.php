@section('meta')
    <x-meta
            :title="__('Categories') . ' - ' . config('app.name')"
            :description="__('Browse product categories')"
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
    <h1 class="text-2xl font-semibold mb-6">{{ __('Categories') }}</h1>
    @if ($roots->isEmpty())
        <div class="text-slate-500">{{ __('No categories available.') }}</div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <aside class="lg:col-span-1">
                <h2 class="text-sm font-semibold text-gray-900 mb-3">{{ __('Browse categories') }}</h2>
                <x-category.tree :nodes="$tree" />
            </aside>
            <div class="lg:col-span-3 grid grid-cols-2 md:grid-cols-3 gap-6">
                @foreach ($roots as $root)
                    <a href="{{ route('category.show', ['locale' => app()->getLocale(), 'slug' => $root->trans('slug') ?? $root->slug]) }}"
                       class="block border rounded-lg p-4 hover:shadow-sm">
                        <div class="aspect-square bg-gray-50 flex items-center justify-center mb-3">
                            @php($thumb = $root->getFirstMediaUrl(config('shopper.media.storage.thumbnail_collection')) ?: ($root->getFirstMediaUrl(config('shopper.media.storage.collection_name'), 'small') ?: $root->getFirstMediaUrl(config('shopper.media.storage.collection_name'))))
                            @if ($thumb)
                                <img loading="lazy" src="{{ $thumb }}"
                                     alt="{{ $root->trans('name') ?? $root->name }}"
                                     class="max-h-24 object-contain" />
                            @endif
                        </div>
                        <div class="text-base font-medium">{{ $root->trans('name') ?? $root->name }}</div>
                        @if ($root->trans('description') ?? $root->description)
                            <div class="mt-1 text-sm text-slate-500 line-clamp-2">
                                {{ \Illuminate\Support\Str::limit(strip_tags($root->trans('description') ?? $root->description), 100) }}
                            </div>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
