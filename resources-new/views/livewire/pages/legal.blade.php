<div>
    <x-container class="py-8">
        @if($translation)
            <div class="max-w-4xl mx-auto">
                <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ $translation->title }}</h1>
                
                <div class="prose prose-lg max-w-none">
                    {!! $translation->content !!}
                </div>
                
                <div class="mt-8 pt-8 border-t border-gray-200">
                    <p class="text-sm text-gray-500">
                        {{ __('Last updated') }}: {{ $legal->updated_at->format('F j, Y') }}
                    </p>
                </div>
            </div>
        @else
            <div class="text-center py-12">
                <div class="text-gray-400 text-6xl mb-4">📄</div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Legal page') }}</h3>
                <p class="text-gray-500">{{ __('This legal page is not available yet.') }}</p>
            </div>
        @endif
    </x-container>
</div>