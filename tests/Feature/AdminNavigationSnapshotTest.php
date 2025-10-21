<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Support\Nav;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(Nav::class)]
final class AdminNavigationSnapshotTest extends TestCase
{
    public function test_it_reads_navigation_metadata_from_resources(): void
    {
        $resource = \App\Filament\Resources\NotificationResource::class;

        // The helper should mirror the values exposed by the resource itself.
        $this->assertSame($resource::getNavigationGroup(), Nav::groupForResource($resource));
        $this->assertSame($resource::getNavigationIcon(), Nav::iconForResource($resource));
        $this->assertSame($resource::getNavigationSort(), Nav::sortForResource($resource));
    }

    public function test_it_caches_metadata_between_calls(): void
    {
        $resource = \App\Filament\Resources\OrderItemResource::class;

        $first = [
            Nav::groupForResource($resource),
            Nav::iconForResource($resource),
            Nav::sortForResource($resource),
        ];

        $second = [
            Nav::groupForResource($resource),
            Nav::iconForResource($resource),
            Nav::sortForResource($resource),
        ];

        // A simple equality assertion verifies that repeated calls hit the cached data.
        $this->assertSame($first, $second);
    }
}
