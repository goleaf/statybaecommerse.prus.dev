<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Country;
use App\Models\Region;
use App\Models\Translations\RegionTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RegionTest extends TestCase
{
    use RefreshDatabase;

    public function test_region_can_be_created(): void
    {
        $country = Country::factory()->create();
        
        $region = Region::factory()->create([
            'name' => 'Test Region',
            'country_id' => $country->id,
            'level' => 1,
            'is_enabled' => true,
        ]);

        $this->assertDatabaseHas('regions', [
            'name' => 'Test Region',
            'country_id' => $country->id,
            'level' => 1,
            'is_enabled' => true,
        ]);

        $this->assertEquals('Test Region', $region->name);
        $this->assertEquals($country->id, $region->country_id);
        $this->assertEquals(1, $region->level);
        $this->assertTrue($region->is_enabled);
    }

    public function test_region_translation_methods(): void
    {
        $region = Region::factory()->create(['name' => 'Original Name']);
        
        // Create translation
        RegionTranslation::factory()->create([
            'region_id' => $region->id,
            'locale' => 'en',
            'name' => 'English Name',
            'description' => 'English Description',
        ]);

        RegionTranslation::factory()->create([
            'region_id' => $region->id,
            'locale' => 'lt',
            'name' => 'Lietuviškas Pavadinimas',
            'description' => 'Lietuviškas Aprašymas',
        ]);

        // Test translation methods
        $this->assertEquals('English Name', $region->getTranslatedName('en'));
        $this->assertEquals('Lietuviškas Pavadinimas', $region->getTranslatedName('lt'));
        $this->assertEquals('Original Name', $region->getTranslatedName('fr')); // fallback to original

        $this->assertEquals('English Description', $region->getTranslatedDescription('en'));
        $this->assertEquals('Lietuviškas Aprašymas', $region->getTranslatedDescription('lt'));
        $this->assertEquals('', $region->getTranslatedDescription('fr')); // fallback to original (empty)

        // Test available locales
        $locales = $region->getAvailableLocales();
        $this->assertContains('en', $locales);
        $this->assertContains('lt', $locales);

        // Test has translation for locale
        $this->assertTrue($region->hasTranslationFor('en'));
        $this->assertTrue($region->hasTranslationFor('lt'));
        $this->assertFalse($region->hasTranslationFor('fr'));
    }

    public function test_region_scopes(): void
    {
        Region::factory()->create(['is_enabled' => true, 'is_default' => false, 'level' => 0]);
        Region::factory()->create(['is_enabled' => false, 'is_default' => true, 'level' => 1]);
        Region::factory()->create(['is_enabled' => false, 'is_default' => false, 'level' => 2]);

        $this->assertCount(1, Region::enabled()->get());
        $this->assertCount(1, Region::default()->get());
        $this->assertCount(1, Region::byLevel(0)->get());
        $this->assertCount(1, Region::byLevel(1)->get());
        $this->assertCount(1, Region::byLevel(2)->get());
    }

    public function test_region_hierarchy_methods(): void
    {
        $country = Country::factory()->create();
        
        $rootRegion = Region::factory()->create([
            'name' => 'Root Region',
            'country_id' => $country->id,
            'parent_id' => null,
            'level' => 0,
        ]);

        $childRegion = Region::factory()->create([
            'name' => 'Child Region',
            'country_id' => $country->id,
            'parent_id' => $rootRegion->id,
            'level' => 1,
        ]);

        $grandchildRegion = Region::factory()->create([
            'name' => 'Grandchild Region',
            'country_id' => $country->id,
            'parent_id' => $childRegion->id,
            'level' => 2,
        ]);

        // Test parent relationship
        $this->assertEquals($rootRegion->id, $childRegion->parent->id);
        $this->assertEquals($childRegion->id, $grandchildRegion->parent->id);

        // Test children relationship
        $this->assertCount(1, $rootRegion->children);
        $this->assertEquals($childRegion->id, $rootRegion->children->first()->id);

        // Test hierarchy methods
        $this->assertTrue($rootRegion->is_root);
        $this->assertFalse($childRegion->is_root);
        $this->assertFalse($grandchildRegion->is_root);

        $this->assertFalse($rootRegion->is_leaf);
        $this->assertFalse($childRegion->is_leaf);
        $this->assertTrue($grandchildRegion->is_leaf);

        $this->assertEquals(0, $rootRegion->depth);
        $this->assertEquals(1, $childRegion->depth);
        $this->assertEquals(2, $grandchildRegion->depth);

        // Test breadcrumb
        $breadcrumb = $grandchildRegion->breadcrumb;
        $this->assertCount(3, $breadcrumb);
        $this->assertEquals($rootRegion->id, $breadcrumb[0]->id);
        $this->assertEquals($childRegion->id, $breadcrumb[1]->id);
        $this->assertEquals($grandchildRegion->id, $breadcrumb[2]->id);
    }

    public function test_region_helper_methods(): void
    {
        $country = Country::factory()->create(['name' => 'Test Country']);
        $region = Region::factory()->create([
            'name' => 'Test Region',
            'country_id' => $country->id,
            'level' => 1,
        ]);

        // Test full display name
        $this->assertEquals('Test Region, Test Country', $region->getFullDisplayName());

        // Test hierarchy info
        $hierarchyInfo = $region->getHierarchyInfo();
        $this->assertEquals(1, $hierarchyInfo['level']);
        $this->assertEquals('State/Province', $hierarchyInfo['level_name']);
        $this->assertEquals(0, $hierarchyInfo['depth']);
        $this->assertTrue($hierarchyInfo['is_root']);
        $this->assertFalse($hierarchyInfo['has_parent']);

        // Test level name
        $this->assertEquals('Root', Region::factory()->create(['level' => 0])->getLevelName());
        $this->assertEquals('State/Province', Region::factory()->create(['level' => 1])->getLevelName());
        $this->assertEquals('County', Region::factory()->create(['level' => 2])->getLevelName());
        $this->assertEquals('District', Region::factory()->create(['level' => 3])->getLevelName());
        $this->assertEquals('Municipality', Region::factory()->create(['level' => 4])->getLevelName());
        $this->assertEquals('Village', Region::factory()->create(['level' => 5])->getLevelName());
        $this->assertEquals('Level 10', Region::factory()->create(['level' => 10])->getLevelName());
    }

    public function test_region_translation_management(): void
    {
        $region = Region::factory()->create(['name' => 'Original Name']);

        // Test get or create translation
        $translation = $region->getOrCreateTranslation('en');
        $this->assertEquals('en', $translation->locale);
        $this->assertEquals('Original Name', $translation->name);

        // Test update translation
        $this->assertTrue($region->updateTranslation('en', [
            'name' => 'Updated English Name',
            'description' => 'Updated Description',
        ]));

        $translation->refresh();
        $this->assertEquals('Updated English Name', $translation->name);
        $this->assertEquals('Updated Description', $translation->description);

        // Test bulk update translations
        $this->assertTrue($region->updateTranslations([
            'lt' => [
                'name' => 'Lietuviškas Pavadinimas',
                'description' => 'Lietuviškas Aprašymas',
            ],
            'de' => [
                'name' => 'Deutscher Name',
                'description' => 'Deutsche Beschreibung',
            ],
        ]));

        $this->assertTrue($region->hasTranslationFor('lt'));
        $this->assertTrue($region->hasTranslationFor('de'));
        $this->assertEquals('Lietuviškas Pavadinimas', $region->getTranslatedName('lt'));
        $this->assertEquals('Deutscher Name', $region->getTranslatedName('de'));
    }

    public function test_region_relations(): void
    {
        $country = Country::factory()->create();
        $parentRegion = Region::factory()->create(['country_id' => $country->id]);
        $region = Region::factory()->create([
            'country_id' => $country->id,
            'parent_id' => $parentRegion->id,
        ]);

        // Test country relation
        $this->assertInstanceOf(Country::class, $region->country);
        $this->assertEquals($country->id, $region->country->id);

        // Test parent relation
        $this->assertInstanceOf(Region::class, $region->parent);
        $this->assertEquals($parentRegion->id, $region->parent->id);

        // Test children relation
        $this->assertCount(1, $parentRegion->children);
        $this->assertEquals($region->id, $parentRegion->children->first()->id);
    }

    public function test_region_search_scope(): void
    {
        Region::factory()->create([
            'name' => 'Lithuania',
            'code' => 'LT',
            'description' => 'A country in Europe',
        ]);

        Region::factory()->create([
            'name' => 'Germany',
            'code' => 'DE',
            'description' => 'A country in Europe',
        ]);

        Region::factory()->create([
            'name' => 'France',
            'code' => 'FR',
            'description' => 'A country in Europe',
        ]);

        // Test search by name
        $results = Region::search('Lithuania')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('Lithuania', $results->first()->name);

        // Test search by code
        $results = Region::search('DE')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('Germany', $results->first()->name);

        // Test search by description
        $results = Region::search('Europe')->get();
        $this->assertCount(3, $results);
    }
}