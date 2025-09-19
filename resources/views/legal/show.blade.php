@extends('components.layouts.base')

@section('title', $translation->seo_title ?: $translation->title)

@section('meta')
    <meta name="description" content="{{ $translation->seo_description ?: Str::limit(strip_tags($translation->content), 160) }}">
    <meta name="keywords" content="{{ $document->type }}, {{ __('frontend.legal.meta_keywords') }}">
    
    @if($document->meta_data && isset($document->meta_data['version']))
        <meta name="document-version" content="{{ $document->meta_data['version'] }}">
    @endif
    
    @if($document->published_at)
        <meta name="article:published_time" content="{{ $document->published_at->toISOString() }}">
    @endif
    
    @if($document->updated_at)
        <meta name="article:modified_time" content="{{ $document->updated_at->toISOString() }}">
    @endif
@endsection

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumbs -->
        <nav class="mb-8">
            <ol class="flex items-center space-x-2 text-sm text-gray-500">
                <li>
                    <a href="{{ route('home') }}" class="hover:text-gray-700">
                        {{ __('frontend.legal.home') }}
                    </a>
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 mx-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <a href="{{ route('legal.index') }}" class="hover:text-gray-700">
                        {{ __('frontend.legal.legal') }}
                    </a>
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 mx-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-gray-900">{{ $translation->title }}</span>
                </li>
            </ol>
        </nav>

        <!-- Document Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 mb-8">
            <div class="flex items-start justify-between mb-6">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">
                        {{ $translation->title }}
                    </h1>
                    
                    <div class="flex items-center space-x-4 text-sm text-gray-500 mb-4">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ \App\Models\Legal::getTypes()[$document->type] ?? $document->type }}
                        </span>
                        
                        @if($document->is_required)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                {{ __('frontend.legal.required') }}
                            </span>
                        @endif
                        
                        <span>{{ __('frontend.legal.last_updated') }}: {{ $document->updated_at->format('Y-m-d') }}</span>
                        
                        @if($translation->getReadingTime() > 0)
                            <span>{{ $translation->getReadingTime() }} {{ __('frontend.legal.minutes') }}</span>
                        @endif
                    </div>

                    @if($document->meta_data)
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            @if(isset($document->meta_data['version']))
                                <div>
                                    <span class="font-medium text-gray-700">{{ __('frontend.legal.version') }}:</span>
                                    <span class="text-gray-600">{{ $document->meta_data['version'] }}</span>
                                </div>
                            @endif
                            
                            @if(isset($document->meta_data['effective_date']))
                                <div>
                                    <span class="font-medium text-gray-700">{{ __('frontend.legal.effective_date') }}:</span>
                                    <span class="text-gray-600">{{ $document->meta_data['effective_date'] }}</span>
                                </div>
                            @endif
                            
                            @if(isset($document->meta_data['last_reviewed']))
                                <div>
                                    <span class="font-medium text-gray-700">{{ __('frontend.legal.review_date') }}:</span>
                                    <span class="text-gray-600">{{ $document->meta_data['last_reviewed'] }}</span>
                                </div>
                            @endif
                            
                            <div>
                                <span class="font-medium text-gray-700">{{ __('frontend.legal.word_count') }}:</span>
                                <span class="text-gray-600">{{ $translation->getWordCount() }}</span>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Actions -->
                <div class="flex flex-col space-y-2 ml-4">
                    <button 
                        onclick="window.print()"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        {{ __('frontend.legal.print_document') }}
                    </button>
                    
                    <a 
                        href="{{ route('legal.download', ['key' => $document->key, 'format' => 'pdf']) }}"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        {{ __('frontend.legal.download_pdf') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Document Content -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 mb-8">
            <div class="prose prose-lg max-w-none">
                {!! $translation->content !!}
            </div>
        </div>

        <!-- Related Documents -->
        @if($relatedDocuments->isNotEmpty() || $otherDocuments->isNotEmpty())
            <div class="grid md:grid-cols-2 gap-8">
                @if($relatedDocuments->isNotEmpty())
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">
                            {{ __('frontend.legal.related_documents') }}
                        </h3>
                        <div class="space-y-3">
                            @foreach($relatedDocuments as $relatedDocument)
                                @php
                                    $relatedTranslation = $relatedDocument->translations->first();
                                @endphp
                                @if($relatedTranslation)
                                    <a 
                                        href="{{ route('legal.show', $relatedDocument->key) }}"
                                        class="block p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                                    >
                                        <h4 class="font-medium text-gray-900 mb-1">
                                            {{ $relatedTranslation->title }}
                                        </h4>
                                        <p class="text-sm text-gray-600">
                                            {{ Str::limit(strip_tags($relatedTranslation->content), 80) }}
                                        </p>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($otherDocuments->isNotEmpty())
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">
                            {{ __('frontend.legal.other_documents') }}
                        </h3>
                        <div class="space-y-3">
                            @foreach($otherDocuments as $otherDocument)
                                @php
                                    $otherTranslation = $otherDocument->translations->first();
                                @endphp
                                @if($otherTranslation)
                                    <a 
                                        href="{{ route('legal.show', $otherDocument->key) }}"
                                        class="block p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                                    >
                                        <h4 class="font-medium text-gray-900 mb-1">
                                            {{ $otherTranslation->title }}
                                        </h4>
                                        <p class="text-sm text-gray-600">
                                            {{ \App\Models\Legal::getTypes()[$otherDocument->type] ?? $otherDocument->type }}
                                        </p>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Back to Top -->
        <div class="text-center mt-8">
            <button 
                onclick="window.scrollTo({top: 0, behavior: 'smooth'})"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                </svg>
                {{ __('frontend.legal.back_to_top') }}
            </button>
        </div>
    </div>
</div>

@endsection
