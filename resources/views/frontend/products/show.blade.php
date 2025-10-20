@extends('components.layouts.base')

@section('title', $product->trans('name') ?? $product->name)

@section('content')
    <x-container class="py-10 space-y-8">
        <div class="grid gap-8 lg:grid-cols-2">
            <div class="space-y-4">
                @php($media = $product->getFirstMedia(config('media.storage.collection_name')))
                @if ($media)
                    <img src="{{ $media->getFullUrl() }}" alt="{{ $product->name }}" class="w-full rounded-xl object-cover">
                @else
                    <div class="h-96 rounded-xl bg-gray-100"></div>
                @endif
            </div>

            <div class="space-y-6">
                <div>
                    <p class="text-sm text-gray-500">{{ $product->brand?->name }}</p>
                    <h1 class="text-3xl font-semibold text-gray-900">{{ $product->trans('name') ?? $product->name }}</h1>
                </div>

                <div>
                    <p class="text-2xl font-semibold text-primary-600">
                        {{ optional($product->prices->first())->formatted ?? \Illuminate\Support\Number::currency((float) ($product->price ?? 0), current_currency(), app()->getLocale()) }}
                    </p>
                </div>

                <div class="prose max-w-none text-gray-700">
                    {!! nl2br(e($product->trans('description') ?? $product->description)) !!}
                </div>

                <form method="post" action="{{ route('frontend.cart.add') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <label class="block text-sm font-medium text-gray-700" for="quantity">{{ __('Quantity') }}</label>
                    <input id="quantity" name="quantity" type="number" min="1" value="1"
                           class="block w-24 rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <x-button type="submit">{{ __('Add to cart') }}</x-button>
                </form>
            </div>
        </div>

        <section class="space-y-4">
            <h2 class="text-2xl font-semibold text-gray-900">{{ __('Customer reviews') }}</h2>
            <div class="space-y-4">
                @forelse ($reviews as $review)
                    <article class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="font-medium text-gray-900">{{ $review->title ?? __('Review') }}</p>
                            <span class="text-sm text-gray-500">{{ $review->created_at->format('Y-m-d') }}</span>
                        </div>
                        <p class="mt-2 text-sm text-gray-700">{{ $review->content }}</p>
                        <p class="mt-2 text-sm text-gray-500">{{ __('Rating: :rating/5', ['rating' => $review->rating]) }}</p>
                    </article>
                @empty
                    <p class="text-gray-500">{{ __('No reviews yet.') }}</p>
                @endforelse
            </div>
        </section>

        <section class="space-y-4">
            <h2 class="text-2xl font-semibold text-gray-900">{{ __('Related products') }}</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @forelse ($relatedProducts as $related)
                    <a href="{{ route('frontend.products.show', $related) }}" class="rounded-lg border border-gray-200 bg-white shadow-sm">
                        @php($relatedMedia = $related->getFirstMedia(config('media.storage.collection_name')))
                        @if ($relatedMedia)
                            <img src="{{ $relatedMedia->getFullUrl() }}" alt="{{ $related->name }}" class="h-40 w-full rounded-t-lg object-cover">
                        @else
                            <div class="h-40 w-full rounded-t-lg bg-gray-100"></div>
                        @endif
                        <div class="p-3">
                            <p class="font-medium text-gray-900">{{ $related->trans('name') ?? $related->name }}</p>
                        </div>
                    </a>
                @empty
                    <p class="text-gray-500">{{ __('No related products found.') }}</p>
                @endforelse
            </div>
        </section>

        @auth
            <section class="space-y-4">
                <h2 class="text-2xl font-semibold text-gray-900">{{ __('Leave a review') }}</h2>
                <form method="post" action="{{ route('frontend.products.add-review', $product) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="rating">{{ __('Rating') }}</label>
                        <select id="rating" name="rating" class="mt-1 block w-32 rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            @for ($rating = 1; $rating <= 5; $rating++)
                                <option value="{{ $rating }}">{{ $rating }}</option>
                            @endfor
                        </select>
                    </div>
                    <x-input label="{{ __('Title') }}" name="title" />
                    <x-textarea label="{{ __('Content') }}" name="content" rows="4"></x-textarea>
                    <x-button type="submit">{{ __('Submit review') }}</x-button>
                </form>
            </section>
        @endauth
    </x-container>
@endsection
