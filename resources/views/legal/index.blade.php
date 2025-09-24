@extends('components.layouts.base')

@section('title', __('frontend.legal.legal_documents'))

@section('meta')
    <meta name="description" content="{{ __('frontend.legal.meta_description') }}">
    <meta name="keywords" content="{{ __('frontend.legal.meta_keywords') }}">
@endsection

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">
                    {{ __('frontend.legal.legal_documents') }}
                </h1>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    {{ __('frontend.legal.meta_description') }}
                </p>
            </div>

            <!-- Search -->
            <div class="mb-8">
                <form action="{{ route('legal.search') }}" method="GET" class="max-w-2xl mx-auto">
                    <div class="flex gap-4">
                        <input
                               type="text"
                               name="q"
                               value="{{ request('q') }}"
                               placeholder="{{ __('frontend.legal.search_documents') }}"
                               class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <button
                                type="submit"
                                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            {{ __('frontend.legal.search') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Documents by Type -->
            @if ($groupedDocuments->isNotEmpty())
                <div class="space-y-12">
                    @foreach ($groupedDocuments as $type => $documents)
                        <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <div class="mb-6">
                                <h2 class="text-2xl font-semibold text-gray-900 mb-2">
                                    {{ \App\Models\Legal::getTypes()[$type] ?? $type }}
                                </h2>
                                <p class="text-gray-600">
                                    {{ __('frontend.legal.' . $type . '_description') }}
                                </p>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                                @foreach ($documents as $document)
                                    @php
                                        $translation = $document->translations->first();
                                    @endphp

                                    @if ($translation)
                                        <div
                                             class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                            <div class="flex items-start justify-between mb-3">
                                                <h3 class="text-lg font-medium text-gray-900 line-clamp-2">
                                                    {{ $translation->title }}
                                                </h3>
                                                @if ($document->is_required)
                                                    <span
                                                          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        {{ __('frontend.legal.required') }}
                                                    </span>
                                                @endif
                                            </div>

                                            @if ($translation->content)
                                                <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                                                    {{ Str::limit(strip_tags($translation->content), 120) }}
                                                </p>
                                            @endif

                                            <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                                <span>
                                                    {{ __('frontend.legal.last_updated') }}:
                                                    {{ $document->updated_at->format('Y-m-d') }}
                                                </span>
                                                @if ($translation->getReadingTime() > 0)
                                                    <span>
                                                        {{ $translation->getReadingTime() }}
                                                        {{ __('frontend.legal.minutes') }}
                                                    </span>
                                                @endif
                                            </div>

                                            <a
                                               href="{{ route('legal.show', $document->key) }}"
                                               class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium">
                                                {{ __('frontend.legal.read_more') }}
                                                <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </a>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <div class="mx-auto h-12 w-12 text-gray-400">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('frontend.legal.no_documents') }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ __('frontend.legal.no_documents_description') }}</p>
                </div>
            @endif

            <!-- Contact Section -->
            <div class="mt-16 bg-blue-50 rounded-lg p-8 text-center">
                <h3 class="text-xl font-semibold text-gray-900 mb-2">
                    {{ __('frontend.legal.questions_about_legal') }}
                </h3>
                <p class="text-gray-600 mb-4">
                    {{ __('frontend.legal.contact_us') }}
                </p>
                @if (\Illuminate\Support\Facades\Route::has('contact'))
                    <a
                       href="{{ route('contact') }}"
                       class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        {{ __('frontend.legal.contact_us') }}
                    </a>
                @else
                    <a
                       href="#"
                       class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        {{ __('frontend.legal.contact_us') }}
                    </a>
                @endif
            </div>
        </div>
    </div>
@endsection
