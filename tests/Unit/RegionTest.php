<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Region;
use App\Models\Country;
use App\Models\Zone;
use App\Models\Translations\RegionTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RegionTest extends TestCase
{
    use RefreshDatabase;

    public function test_region_fillable_attributes(): void
    {
        $region = new Region();
        $fillable = $region->getFillable();

        $expectedFillable = [
            'name', 'slug', 'code', 'description', 'is_enabled', 'is_default',
            'country_id', 'zone_id', 'parent_id', 'level', 'sort_order', 'metadata',
        ];

        $this->assertEquals($expectedFillable, $fillable);
    }

    public function test_region_casts(): void
    {
        $region = new Region();
        $casts = $region->getCasts();

        $this->assertEquals('boolean', $casts['is_enabled']);
        $this->assertEquals('boolean', $casts['is_default']);
        $this->assertEquals('integer', $casts['level']);
        $this->assertEquals('integer', $casts['sort_order']);
        $this->assertEquals('array', $casts['metadata']);
    }

    public function test_region_uses_soft_deletes(): void
    {
        $region = new Region();
        $this->assertTrue(in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($region)));
    }

    public function test_region_uses_has_translations_trait(): void
    {
        $region = new Region();
        $this->assertTrue(in_array('App\Traits\HasTranslations', class_uses($region)));
    }

    public function test_region_translation_model_property(): void
    {
        $region = new Region();
        $this->assertEquals(RegionTranslation::class, $region->translationModel);
    }

    public function test_region_table_name(): void
    {
        $region = new Region();
        $this->assertEquals('regions', $region->getTable());
    }

    public function test_region_country_relationship(): void
    {
        $country = Country::factory()->create();
        $region = Region::factory()->create(['country_id' => $country->id]);

        $this->assertInstanceOf(Country::class, $region->country);
        $this->assertEquals($country->id, $region->country->id);
    }

    public function test_region_zone_relationship(): void
    {
        $zone = Zone::factory()->create();
        $region = Region::factory()->create(['zone_id' => $zone->id]);

        $this->assertInstanceOf(Zone::class, $region->zone);
        $this->assertEquals($zone->id, $region->zone->id);
    }

    public function test_region_parent_relationship(): void
    {
        $parentRegion = Region::factory()->create();
        $childRegion = Region::factory()->create(['parent_id' => $parentRegion->id]);

        $this->assertInstanceOf(Region::class, $childRegion->parent);
        $this->assertEquals($parentRegion->id, $childRegion->parent->id);
    }

    public function test_region_children_relationship(): void
    {
        $parentRegion = Region::factory()->create();
        $childRegion = Region::factory()->create(['parent_id' => $parentRegion->id]);

        $this->assertTrue($parentRegion->children->contains($childRegion));
        $this->assertEquals($childRegion->id, $parentRegion->children->first()->id);
    }

    public function test_region_trans_method(): void
    {
        $region = Region::factory()->create(['name' => 'Original Name']);

        $this->assertEquals('Original Name', $region->trans('name'));

        RegionTranslation::factory()->create([
            'region_id' => $region->id,
            'locale' => 'en',
            'name' => 'Translated Name',
        ]);

        app()->setLocale('en');
        $this->assertEquals('Translated Name', $region->trans('name'));

        app()->setLocale('lt');
        $this->assertEquals('Original Name', $region->trans('name'));
    }

    public function test_region_translated_name_accessor(): void
    {
        $region = Region::factory()->create(['name' => 'Original Name']);

        $this->assertEquals('Original Name', $region->translated_name);

        RegionTranslation::factory()->create([
            'region_id' => $region->id,
            'locale' => 'en',
            'name' => 'Translated Name',
        ]);

        app()->setLocale('en');
        $this->assertEquals('Translated Name', $region->translated_name);
    }

    public function test_region_metadata_casting(): void
    {
        $metadata = ['key1' => 'value1', 'key2' => 'value2'];
        $region = Region::factory()->create(['metadata' => $metadata]);

        $this->assertIsArray($region->metadata);
        $this->assertEquals($metadata, $region->metadata);
    }

    public function test_region_boolean_casting(): void
    {
        $region = Region::factory()->create([
            'is_enabled' => 1,
            'is_default' => 0,
        ]);

        $this->assertTrue($region->is_enabled);
        $this->assertFalse($region->is_default);
    }

    public function test_region_soft_delete(): void
    {
        $region = Region::factory()->create();
        $regionId = $region->id;

        $region->delete();

        $this->assertSoftDeleted('regions', ['id' => $regionId]);
        $this->assertDatabaseHas('regions', ['id' => $regionId]);
    }
}