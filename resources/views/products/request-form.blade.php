@extends('layouts.app')

@section('title', __('translations.request_product') . ' - ' . $product->name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">
                    {{ __('translations.request_product') }}
                </h1>
                <p class="text-gray-600">
                    {{ __('translations.request_product_description') }}
                </p>
            </div>

            <!-- Product Info -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="flex items-center space-x-4">
                    @if($product->getMainImage())
                        <img src="{{ $product->getMainImage() }}" 
                             alt="{{ $product->name }}" 
                             class="w-16 h-16 object-cover rounded-lg">
                    @endif
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $product->name }}</h3>
                        <p class="text-sm text-gray-600">{{ $product->sku }}</p>
                        @if($product->price)
                            <p class="text-sm font-medium text-green-600">
                                {{ number_format($product->price, 2) }} €
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Request Form -->
            <form action="{{ route('product-requests.store') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('translations.name') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', auth()->user()->name ?? '') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror"
                               required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('translations.email') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email', auth()->user()->email ?? '') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror"
                               required>
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('translations.phone') }}
                        </label>
                        <input type="tel" 
                               id="phone" 
                               name="phone" 
                               value="{{ old('phone') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('phone') border-red-500 @enderror">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="requested_quantity" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('translations.quantity') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               id="requested_quantity" 
                               name="requested_quantity" 
                               value="{{ old('requested_quantity', 1) }}"
                               min="1" 
                               max="999"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('requested_quantity') border-red-500 @enderror"
                               required>
                        @error('requested_quantity')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('translations.message') }}
                    </label>
                    <textarea id="message" 
                              name="message" 
                              rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('message') border-red-500 @enderror"
                              placeholder="{{ __('translations.additional_information_optional') }}">{{ old('message') }}</textarea>
                    @error('message')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between pt-6 border-t">
                    <a href="{{ localized_route('products.show', $product) }}" 
                       class="text-gray-600 hover:text-gray-800 transition-colors">
                        {{ __('translations.back_to_product') }}
                    </a>
                    
                    <button type="submit" 
                            class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        {{ __('translations.submit_request') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

