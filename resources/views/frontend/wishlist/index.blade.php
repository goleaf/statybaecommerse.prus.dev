@extends('frontend.layouts.app')

@section('title', __('My Wishlist'))

@section('content')
    <div class="container mx-auto px-4 py-10">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('My Wishlist') }}</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">{{ __('Keep track of the products you love and move them to your cart whenever you are ready.') }}</p>
                </div>

                @if ($wishlistItems->count() > 0)
                    <form method="POST" action="{{ route('frontend.wishlist.clear') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-md border border-red-200 text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            {{ __('Clear wishlist') }}
                        </button>
                    </form>
                @endif
            </div>

            @if (session('status'))
                <div class="mb-6 rounded-md bg-green-50 p-4 text-sm text-green-800 border border-green-200">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->has('wishlist'))
                <div class="mb-6 rounded-md bg-red-50 p-4 text-sm text-red-800 border border-red-200">
                    {{ $errors->first('wishlist') }}
                </div>
            @endif

            @if ($wishlistItems->isEmpty())
                <div class="bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-700 rounded-2xl p-12 text-center">
                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('Your wishlist is empty') }}</h2>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        {{ __('Explore our products and tap the heart icon to save items for later.') }}
                    </p>
                    <a href="{{ route('home') }}"
                       class="mt-6 inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-md shadow focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        {{ __('Browse products') }}
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($wishlistItems as $item)
                        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden shadow-sm flex flex-col">
                            <div class="px-5 py-4 flex-1 flex flex-col gap-3">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Saved product') }}</p>
                                    <h3 class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ $item->display_name }}
                                    </h3>
                                </div>

                                <p class="text-sm text-gray-600 dark:text-gray-300">
                                    {{ $item->formatted_current_price }}
                                </p>

                                <div class="mt-auto">
                                    <form method="POST" action="{{ route('frontend.wishlist.remove') }}">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="product_id" value="{{ $item->product_id }}">
                                        @if ($item->variant_id)
                                            <input type="hidden" name="variant_id" value="{{ $item->variant_id }}">
                                        @endif
                                        <button type="submit"
                                                class="inline-flex items-center px-3 py-2 text-sm font-medium text-red-600 hover:text-red-700 focus:outline-none">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            {{ __('Remove') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $wishlistItems->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
