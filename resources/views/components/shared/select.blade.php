@props([
    'label' => null,
    'placeholder' => null,
    'required' => false,
    'error' => null,
    'helpText' => null,
    'options' => [],
    'size' => 'md', // sm, md, lg
])

@php
$inputId = $attributes->get('id', 'select-' . uniqid());

$baseClasses = 'block w-full border-gray-300 bg-white text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-400 dark:focus:ring-blue-400';

$sizeClasses = match($size) {
    'sm' => 'px-3 py-2 text-sm rounded-md',
    'md' => 'px-4 py-2 text-sm rounded-lg',
    'lg' => 'px-4 py-3 text-base rounded-lg',
    default => 'px-4 py-2 text-sm rounded-lg',
};

$classes = $baseClasses . ' ' . $sizeClasses;

if ($error) {
    $classes .= ' border-red-500 focus:border-red-500 focus:ring-red-500';
}
@endphp

<div class="space-y-2">
    @if($label)
        <label for="{{ $inputId }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    
    <select
        id="{{ $inputId }}"
        @if($required) required @endif
        {{ $attributes->merge(['class' => $classes]) }}
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        
        @if(!empty($options))
            @foreach($options as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        @else
            {{ $slot }}
        @endif
    </select>
    
    @if($error)
        <p class="text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
    @endif
    
    @if($helpText)
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $helpText }}</p>
    @endif
</div>
