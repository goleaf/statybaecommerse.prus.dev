@extends('layouts.app')

@section('title', __('translations.my_product_requests'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                {{ __('translations.my_product_requests') }}
            </h1>
            <p class="text-gray-600">
                {{ __('translations.product_requests_description') }}
            </p>
        </div>

        @if($productRequests->count() > 0)
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('translations.product') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('translations.quantity') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('translations.status') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('translations.date') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('translations.actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($productRequests as $request)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if($request->product->getMainImage())
                                                <img src="{{ $request->product->getMainImage() }}" 
                                                     alt="{{ $request->product->name }}" 
                                                     class="w-10 h-10 object-cover rounded-lg mr-3">
                                            @endif
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $request->product->name }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $request->product->sku }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $request->requested_quantity }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                            @if($request->status === 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($request->status === 'in_progress') bg-blue-100 text-blue-800
                                            @elseif($request->status === 'completed') bg-green-100 text-green-800
                                            @elseif($request->status === 'cancelled') bg-red-100 text-red-800
                                            @endif">
                                            {{ $request->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $request->created_at->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('product-requests.show', $request) }}" 
                                               class="text-blue-600 hover:text-blue-900 transition-colors">
                                                {{ __('translations.view') }}
                                            </a>
                                            
                                            @if($request->isPending() || $request->isInProgress())
                                                <form action="{{ route('product-requests.cancel', $request) }}" 
                                                      method="POST" 
                                                      class="inline"
                                                      onsubmit="return confirm('{{ __('translations.confirm_cancel_request') }}')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" 
                                                            class="text-red-600 hover:text-red-900 transition-colors">
                                                        {{ __('translations.cancel') }}
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $productRequests->links() }}
                </div>
            </div>
        @else
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <div class="text-gray-400 mb-4">
                    <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                    {{ __('translations.no_requests_found') }}
                </h3>
                <p class="text-gray-500 mb-4">
                    {{ __('translations.no_requests_description') }}
                </p>
                <a href="{{ route('shop.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                    {{ __('translations.browse_products') }}
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

