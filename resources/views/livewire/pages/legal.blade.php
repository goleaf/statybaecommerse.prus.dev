@section('meta')
    <x-meta
            :title="$page->trans('title') ?? $page->title"
            :description="Str::limit(strip_tags($page->trans('content') ?? ($page->content ?? '')), 150)"
            canonical="{{ url()->current() }}" />
@endsection

<div>
    <div class="container mx-auto px-4 py-8" wire:loading.attr="aria-busy" aria-busy="false">
        @php
            $__status = session('status');
            $__error = session('error');
            $__hasErrors = $errors->any();
        @endphp
        @if ($__status)
            <x-alert type="success" class="mb-4">{{ $__status }}</x-alert>
        @endif
        @if ($__error)
            <x-alert type="error" class="mb-4">{{ $__error }}</x-alert>
        @endif
        @if ($__hasErrors)
            <x-alert type="error" class="mb-4">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif
        <h1 class="text-2xl font-semibold mb-6">{{ $page->trans('title') ?? $page->title }}</h1>

        <article class="prose max-w-none">
            {!! $page->trans('content') ?? $page->content !!}
        </article>
    </div>
</div>

@push('scripts')
    @php
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => $page->trans('title') ?? $page->title,
            'description' => Str::limit(strip_tags($page->trans('content') ?? ($page->content ?? '')), 300),
            'url' => url()->current(),
        ];
    @endphp
    <script type="application/ld+json">
        {!! json_encode($schema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush
