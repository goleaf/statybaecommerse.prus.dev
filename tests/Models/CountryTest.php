<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CountryTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_orders_countries_by_name(): void
    {
        // We intentionally create records out of order and with mixed casing to validate the scope behaviour.
        Country::factory()->create(['name' => 'Lithuania']);
        Country::factory()->create(['name' => 'Estonia']);
        Country::factory()->create(['name' => 'Latvia']);
        Country::factory()->create(['name' => 'andorra']);

        // The orderedByName scope should normalise casing and return a consistently sorted list of names.
        $orderedNames = Country::query()->orderedByName()->pluck('name')->all();

        $this->assertSame(['andorra', 'Estonia', 'Latvia', 'Lithuania'], $orderedNames);
    }
}
