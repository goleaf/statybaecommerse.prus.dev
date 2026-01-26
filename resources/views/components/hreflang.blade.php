@foreach ($alternateLocales as $loc => $href)
        <link rel="alternate" hreflang="{{ $loc }}" href="{{ $href }}" />
@endforeach
