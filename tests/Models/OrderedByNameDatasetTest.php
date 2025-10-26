<?php

declare(strict_types=1);

use App\Models\Concerns\OrdersByName;
use Tests\TestCase;

uses(TestCase::class);

/**
 * @param class-string $className
 * @param list<string> $columnCandidates
 */
it('models in the OrderedByName dataset expose consistent configuration', function (string $className, array $columnCandidates): void {
    $usedTraits = class_uses_recursive($className);

    expect($usedTraits)
        ->toBeArray()
        ->and(in_array(OrdersByName::class, $usedTraits, true))
        ->toBeTrue();

    /** @var object $model */
    $model = new $className();

    if (! method_exists($className, 'getNameColumn')) {
        throw new \RuntimeException(sprintf('Model %s must expose a getNameColumn method.', $className));
    }

    $reflection = new \ReflectionMethod($className, 'getNameColumn');
    $reflection->setAccessible(true);

    /** @var string $resolvedColumn */
    $resolvedColumn = $reflection->invoke($model);

    expect($columnCandidates)->toBeArray();
    expect(count($columnCandidates))->toBeGreaterThan(0);
    expect($columnCandidates)->toContain($resolvedColumn);
})->with('ordered_by_name_models');
