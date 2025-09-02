@php($layout = 'layouts.templates.app')

<x-dynamic-component :component="$layout">
    @section('meta')
        <x-meta
                :title="__('Brands') . ' - ' . config('app.name')"
                :description="__('Browse our brands catalog by locale.')"
                :prev="$brands->previousPageUrl()"
                :next="$brands->nextPageUrl()"
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

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">{{ __('Brands') }}</h1>
            <div class="flex items-center gap-2">
                <label for="sort" class="sr-only">{{ __('Sort') }}</label>
                <select id="sort" class="rounded-md border-gray-300 text-sm"
                        onchange="location.href='?sort='+this.value;">
                    <option value="name_asc" {{ request('sort') !== 'name_desc' ? 'selected' : '' }}>{{ __('Name (A–Z)') }}</option>
                    <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>{{ __('Name (Z–A)') }}</option>
                </select>
            </div>
        </div>

        @if ($brands->isEmpty())
            <div class="text-slate-500">{{ __('No brands available.') }}</div>
        @else
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach ($brands as $brand)
                    <a href="{{ route('brand.show', ['locale' => app()->getLocale(), 'slug' => $brand->trans('slug') ?? $brand->slug]) }}"
                       class="block border rounded-lg p-4 hover:shadow-sm">
                        <div class="aspect-square bg-gray-50 flex items-center justify-center mb-3">
                            @php($thumb = method_exists($brand, 'getFirstMediaUrl') ? ($brand->getFirstMediaUrl(config('shopper.media.storage.thumbnail_collection')) ?: ($brand->getFirstMediaUrl(config('shopper.media.storage.collection_name'), 'small') ?: $brand->getFirstMediaUrl(config('shopper.media.storage.collection_name')))) : null)
                            @if ($thumb)
                                <img loading="lazy" src="{{ $thumb }}"
                                     alt="{{ $brand->trans('name') ?? $brand->name }}"
                                     class="max-h-24 object-contain" />
                            @endif
                        </div>
                        <div class="text-lg font-medium">{{ $brand->trans('name') ?? $brand->name }}</div>
                        @if (!empty($brand->website))
                            <div class="text-sm text-slate-500 truncate">{{ $brand->website }}</div>
                        @endif
                    </a>
                @endforeach
            </div>

            <nav class="mt-6" aria-label="Pagination">{{ $brands->links() }}</nav>
        @endif
    </div>
</x-dynamic-component>
