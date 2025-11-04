@php($layout = 'layouts.base')

<x-dynamic-component :component="$layout">
    @section('meta')
        <x-meta
                :title="__('nav_brands') . ' - ' . config('app.name')"
                :description="__('brands_browse')"
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
            <h1 class="text-2xl font-semibold">{{ __('nav_brands') }}</h1>
            <div class="flex items-center gap-2">
                <label for="sort" class="sr-only">{{ __('sort') }}</label>
                <select id="sort" class="rounded-md border-gray-300 text-sm"
                        onchange="location.href='?sort='+this.value;">
                    <option value="name_asc" {{ request('sort') !== 'name_desc' ? 'selected' : '' }}>{{ __('name_asc') }}</option>
                    <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>{{ __('name_desc') }}</option>
                </select>
            </div>
        </div>

        @if ($brands->isEmpty())
            <div class="text-slate-500">{{ __('brands_no_available') }}</div>
        @else
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach ($brands as $brand)
                    <a href="{{ route('brands.show', ['locale' => app()->getLocale(), 'brand' => $brand->trans('slug') ?? $brand->slug]) }}"
                       class="block border rounded-lg p-4 hover:shadow-sm">
                        <div class="aspect-square bg-gray-50 flex items-center justify-center mb-3">
                            @if ($brand->getLogoUrl('md'))
                                <img loading="lazy" 
                                     src="{{ $brand->getLogoUrl('md') }}"
                                     srcset="{{ $brand->getLogoUrl('sm') }} 128w, {{ $brand->getLogoUrl('md') }} 200w, {{ $brand->getLogoUrl('lg') }} 400w"
                                     sizes="(max-width: 640px) 128px, 200px"
                                     alt="{{ $brand->trans('name') ?? $brand->name }}"
                                     width="200" height="200"
                                     class="max-h-24 object-contain" />
                            @elseif ($brand->getLogoUrl())
                                <img loading="lazy" src="{{ $brand->getLogoUrl() }}"
                                     alt="{{ $brand->trans('name') ?? $brand->name }}"
                                     class="max-h-24 object-contain" />
                            @else
                                <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                    <span class="text-xs text-gray-500 font-medium">{{ strtoupper(substr($brand->trans('name') ?? $brand->name, 0, 2)) }}</span>
                                </div>
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
