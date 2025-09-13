<?php

declare(strict_types=1);

use App\Models\Region;
use App\Models\Country;
use App\Models\Zone;
use App\Models\City;
use App\Models\Address;
use App\Models\Translations\RegionTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->country = Country::factory()->create(['is_active' => true]);
    $this->zone = Zone::factory()->create(['is_active' => true]);
});

it('can create a region', function () {
    $region = Region::factory()->create([
        'name' => 'Test Region',
        'country_id' => $this->country->id,
        'zone_id' => $this->zone->id,
    ]);

    expect($region->name)->toBe('Test Region');
    expect($region->country_id)->toBe($this->country->id);
    expect($region->zone_id)->toBe($this->zone->id);
});

it('belongs to a country', function () {
    $region = Region::factory()->create(['country_id' => $this->country->id]);
    
    expect($region->country)->toBeInstanceOf(Country::class);
    expect($region->country->id)->toBe($this->country->id);
});

it('belongs to a zone', function () {
    $region = Region::factory()->create(['zone_id' => $this->zone->id]);
    
    expect($region->zone)->toBeInstanceOf(Zone::class);
    expect($region->zone->id)->toBe($this->zone->id);
});

it('can have a parent region', function () {
    $parent = Region::factory()->create();
    $child = Region::factory()->create(['parent_id' => $parent->id]);
    
    expect($child->parent)->toBeInstanceOf(Region::class);
    expect($child->parent->id)->toBe($parent->id);
});

it('can have child regions', function () {
    $parent = Region::factory()->create();
    $child1 = Region::factory()->create(['parent_id' => $parent->id]);
    $child2 = Region::factory()->create(['parent_id' => $parent->id]);
    
    expect($parent->children)->toHaveCount(2);
    expect($parent->children->first()->id)->toBe($child1->id);
});

it('has many cities', function () {
    $region = Region::factory()->create();
    City::factory()->count(3)->create(['region_id' => $region->id]);
    
    expect($region->cities)->toHaveCount(3);
});

it('has many addresses', function () {
    $region = Region::factory()->create();
    $user = \App\Models\User::factory()->create();
    Address::factory()->count(2)->create([
        'region_id' => $region->id,
        'user_id' => $user->id,
        'country_id' => $this->country->id,
    ]);
    
    expect($region->addresses)->toHaveCount(2);
});

// it('has many users', function () {
//     $region = Region::factory()->create();
//     User::factory()->count(3)->create(['region_id' => $region->id]);
//     
//     expect($region->users)->toHaveCount(3);
// });

// it('has many orders', function () {
//     $region = Region::factory()->create();
//     Order::factory()->count(2)->create(['region_id' => $region->id]);
//     
//     expect($region->orders)->toHaveCount(2);
// });

// it('has many warehouses', function () {
//     $region = Region::factory()->create();
//     Warehouse::factory()->count(2)->create(['region_id' => $region->id]);
//     
//     expect($region->warehouses)->toHaveCount(2);
// });

// it('has many stores', function () {
//     $region = Region::factory()->create();
//     Store::factory()->count(2)->create(['region_id' => $region->id]);
//     
//     expect($region->stores)->toHaveCount(2);
// });

it('has translations', function () {
    $region = Region::factory()->create(['name' => 'Test Region']);
    
    $region->translations()->create([
        'locale' => 'en',
        'name' => 'Test Region EN',
        'description' => 'Test Region Description EN',
    ]);
    
    $region->translations()->create([
        'locale' => 'lt',
        'name' => 'Test Region LT',
        'description' => 'Test Region Description LT',
    ]);
    
    expect($region->translations)->toHaveCount(2);
});

it('can get translated name', function () {
    $region = Region::factory()->create(['name' => 'Original Name']);
    
    $region->translations()->create([
        'locale' => 'en',
        'name' => 'English Name',
        'description' => 'English Description',
    ]);
    
    expect($region->getTranslatedName('en'))->toBe('English Name');
    expect($region->getTranslatedName('lt'))->toBe('Original Name'); // fallback to original
});

it('can get translated description', function () {
    $region = Region::factory()->create(['description' => 'Original Description']);
    
    $region->translations()->create([
        'locale' => 'en',
        'name' => 'English Name',
        'description' => 'English Description',
    ]);
    
    expect($region->getTranslatedDescription('en'))->toBe('English Description');
    expect($region->getTranslatedDescription('lt'))->toBe('Original Description'); // fallback to original
});

it('can scope with translations', function () {
    $region = Region::factory()->create();
    
    $region->translations()->create([
        'locale' => 'en',
        'name' => 'English Name',
        'description' => 'English Description',
    ]);
    
    $regions = Region::withTranslations('en')->get();
    
    expect($regions)->toHaveCount(1);
    expect($regions->first()->translations)->toHaveCount(1);
});

it('can get available locales', function () {
    $region = Region::factory()->create();
    
    $region->translations()->create(['locale' => 'en', 'name' => 'English Name', 'description' => 'English Description']);
    $region->translations()->create(['locale' => 'lt', 'name' => 'Lithuanian Name', 'description' => 'Lithuanian Description']);
    
    $locales = $region->getAvailableLocales();
    
    expect($locales)->toContain('en', 'lt');
});

it('can check if has translation for locale', function () {
    $region = Region::factory()->create();
    
    $region->translations()->create([
        'locale' => 'en',
        'name' => 'English Name',
        'description' => 'English Description',
    ]);
    
    expect($region->hasTranslationFor('en'))->toBeTrue();
    expect($region->hasTranslationFor('lt'))->toBeFalse();
});

it('can get or create translation', function () {
        $region = Region::factory()->create(['name' => 'Original Name']);

    $translation = $region->getOrCreateTranslation('en');
    
    expect($translation)->toBeInstanceOf(RegionTranslation::class);
    expect($translation->locale)->toBe('en');
    expect($translation->name)->toBe('Original Name');
});

it('can update translation', function () {
    $region = Region::factory()->create();
    
    $region->translations()->create([
            'locale' => 'en',
        'name' => 'Original Name',
        'description' => 'Original Description',
    ]);
    
    $result = $region->updateTranslation('en', [
        'name' => 'Updated Name',
        'description' => 'Updated Description',
    ]);
    
    expect($result)->toBeTrue();
    
    $translation = $region->translations()->where('locale', 'en')->first();
    expect($translation->name)->toBe('Updated Name');
    expect($translation->description)->toBe('Updated Description');
});

it('can bulk update translations', function () {
    $region = Region::factory()->create();
    
    $result = $region->updateTranslations([
        'en' => ['name' => 'English Name', 'description' => 'English Description'],
        'lt' => ['name' => 'Lithuanian Name', 'description' => 'Lithuanian Description'],
    ]);
    
    expect($result)->toBeTrue();
    expect($region->translations)->toHaveCount(2);
});

it('can get full display name', function () {
    $region = Region::factory()->create(['name' => 'Test Region', 'country_id' => $this->country->id]);
    $region->translations()->create([
            'locale' => 'en',
        'name' => 'English Region Name',
        'description' => 'English Description',
    ]);
    
    $fullName = $region->getFullDisplayName('en');
    
    expect($fullName)->toContain('English Region Name');
    expect($fullName)->toContain($this->country->name);
});

it('can get hierarchy info', function () {
    $region = Region::factory()->create(['level' => 2]);
    
    $hierarchy = $region->getHierarchyInfo();
    
    expect($hierarchy)->toHaveKey('level');
    expect($hierarchy)->toHaveKey('level_name');
    expect($hierarchy)->toHaveKey('depth');
    expect($hierarchy['level'])->toBe(2);
});

it('can get level name', function () {
    $region = Region::factory()->create(['level' => 1]);
    
    expect($region->getLevelName())->toBe('State/Province');
    
    $region->level = 2;
    expect($region->getLevelName())->toBe('County');
});

it('can get geographic info', function () {
        $region = Region::factory()->create([
        'country_id' => $this->country->id,
        'zone_id' => $this->zone->id,
    ]);
    
    $geoInfo = $region->getGeographicInfo();
    
    expect($geoInfo)->toHaveKey('country');
    expect($geoInfo)->toHaveKey('zone');
    expect($geoInfo['country']['id'])->toBe($this->country->id);
    expect($geoInfo['zone']['id'])->toBe($this->zone->id);
});

it('can get business info', function () {
        $region = Region::factory()->create();
    
    City::factory()->count(3)->create(['region_id' => $region->id]);
    Address::factory()->count(2)->create(['region_id' => $region->id]);
    // User::factory()->count(5)->create(['region_id' => $region->id]); // users table doesn't have region_id
    
    $businessInfo = $region->getBusinessInfo();
    
    expect($businessInfo['cities_count'])->toBe(3);
    expect($businessInfo['addresses_count'])->toBe(2);
    // expect($businessInfo['users_count'])->toBe(5); // users table doesn't have region_id
});

it('can get complete info', function () {
    $region = Region::factory()->create(['name' => 'Test Region']);
    $region->translations()->create([
        'locale' => 'en',
        'name' => 'English Region Name',
        'description' => 'English Description',
    ]);
    
    $completeInfo = $region->getCompleteInfo('en');
    
    expect($completeInfo)->toHaveKey('basic');
    expect($completeInfo)->toHaveKey('hierarchy');
    expect($completeInfo)->toHaveKey('geographic');
    expect($completeInfo)->toHaveKey('business');
    expect($completeInfo)->toHaveKey('status');
    expect($completeInfo['basic']['name'])->toBe('English Region Name');
});