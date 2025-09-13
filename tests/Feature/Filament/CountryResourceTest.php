<?php

declare(strict_types=1);

use App\Filament\Resources\CountryResource;
use App\Models\Country;
use App\Models\Translations\CountryTranslation;
use App\Models\User;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can list countries', function () {
    $countries = Country::factory()->count(3)->create();

    Livewire::test(CountryResource\Pages\ListCountries::class)
        ->assertCanSeeTableRecords($countries);
});

it('can create country', function () {
    $newCountry = Country::factory()->make();

    Livewire::test(CountryResource\Pages\CreateCountry::class)
        ->fillForm([
            'name' => $newCountry->name,
            'cca2' => $newCountry->cca2,
            'cca3' => $newCountry->cca3,
            'currency_code' => $newCountry->currency_code,
            'region' => $newCountry->region,
            'is_active' => $newCountry->is_active,
            'is_eu_member' => $newCountry->is_eu_member,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('countries', [
        'name' => $newCountry->name,
        'cca2' => $newCountry->cca2,
        'cca3' => $newCountry->cca3,
    ]);
});

it('can validate required fields when creating country', function () {
    Livewire::test(CountryResource\Pages\CreateCountry::class)
        ->fillForm([
            'name' => '',
            'cca2' => '',
            'cca3' => '',
        ])
        ->call('create')
        ->assertHasFormErrors(['name', 'cca2', 'cca3']);
});

it('can validate unique cca2 when creating country', function () {
    $existingCountry = Country::factory()->create(['cca2' => 'LT']);

    Livewire::test(CountryResource\Pages\CreateCountry::class)
        ->fillForm([
            'name' => 'New Country',
            'cca2' => 'LT', // Same as existing
            'cca3' => 'NEW',
        ])
        ->call('create')
        ->assertHasFormErrors(['cca2']);
});

it('can validate unique cca3 when creating country', function () {
    $existingCountry = Country::factory()->create(['cca3' => 'LTU']);

    Livewire::test(CountryResource\Pages\CreateCountry::class)
        ->fillForm([
            'name' => 'New Country',
            'cca2' => 'NC',
            'cca3' => 'LTU', // Same as existing
        ])
        ->call('create')
        ->assertHasFormErrors(['cca3']);
});

it('can edit country', function () {
    $country = Country::factory()->create();

    Livewire::test(CountryResource\Pages\EditCountry::class, ['record' => $country->getRouteKey()])
        ->fillForm([
            'name' => 'Updated Country Name',
            'region' => 'Updated Region',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($country->fresh()->name)->toBe('Updated Country Name');
    expect($country->fresh()->region)->toBe('Updated Region');
});

it('can view country', function () {
    $country = Country::factory()->create();

    Livewire::test(CountryResource\Pages\ViewCountry::class, ['record' => $country->getRouteKey()])
        ->assertFormSet([
            'name' => $country->name,
            'cca2' => $country->cca2,
            'cca3' => $country->cca3,
        ]);
});

it('can delete country', function () {
    $country = Country::factory()->create();

    Livewire::test(CountryResource\Pages\ListCountries::class)
        ->callTableAction(DeleteAction::class, $country);

    $this->assertSoftDeleted('countries', ['id' => $country->id]);
});

it('can filter countries by active status', function () {
    $activeCountry = Country::factory()->active()->create();
    $inactiveCountry = Country::factory()->inactive()->create();

    Livewire::test(CountryResource\Pages\ListCountries::class)
        ->filterTable('is_active', 'true')
        ->assertCanSeeTableRecords([$activeCountry])
        ->assertCanNotSeeTableRecords([$inactiveCountry]);
});

it('can filter countries by eu membership', function () {
    $euCountry = Country::factory()->euMember()->create();
    $nonEuCountry = Country::factory()->create(['is_eu_member' => false]);

    Livewire::test(CountryResource\Pages\ListCountries::class)
        ->filterTable('is_eu_member', 'true')
        ->assertCanSeeTableRecords([$euCountry])
        ->assertCanNotSeeTableRecords([$nonEuCountry]);
});

it('can filter countries by region', function () {
    $europeanCountry = Country::factory()->european()->create();
    $asianCountry = Country::factory()->asian()->create();

    Livewire::test(CountryResource\Pages\ListCountries::class)
        ->filterTable('region', 'Europe')
        ->assertCanSeeTableRecords([$europeanCountry])
        ->assertCanNotSeeTableRecords([$asianCountry]);
});

it('can filter countries by currency', function () {
    $eurCountry = Country::factory()->create(['currency_code' => 'EUR']);
    $usdCountry = Country::factory()->create(['currency_code' => 'USD']);

    Livewire::test(CountryResource\Pages\ListCountries::class)
        ->filterTable('currency_code', 'EUR')
        ->assertCanSeeTableRecords([$eurCountry])
        ->assertCanNotSeeTableRecords([$usdCountry]);
});

it('can search countries by name', function () {
    $lithuania = Country::factory()->create(['name' => 'Lithuania']);
    $latvia = Country::factory()->create(['name' => 'Latvia']);

    Livewire::test(CountryResource\Pages\ListCountries::class)
        ->searchTable('Lithuania')
        ->assertCanSeeTableRecords([$lithuania])
        ->assertCanNotSeeTableRecords([$latvia]);
});

it('can search countries by cca2 code', function () {
    $lithuania = Country::factory()->create(['cca2' => 'LT']);
    $latvia = Country::factory()->create(['cca2' => 'LV']);

    Livewire::test(CountryResource\Pages\ListCountries::class)
        ->searchTable('LT')
        ->assertCanSeeTableRecords([$lithuania])
        ->assertCanNotSeeTableRecords([$latvia]);
});

it('can sort countries by name', function () {
    $latvia = Country::factory()->create(['name' => 'Latvia']);
    $lithuania = Country::factory()->create(['name' => 'Lithuania']);

    Livewire::test(CountryResource\Pages\ListCountries::class)
        ->sortTable('translated_name')
        ->assertCanSeeTableRecords([$latvia, $lithuania], inOrder: true);
});

it('can sort countries by region', function () {
    $asia = Country::factory()->create(['region' => 'Asia']);
    $europe = Country::factory()->create(['region' => 'Europe']);

    Livewire::test(CountryResource\Pages\ListCountries::class)
        ->sortTable('region')
        ->assertCanSeeTableRecords([$asia, $europe], inOrder: true);
});

it('can bulk delete countries', function () {
    $countries = Country::factory()->count(3)->create();

    Livewire::test(CountryResource\Pages\ListCountries::class)
        ->callTableBulkAction('delete', $countries);

    foreach ($countries as $country) {
        $this->assertSoftDeleted('countries', ['id' => $country->id]);
    }
});

it('can bulk activate countries', function () {
    $countries = Country::factory()->count(3)->inactive()->create();

    Livewire::test(CountryResource\Pages\ListCountries::class)
        ->callTableBulkAction('activate', $countries);

    foreach ($countries as $country) {
        expect($country->fresh()->is_active)->toBeTrue();
    }
});

it('can bulk deactivate countries', function () {
    $countries = Country::factory()->count(3)->active()->create();

    Livewire::test(CountryResource\Pages\ListCountries::class)
        ->callTableBulkAction('deactivate', $countries);

    foreach ($countries as $country) {
        expect($country->fresh()->is_active)->toBeFalse();
    }
});

it('can create country with translations', function () {
    $newCountry = Country::factory()->make();

    Livewire::test(CountryResource\Pages\CreateCountry::class)
        ->fillForm([
            'name' => $newCountry->name,
            'cca2' => $newCountry->cca2,
            'cca3' => $newCountry->cca3,
            'currency_code' => $newCountry->currency_code,
            'region' => $newCountry->region,
            'is_active' => $newCountry->is_active,
            'is_eu_member' => $newCountry->is_eu_member,
            // Translation fields
            'name_lt' => 'Lietuva',
            'name_official_lt' => 'Lietuvos Respublika',
            'description_lt' => 'Šiaurės Europos šalis',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $country = Country::where('cca2', $newCountry->cca2)->first();
    expect($country)->not->toBeNull();

    $translation = $country->translations()->where('locale', 'lt')->first();
    expect($translation)->not->toBeNull();
    expect($translation->name)->toBe('Lietuva');
    expect($translation->name_official)->toBe('Lietuvos Respublika');
    expect($translation->description)->toBe('Šiaurės Europos šalis');
});

it('can edit country translations', function () {
    $country = Country::factory()->create();
    $translation = CountryTranslation::factory()->create([
        'country_id' => $country->id,
        'locale' => 'lt',
        'name' => 'Lietuva',
    ]);

    Livewire::test(CountryResource\Pages\EditCountry::class, ['record' => $country->getRouteKey()])
        ->fillForm([
            'name_lt' => 'Atnaujinta Lietuva',
            'name_official_lt' => 'Atnaujinta Lietuvos Respublika',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($translation->fresh()->name)->toBe('Atnaujinta Lietuva');
    expect($translation->fresh()->name_official)->toBe('Atnaujinta Lietuvos Respublika');
});

it('can view country with translations', function () {
    $country = Country::factory()->create();
    $translation = CountryTranslation::factory()->create([
        'country_id' => $country->id,
        'locale' => 'lt',
        'name' => 'Lietuva',
    ]);

    Livewire::test(CountryResource\Pages\ViewCountry::class, ['record' => $country->getRouteKey()])
        ->assertFormSet([
            'name' => $country->name,
            'name_lt' => 'Lietuva',
        ]);
});

it('can handle countries with complex data', function () {
    $country = Country::factory()->create([
        'currencies' => [
            'EUR' => [
                'name' => 'Euro',
                'symbol' => '€',
            ],
        ],
        'languages' => [
            'lt' => 'Lithuanian',
            'en' => 'English',
        ],
        'timezones' => [
            'UTC+2' => 'Eastern European Time',
        ],
        'metadata' => [
            'population' => 2800000,
            'area' => 65300,
        ],
    ]);

    Livewire::test(CountryResource\Pages\ViewCountry::class, ['record' => $country->getRouteKey()])
        ->assertFormSet([
            'name' => $country->name,
            'currencies' => $country->currencies,
            'languages' => $country->languages,
            'timezones' => $country->timezones,
            'metadata' => $country->metadata,
        ]);
});
