<x-link
    {{ $attributes->merge(['class' => 'text-sm text-gray-900 group group-link-underline']) }}
>
    <span class="link link-underline link-underline-black">
        {{ $slot }}
    </span>
</x-link>
