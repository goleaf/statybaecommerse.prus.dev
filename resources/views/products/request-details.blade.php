@extends('components.layouts.base')

@section('title', __('translations.product_request_details'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                {{ __('translations.product_request_details') }}
            </h1>
            <p class="text-gray-600">
                {{ __('translations.request_id') }}: #{{ $productRequest->id }}
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Product Information -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">
                        {{ __('translations.product_information') }}
                    </h2>
                    
                    <div class="flex items-start space-x-4">
                        @if($productRequest->product->getMainImage())
                            <img src="{{ $productRequest->product->getMainImage() }}" 
                                 alt="{{ $productRequest->product->name }}" 
                                 class="w-20 h-20 object-cover rounded-lg">
                        @endif
                        <div class="flex-1">
                            <h3 class="text-lg font-medium text-gray-900">
                                {{ $productRequest->product->name }}
                            </h3>
                            <p class="text-sm text-gray-600 mb-2">
                                {{ __('translations.sku') }}: {{ $productRequest->product->sku }}
                            </p>
                            @if($productRequest->product->price)
                                <p class="text-lg font-semibold text-green-600">
                                    {{ number_format($productRequest->product->price, 2) }} €
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Request Details -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">
                        {{ __('translations.request_details') }}
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('translations.name') }}
                            </label>
                            <p class="text-gray-900">{{ $productRequest->name }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('translations.email') }}
                            </label>
                            <p class="text-gray-900">{{ $productRequest->email }}</p>
                        </div>
                        
                        @if($productRequest->phone)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('translations.phone') }}
                                </label>
                                <p class="text-gray-900">{{ $productRequest->phone }}</p>
                            </div>
                        @endif
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('translations.quantity') }}
                            </label>
                            <p class="text-gray-900">{{ $productRequest->requested_quantity }}</p>
                        </div>
                    </div>
                    
                    @if($productRequest->message)
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('translations.message') }}
                            </label>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-gray-900 whitespace-pre-wrap">{{ $productRequest->message }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Admin Response -->
                @if($productRequest->admin_notes)
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">
                            {{ __('translations.admin_response') }}
                        </h2>
                        
                        <div class="bg-blue-50 rounded-lg p-4">
                            <p class="text-gray-900 whitespace-pre-wrap">{{ $productRequest->admin_notes }}</p>
                        </div>
                        
                        @if($productRequest->responded_at)
                            <p class="text-sm text-gray-500 mt-2">
                                {{ __('translations.responded_on') }}: {{ $productRequest->responded_at->format('Y-m-d H:i') }}
                                @if($productRequest->respondedBy)
                                    {{ __('translations.by') }} {{ $productRequest->respondedBy->name }}
                                @endif
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Status Card -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        {{ __('translations.status') }}
                    </h3>
                    
                    <div class="text-center">
                        <span class="inline-flex px-4 py-2 text-sm font-semibold rounded-full
                            @if($productRequest->status === 'pending') bg-yellow-100 text-yellow-800
                            @elseif($productRequest->status === 'in_progress') bg-blue-100 text-blue-800
                            @elseif($productRequest->status === 'completed') bg-green-100 text-green-800
                            @elseif($productRequest->status === 'cancelled') bg-red-100 text-red-800
                            @endif">
                            {{ $productRequest->status_label }}
                        </span>
                    </div>
                </div>

                <!-- Request Information -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        {{ __('translations.request_information') }}
                    </h3>
                    
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                {{ __('translations.submitted_on') }}
                            </label>
                            <p class="text-gray-900">{{ $productRequest->created_at->format('Y-m-d H:i') }}</p>
                        </div>
                        
                        @if($productRequest->responded_at)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    {{ __('translations.responded_on') }}
                                </label>
                                <p class="text-gray-900">{{ $productRequest->responded_at->format('Y-m-d H:i') }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        {{ __('translations.actions') }}
                    </h3>
                    
                    <div class="space-y-3">
                        <a href="{{ route('products.show', $productRequest->product) }}" 
                           class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                            {{ __('translations.view_product') }}
                        </a>
                        
                        @if($productRequest->isPending() || $productRequest->isInProgress())
                            <form action="{{ route('product-requests.cancel', $productRequest) }}" 
                                  method="POST"
                                  onsubmit="return confirm('{{ __('translations.confirm_cancel_request') }}')">
                                @csrf
                                @method('PATCH')
                                <button type="submit" 
                                        class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                                    {{ __('translations.cancel_request') }}
                                </button>
                            </form>
                        @endif
                        
                        <a href="{{ route('product-requests.index') }}" 
                           class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors">
                            {{ __('translations.back_to_requests') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

