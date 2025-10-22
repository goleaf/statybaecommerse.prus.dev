<?php

declare(strict_types=1);

namespace App\Livewire\Hooks;

use Filament\Resources\Pages\Page;
use Livewire\Mechanisms\ComponentRegistry;
use ReflectionClass;
use ReflectionProperty;
use Throwable;
use Traversable;

/**
 * AssignFilamentResourceHook dynamically wires Filament page components to the
 * resource class supplied by Livewire test parameters so generic component
 * mounting (e.g. Livewire::test(ListRecords::class, ['resource' => ...]))
 * continues to function during automated testing.
 */
final class AssignFilamentResourceHook
{
    /**
     * Inject the provided resource class into the underlying Filament page when
     * the component is mounted by Livewire without a specialised subclass.
     *
     * @param array<string, mixed>|mixed $parameters
     */
    public function assignFromEvent(string|object $component, mixed $parameters): void
    {
        $class = $this->resolveComponentClass($component);

        if (! is_string($class) || $class === '' || ! is_subclass_of($class, Page::class)) {
            return;
        }

        if ($parameters instanceof Traversable) {
            $parameters = iterator_to_array($parameters);
        } elseif (! is_array($parameters)) {
            // Normalise scalar or object params into an array for consistent access.
            $parameters = (array) $parameters;
        }

        $resource = $parameters['resource'] ?? null;

        if (! is_string($resource) || $resource === '' || ! class_exists($resource)) {
            return;
        }

        $reflection = new ReflectionClass($class);

        // Bubble up the inheritance chain until we locate the static $resource property.
        while ($reflection instanceof ReflectionClass) {
            if (! $reflection->hasProperty('resource')) {
                $reflection = $reflection->getParentClass();

                continue;
            }

            $property = $reflection->getProperty('resource');

            if (! $property->isStatic()) {
                return;
            }

            $this->applyResourceValue($property, $resource);

            return;
        }
    }

    /**
     * Resolve the fully-qualified component class from either a Livewire name or instance.
     */
    private function resolveComponentClass(string|object $component): ?string
    {
        if (is_object($component)) {
            return $component::class;
        }

        if (! is_string($component) || $component === '') {
            return null;
        }

        /** @var ComponentRegistry|mixed $registry */
        $registry = app(ComponentRegistry::class);

        if (! $registry instanceof ComponentRegistry) {
            return class_exists($component) ? $component : null;
        }

        try {
            return $registry->getClass($component);
        } catch (Throwable) {
            return class_exists($component) ? $component : null;
        }
    }

    /**
     * Safely update the protected static $resource property using reflection.
     */
    private function applyResourceValue(ReflectionProperty $property, string $resource): void
    {
        $property->setAccessible(true);

        // Always set the resource so subsequent tests can override earlier values.
        $property->setValue(null, $resource);
    }
}
