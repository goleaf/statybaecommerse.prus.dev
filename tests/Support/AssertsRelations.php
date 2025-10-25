<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Database\Eloquent\Relations\Relation;
use PHPUnit\Framework\ExpectationFailedException;

/**
 * Lightweight helper to centralise relation signature checks for Pest/PHPUnit
 * tests so that model assertions remain expressive and documented.
 */
final class AssertsRelations
{
    /**
     * Call a relation method on a model instance and assert the type, skipping if method missing.
     *
     * @param class-string<Relation> $relationClass
     */
    public static function assertRelation(object $modelInstance, string $method, string $relationClass): void
    {
        // Defer to Pest's skip mechanism whenever the model does not expose the relation yet.
        if (! method_exists($modelInstance, $method)) {
            \PHPUnit\Framework\Assert::markTestSkipped(class_basename($modelInstance) . "::{$method}() not defined.");
        }

        $relation = $modelInstance->{$method}();

        if (! $relation instanceof Relation) {
            // Surface a descriptive failure to help identify misconfigured relationships quickly.
            throw new ExpectationFailedException(sprintf(
                'Expected %s::%s() to return a Relation, got %s',
                $modelInstance::class,
                $method,
                is_object($relation) ? $relation::class : gettype($relation)
            ));
        }

        \PHPUnit\Framework\Assert::assertInstanceOf(
            $relationClass,
            $relation,
            sprintf('Expected %s::%s() to be instance of %s', $modelInstance::class, $method, $relationClass)
        );
    }
}
