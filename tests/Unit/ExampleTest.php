<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Offer a deterministic unit-level placeholder so tooling that references the
 * ExampleTest legacy entry continues to operate without missing files.
 */
final class ExampleTest extends TestCase
{
    #[Test]
    public function test_example(): void
    {
        // Keep a trivial assertion to guarantee the unit placeholder stays green.
        $this->assertSame(42, 40 + 2, 'Unit example placeholder must remain predictable.');
    }
}
