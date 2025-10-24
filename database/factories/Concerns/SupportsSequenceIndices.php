<?php

declare(strict_types=1);

namespace Database\Factories\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Factories\Sequence as SequenceContext;
use ReflectionFunction;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;

/**
 * Normalises factory sequence callbacks so tests may type-hint integer indices directly.
 */
trait SupportsSequenceIndices
{
    /**
     * @param  array<int, mixed>  $definitions
     * @return array<int, mixed>
     */
    protected function normaliseSequenceDefinitions(array $definitions): array
    {
        return array_map(
            static function ($definition) {
                if (! $definition instanceof Closure) {
                    return $definition;
                }

                $reflection = new ReflectionFunction($definition);
                $parameterCount = $reflection->getNumberOfParameters();

                $expectsInteger = false;
                $firstParameter = $reflection->getParameters()[0] ?? null;

                if ($firstParameter instanceof ReflectionParameter) {
                    $expectsInteger = self::sequenceParameterExpectsInteger($firstParameter);
                }

                return static function ($context, ...$args) use ($definition, $expectsInteger, $parameterCount) {
                    if ($expectsInteger && $context instanceof SequenceContext) {
                        $context = $context->index;
                    }

                    if ($parameterCount === 0) {
                        return $definition();
                    }

                    $arguments = array_merge([$context], $args);

                    if ($parameterCount < count($arguments)) {
                        $arguments = array_slice($arguments, 0, $parameterCount);
                    }

                    return $definition(...$arguments);
                };
            },
            $definitions
        );
    }

    private static function sequenceParameterExpectsInteger(ReflectionParameter $parameter): bool
    {
        $type = $parameter->getType();

        if ($type === null) {
            return false;
        }

        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $unionType) {
                if (! $unionType instanceof ReflectionNamedType) {
                    continue;
                }

                if (! $unionType->isBuiltin()) {
                    continue;
                }

                if (self::isIntegerTypeName($unionType->getName())) {
                    return true;
                }
            }

            return false;
        }

        if (! $type instanceof ReflectionNamedType) {
            return false;
        }

        return $type->isBuiltin() && self::isIntegerTypeName($type->getName());
    }

    private static function isIntegerTypeName(string $name): bool
    {
        return in_array(strtolower($name), ['int', 'integer'], true);
    }
}
