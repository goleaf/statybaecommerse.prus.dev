<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\City;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class CityTest extends TestCase
{
    use RefreshDatabase;

    public function test_city_belongs_to_country(): void
    {
        // Create a country record so the city can reference it reliably.
        $country = Country::factory()->create();

        // Persist a city linked to the country to confirm the relationship wiring.
        $city = City::factory()->create([
            'country_id' => $country->id,
        ]);

        // Ensure the eager-loaded country relation resolves to the expected model instance.
        $this->assertSame($country->id, $city->country->id);
    }

    public function test_city_can_reference_parent_and_children(): void
    {
        // Create the root city that should act as the parent node.
        $parent = City::factory()->create([
            'is_active'  => true,
            'is_enabled' => true,
        ]);

        // Attach a child city to the parent to exercise both relationship directions.
        $child = City::factory()->create([
            'parent_id'  => $parent->id,
            'is_active'  => true,
            'is_enabled' => true,
        ]);

        // Confirm the child resolves its parent relation and the parent exposes the child collection.
        $this->assertSame($parent->id, $child->parent->id);
        $this->assertTrue($parent->children->contains($child));
    }

    public function test_scope_enabled_filters_disabled_records(): void
    {
        // Seed two cities with differing enabled flags to validate the scope constraint.
        $enabledCity = City::factory()->create([
            'is_enabled' => true,
            'is_active'  => true,
        ]);
        City::factory()->create([
            'is_enabled' => false,
            'is_active'  => true,
        ]);

        // The enabled scope should only include the enabled city.
        $result = City::query()->withoutGlobalScopes()->enabled()->get();
        $this->assertSame([$enabledCity->id], $result->pluck('id')->all());
    }

    public function test_scope_active_filters_inactive_records(): void
    {
        // Create active and inactive cities so the scope has distinct options to filter.
        $activeCity = City::factory()->create([
            'is_active'  => true,
            'is_enabled' => true,
        ]);
        City::factory()->create([
            'is_active'  => false,
            'is_enabled' => true,
        ]);

        // Ensure only the active city remains once the active scope is applied.
        $result = City::query()->withoutGlobalScopes()->active()->get();
        $this->assertSame([$activeCity->id], $result->pluck('id')->all());
    }

    public function test_scope_ordered_by_name_sorts_alphabetically(): void
    {
        // Insert cities with deterministic names so alphabetical sorting can be asserted.
        $alpha = City::factory()->create([
            'name'       => 'Alpha City',
            'is_active'  => true,
            'is_enabled' => true,
        ]);
        $zulu = City::factory()->create([
            'name'       => 'Zulu City',
            'is_active'  => true,
            'is_enabled' => true,
        ]);

        // Fetch using the new scope to verify the alphabetical order by name column.
        $ordered = City::query()
            ->withoutGlobalScopes()
            ->orderedByName()
            ->pluck('id')
            ->all();

        $this->assertSame([$alpha->id, $zulu->id], $ordered);
    }

    public function test_creating_city_generates_slug_and_code_when_missing(): void
    {
        // Prepare a supporting country so the new city can be created without factory defaults.
        $country = Country::factory()->create();

        // Manually create a city without slug and code to exercise the boot event fallback logic.
        $city = City::query()->create([
            'name'       => 'Test Example City',
            'slug'       => null,
            'code'       => null,
            'country_id' => $country->id,
            'is_active'  => true,
            'is_enabled' => true,
        ]);

        // Confirm the slug follows the expected slugified structure and the code is generated.
        $this->assertSame(Str::slug('Test Example City'), $city->slug);
        $this->assertNotEmpty($city->code);
    }

    public function test_attribute_casts_normalize_boolean_flags(): void
    {
        // Create a city with explicit boolean-flaggable values stored as truthy/falsy integers.
        $city = City::factory()->create([
            'is_active'  => false,
            'is_enabled' => true,
            'is_default' => 1,
            'is_capital' => 0,
        ]);

        // Reload the model to ensure casts run against persisted database values.
        $fresh = $city->fresh();

        // Validate each cast returns a strict boolean as advertised by the model configuration.
        $this->assertFalse($fresh->is_active);
        $this->assertTrue($fresh->is_enabled);
        $this->assertTrue($fresh->is_default);
        $this->assertFalse($fresh->is_capital);
    }
}
