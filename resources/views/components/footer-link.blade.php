<x-link
    {{ $attributes->merge(['class' => 'text-sm text-ash hover:text-sage group group-link-underline transition-colors duration-200 font-medium']) }}
>
    <span class="link link-underline link-underline-sage">
        {{ $slot }}
    </span>
</x-link>
