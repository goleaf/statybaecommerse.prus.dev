<?php
use function Livewire\Volt\{layout, title};

layout('components.layouts.base');
title(__('users.reviews'));

?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumbs -->
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('home') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                        </svg>
                        {{ __('nav.home') }}
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <a href="{{ route('users.dashboard') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">{{ __('users.dashboard') }}</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ __('users.reviews') }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('users.my_reviews') }}</h1>
                    <p class="mt-2 text-gray-600">{{ __('users.reviews_description') }}</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <button 
                        type="button"
                        onclick="openReviewModal()"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        {{ __('users.write_review') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
            <form method="GET" action="{{ route('users.reviews') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Rating Filter -->
                <div>
                    <label for="rating" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.filter_by_rating') }}</label>
                    <select 
                        id="rating" 
                        name="rating"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">{{ __('users.all_ratings') }}</option>
                        <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>{{ __('users.five_stars') }}</option>
                        <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>{{ __('users.four_stars') }}</option>
                        <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>{{ __('users.three_stars') }}</option>
                        <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>{{ __('users.two_stars') }}</option>
                        <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>{{ __('users.one_star') }}</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.filter_by_status') }}</label>
                    <select 
                        id="status" 
                        name="status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">{{ __('users.all_statuses') }}</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>{{ __('users.published') }}</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('users.pending') }}</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>{{ __('users.rejected') }}</option>
                    </select>
                </div>

                <!-- Date From -->
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.date_from') }}</label>
                    <input 
                        type="date" 
                        id="date_from" 
                        name="date_from" 
                        value="{{ request('date_from') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>

                <!-- Filter Button -->
                <div class="flex items-end">
                    <button 
                        type="submit"
                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        {{ __('users.filter') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Reviews List -->
        @if($reviews->count() > 0)
            <div class="space-y-6">
                @foreach($reviews as $review)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <!-- Review Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center space-x-4">
                                <!-- Product Image -->
                                @if($review->product && $review->product->featured_image)
                                    <img 
                                        src="{{ Storage::disk('public')->url($review->product->featured_image) }}" 
                                        alt="{{ $review->product->name }}"
                                        class="h-16 w-16 rounded-md object-cover"
                                    >
                                @else
                                    <div class="h-16 w-16 rounded-md bg-gray-200 flex items-center justify-center">
                                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                                
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">
                                        <a href="{{ $review->product ? route('products.show', $review->product) : '#' }}" class="hover:text-blue-600">
                                            {{ $review->product ? $review->product->name : $review->product_name }}
                                        </a>
                                    </h3>
                                    <div class="flex items-center space-x-2 mt-1">
                                        <!-- Rating Stars -->
                                        <div class="flex items-center">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $review->rating)
                                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                    </svg>
                                                @else
                                                    <svg class="h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                    </svg>
                                                @endif
                                            @endfor
                                        </div>
                                        
                                        <!-- Status Badge -->
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $review->status_color }}">
                                            {{ $review->status_text }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-right">
                                <p class="text-sm text-gray-500">{{ $review->created_at->format('Y-m-d') }}</p>
                                @if($review->helpful_count > 0)
                                    <p class="text-sm text-gray-600 mt-1">
                                        {{ $review->helpful_count }} {{ __('users.people_found_helpful') }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <!-- Review Content -->
                        <div class="mb-4">
                            @if($review->title)
                                <h4 class="text-md font-medium text-gray-900 mb-2">{{ $review->title }}</h4>
                            @endif
                            <p class="text-gray-700 whitespace-pre-wrap">{{ $review->content }}</p>
                        </div>

                        <!-- Review Images -->
                        @if($review->images && count($review->images) > 0)
                            <div class="mb-4">
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    @foreach($review->images as $image)
                                        <div class="relative">
                                            <img 
                                                src="{{ Storage::disk('public')->url($image) }}" 
                                                alt="{{ __('users.review_image') }}"
                                                class="h-24 w-full object-cover rounded-md cursor-pointer hover:opacity-75"
                                                onclick="openImageModal('{{ Storage::disk('public')->url($image) }}')"
                                            >
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Review Actions -->
                        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                            <div class="flex items-center space-x-4">
                                @if($review->status === 'published')
                                    <button 
                                        type="button"
                                        onclick="toggleHelpful({{ $review->id }})"
                                        class="inline-flex items-center text-sm text-gray-600 hover:text-blue-600"
                                    >
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path>
                                        </svg>
                                        {{ __('users.helpful') }} ({{ $review->helpful_count }})
                                    </button>
                                @endif
                            </div>
                            
                            <div class="flex items-center space-x-3">
                                @if($review->can_be_edited)
                                    <button 
                                        type="button"
                                        onclick="editReview({{ $review->id }})"
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    >
                                        {{ __('users.edit') }}
                                    </button>
                                @endif
                                
                                @if($review->can_be_deleted)
                                    <form method="POST" action="{{ route('users.reviews.destroy', $review) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="submit"
                                            onclick="return confirm('{{ __("users.confirm_delete_review") }}')"
                                            class="inline-flex items-center px-3 py-2 border border-red-300 text-sm leading-4 font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                        >
                                            {{ __('users.delete') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($reviews->hasPages())
                <div class="mt-8">
                    {{ $reviews->links() }}
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('users.no_reviews') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('users.no_reviews_description') }}</p>
                <div class="mt-6">
                    <button 
                        type="button"
                        onclick="openReviewModal()"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                    >
                        {{ __('users.write_first_review') }}
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Review Modal -->
<div id="review-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900" id="modal-title">{{ __('users.write_review') }}</h3>
                <button 
                    type="button"
                    onclick="closeReviewModal()"
                    class="text-gray-400 hover:text-gray-600"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <form id="review-form" method="POST" action="{{ route('users.reviews.store') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
                @csrf
                <input type="hidden" id="review-id" name="review_id">
                <input type="hidden" id="form-method" name="_method" value="POST">

                <!-- Product Selection -->
                <div id="product-selection">
                    <label for="product_id" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.select_product') }}</label>
                    <select 
                        id="product_id" 
                        name="product_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        required
                    >
                        <option value="">{{ __('users.choose_product') }}</option>
                        @foreach($user->orders->flatMap->items as $orderItem)
                            @if($orderItem->productVariant && $orderItem->productVariant->product)
                                <option value="{{ $orderItem->productVariant->product->id }}">
                                    {{ $orderItem->productVariant->product->name }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <!-- Rating -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.rating') }}</label>
                    <div class="flex items-center space-x-1" id="rating-stars">
                        @for($i = 1; $i <= 5; $i++)
                            <button 
                                type="button"
                                onclick="setRating({{ $i }})"
                                class="rating-star h-8 w-8 text-gray-300 hover:text-yellow-400 transition-colors"
                                data-rating="{{ $i }}"
                            >
                                <svg class="h-full w-full" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                            </button>
                        @endfor
                    </div>
                    <input type="hidden" id="rating" name="rating" required>
                    @error('rating')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.review_title') }}</label>
                    <input 
                        type="text" 
                        id="title" 
                        name="title"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        placeholder="{{ __('users.review_title_placeholder') }}"
                    >
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Content -->
                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.review_content') }}</label>
                    <textarea 
                        id="content" 
                        name="content"
                        rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        placeholder="{{ __('users.review_content_placeholder') }}"
                        required
                    ></textarea>
                    @error('content')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Images -->
                <div>
                    <label for="images" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.review_images') }} ({{ __('users.optional') }})</label>
                    <input 
                        type="file" 
                        id="images" 
                        name="images[]"
                        multiple
                        accept="image/*"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                    <p class="mt-1 text-sm text-gray-500">{{ __('users.review_images_help') }}</p>
                    @error('images')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Modal Footer -->
                <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                    <button 
                        type="button"
                        onclick="closeReviewModal()"
                        class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        {{ __('users.cancel') }}
                    </button>
                    <button 
                        type="submit"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        {{ __('users.submit_review') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div id="image-modal" class="fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">{{ __('users.review_image') }}</h3>
                <button 
                    type="button"
                    onclick="closeImageModal()"
                    class="text-gray-400 hover:text-gray-600"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="mt-4">
                <img id="modal-image" src="" alt="{{ __('users.review_image') }}" class="w-full h-auto rounded-md">
            </div>
        </div>
    </div>
</div>

<script>
// Review Modal Functions
function openReviewModal(reviewId = null) {
    const modal = document.getElementById('review-modal');
    const form = document.getElementById('review-form');
    const title = document.getElementById('modal-title');
    const reviewIdInput = document.getElementById('review-id');
    const methodInput = document.getElementById('form-method');
    const productSelection = document.getElementById('product-selection');
    
    if (reviewId) {
        // Edit mode
        title.textContent = '{{ __("users.edit_review") }}';
        form.action = '{{ route("users.reviews.update", ":id") }}'.replace(':id', reviewId);
        methodInput.value = 'PUT';
        reviewIdInput.value = reviewId;
        productSelection.style.display = 'none'; // Hide product selection in edit mode
        
        // Load review data
        loadReviewData(reviewId);
    } else {
        // Create mode
        title.textContent = '{{ __("users.write_review") }}';
        form.action = '{{ route("users.reviews.store") }}';
        methodInput.value = 'POST';
        reviewIdInput.value = '';
        productSelection.style.display = 'block';
        form.reset();
        resetRating();
    }
    
    modal.classList.remove('hidden');
}

function closeReviewModal() {
    const modal = document.getElementById('review-modal');
    modal.classList.add('hidden');
}

function editReview(reviewId) {
    openReviewModal(reviewId);
}

function loadReviewData(reviewId) {
    // This would typically fetch data via AJAX
    // For now, we'll use the review data from the page
    const reviews = @json($reviews);
    const review = reviews.find(r => r.id === reviewId);
    
    if (review) {
        document.getElementById('title').value = review.title || '';
        document.getElementById('content').value = review.content;
        setRating(review.rating);
    }
}

// Rating Functions
function setRating(rating) {
    document.getElementById('rating').value = rating;
    
    const stars = document.querySelectorAll('.rating-star');
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.remove('text-gray-300');
            star.classList.add('text-yellow-400');
        } else {
            star.classList.remove('text-yellow-400');
            star.classList.add('text-gray-300');
        }
    });
}

function resetRating() {
    document.getElementById('rating').value = '';
    const stars = document.querySelectorAll('.rating-star');
    stars.forEach(star => {
        star.classList.remove('text-yellow-400');
        star.classList.add('text-gray-300');
    });
}

// Image Modal Functions
function openImageModal(imageSrc) {
    const modal = document.getElementById('image-modal');
    const image = document.getElementById('modal-image');
    image.src = imageSrc;
    modal.classList.remove('hidden');
}

function closeImageModal() {
    const modal = document.getElementById('image-modal');
    modal.classList.add('hidden');
}

// Review Actions
function toggleHelpful(reviewId) {
    fetch(`{{ route('users.reviews.helpful', '') }}/${reviewId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the helpful count
            const helpfulButton = document.querySelector(`[onclick="toggleHelpful(${reviewId})"]`);
            if (helpfulButton) {
                helpfulButton.innerHTML = `{{ __('users.helpful') }} (${data.count})`;
            }
            showNotification(data.message, 'success');
        } else {
            showNotification(data.message || '{{ __("users.error_toggling_helpful") }}', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('{{ __("users.error_toggling_helpful") }}', 'error');
    });
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden ${
        type === 'success' ? 'border-l-4 border-green-400' :
        type === 'error' ? 'border-l-4 border-red-400' :
        type === 'warning' ? 'border-l-4 border-yellow-400' :
        'border-l-4 border-blue-400'
    }`;
    
    notification.innerHTML = `
        <div class="p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    ${type === 'success' ? 
                        '<svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>' :
                        type === 'error' ?
                        '<svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>' :
                        type === 'warning' ?
                        '<svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>' :
                        '<svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>'
                    }
                </div>
                <div class="ml-3 w-0 flex-1 pt-0.5">
                    <p class="text-sm font-medium text-gray-900">${message}</p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" onclick="this.parentElement.parentElement.parentElement.remove()">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Close modals when clicking outside
document.getElementById('review-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeReviewModal();
    }
});

document.getElementById('image-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeImageModal();
    }
});
</script>
