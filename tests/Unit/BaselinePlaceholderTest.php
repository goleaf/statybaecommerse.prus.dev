<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Offer a deterministic unit-level placeholder so tooling retains a
 * predictable baseline assertion when smoke-checking the suite.
 */
final class BaselinePlaceholderTest extends TestCase
{
    #[Test]
    public function placeholder_runs(): void
    {
        // Keep a trivial assertion to guarantee the unit placeholder stays green.
        $this->assertSame(42, 40 + 2, 'Unit placeholder must remain predictable.');
    }
}
