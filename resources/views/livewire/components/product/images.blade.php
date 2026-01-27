<div>
    <div class="aspect-square bg-white flex items-center justify-center border rounded-md">
        @if (!empty($images[$active]['src']))
            <img src="{{ $images[$active]['src'] }}" alt="{{ $images[$active]['alt'] ?? '' }}"
                 srcset="{{ $images[$active]['srcset'] ?? '' }}"
                 sizes="(max-width: 1024px) 60vw, 800px"
                 width="800" height="800" loading="eager" fetchpriority="high"
                 class="max-h-[480px] object-contain" />
        @elseif ($thumbnail)
            <img src="{{ $thumbnail }}" alt="" width="800" height="800" loading="eager"
                 fetchpriority="high"
                 class="max-h-[480px] object-contain" />
        @endif
    </div>

    @if (count($images) > 1)
        <div class="mt-4 grid grid-cols-4 gap-3">
            @foreach ($images as $idx => $img)
                <button type="button" wire:click="setActive({{ $idx }})"
                        class="border rounded-md {{ $idx === $active ? 'ring-2 ring-primary-500' : '' }}">
                    <img src="{{ $img['src'] }}" alt="{{ $img['alt'] ?? '' }}" width="150" height="150"
                         loading="lazy"
                         class="aspect-square object-cover" />
                </button>
            @endforeach
        </div>
    @endif
</div>