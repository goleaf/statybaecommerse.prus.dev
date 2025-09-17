<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class MakeSharedComponentCommand extends Command
{
    protected $signature = 'make:shared-component {name} {--type=ui} {--force}';

    protected $description = 'Create a new shared component';

    public function handle(): int
    {
        $name = $this->argument('name');
        $type = $this->option('type');
        $force = $this->option('force');

        $componentName = Str::kebab($name);
        $className = Str::studly($name);

        $viewPath = resource_path("views/components/shared/{$componentName}.blade.php");

        if (File::exists($viewPath) && ! $force) {
            $this->error("Component {$componentName} already exists. Use --force to overwrite.");

            return 1;
        }

        $template = $this->getComponentTemplate($type, $className, $componentName);

        File::ensureDirectoryExists(dirname($viewPath));
        File::put($viewPath, $template);

        $this->info('Shared component created successfully:');
        $this->line("View: {$viewPath}");
        $this->line("Usage: <x-shared.{$componentName} />");

        // Update component registry
        $this->updateComponentRegistry($componentName, $type);

        return 0;
    }

    private function getComponentTemplate(string $type, string $className, string $componentName): string
    {
        $templates = [
            'ui' => $this->getUiComponentTemplate($className, $componentName),
            'form' => $this->getFormComponentTemplate($className, $componentName),
            'layout' => $this->getLayoutComponentTemplate($className, $componentName),
            'ecommerce' => $this->getEcommerceComponentTemplate($className, $componentName),
        ];

        return $templates[$type] ?? $templates['ui'];
    }

    private function getUiComponentTemplate(string $className, string $componentName): string
    {
        return <<<'BLADE'
@props([
    'variant' => 'primary',
    'size' => 'md',
])

@php
$baseClasses = 'inline-flex items-center justify-center font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2';

$variantClasses = match($variant) {
    'primary' => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
    'secondary' => 'bg-gray-200 text-gray-900 hover:bg-gray-300 focus:ring-gray-500',
    default => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
};

$sizeClasses = match($size) {
    'sm' => 'px-3 py-2 text-sm rounded-md',
    'md' => 'px-4 py-2 text-sm rounded-lg',
    'lg' => 'px-6 py-3 text-base rounded-lg',
    default => 'px-4 py-2 text-sm rounded-lg',
};

$classes = $baseClasses . ' ' . $variantClasses . ' ' . $sizeClasses;
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
BLADE;
    }

    private function getFormComponentTemplate(string $className, string $componentName): string
    {
        return <<<'BLADE'
@props([
    'label' => null,
    'required' => false,
    'error' => null,
    'helpText' => null,
])

@php
$inputId = $attributes->get('id', 'input-' . uniqid());
$classes = 'block w-full rounded-lg border-gray-300 bg-white px-4 py-2 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white';

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
    
    <div {{ $attributes->merge(['class' => $classes, 'id' => $inputId]) }}>
        {{ $slot }}
    </div>
    
    @if($error)
        <p class="text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
    @endif
    
    @if($helpText)
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $helpText }}</p>
    @endif
</div>
BLADE;
    }

    private function getLayoutComponentTemplate(string $className, string $componentName): string
    {
        return <<<'BLADE'
@props([
    'title' => null,
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg shadow-md p-6 dark:bg-gray-800']) }}>
    @if($title)
        <div class="mb-4">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $title }}</h2>
            @if($description)
                <p class="mt-1 text-gray-600 dark:text-gray-300">{{ $description }}</p>
            @endif
        </div>
    @endif
    
    {{ $slot }}
</div>
BLADE;
    }

    private function getEcommerceComponentTemplate(string $className, string $componentName): string
    {
        return <<<'BLADE'
@props([
    'product',
    'showActions' => true,
])

<div class="group relative overflow-hidden rounded-xl bg-white shadow-md ring-1 ring-gray-200 transition-all duration-300 hover:shadow-lg hover:ring-gray-300 dark:bg-gray-800 dark:ring-gray-700">
    {{-- Product Image --}}
    <div class="aspect-w-1 aspect-h-1 overflow-hidden">
        @if($product->getFirstMediaUrl('gallery'))
            <img 
                src="{{ $product->getFirstMediaUrl('gallery') }}" 
                alt="{{ $product->name }}"
                class="h-64 w-full object-cover transition-transform duration-300 group-hover:scale-105"
                loading="lazy"
            />
        @else
            <div class="flex h-64 items-center justify-center bg-gray-200 dark:bg-gray-700">
                <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        @endif
    </div>
    
    {{-- Product Info --}}
    <div class="p-4">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
            {{ $product->name }}
        </h3>
        
        @if($showActions)
            <div class="mt-4">
                <x-shared.button 
                    wire:click="addToCart({{ $product->id }})"
                    variant="primary"
                    size="sm"
                >
                    {{ __('shared.add_to_cart') }}
                </x-shared.button>
            </div>
        @endif
    </div>
</div>
BLADE;
    }

    private function updateComponentRegistry(string $componentName, string $type): void
    {
        $registryPath = app_path('Services/Shared/ComponentRegistryService.php');

        if (File::exists($registryPath)) {
            $this->info("Don't forget to update the ComponentRegistryService with the new component!");
            $this->line("Component: shared.{$componentName}");
            $this->line("Type: {$type}");
        }
    }
}
