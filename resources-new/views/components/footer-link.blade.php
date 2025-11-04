<x-link
    {{ $attributes->merge(['class' => 'text-sm text-slate-600 hover:text-blue-600 group group-link-underline transition-colors duration-200 font-medium']) }}
>
    <span class="link link-underline link-underline-blue">
        {{ $slot }}
    </span>
</x-link>
