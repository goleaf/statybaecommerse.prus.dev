<?php declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\LocationResource;
use App\Models\Country;
use App\Models\Location;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LocationResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Country $country;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user with proper permissions
        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
            'name' => 'Admin User',
        ]);

        // Give the user admin permissions
        $this->adminUser->assignRole('super_admin');

        // Create a country for testing
        $this->country = Country::factory()->create([
            'name' => 'Lithuania',
            'cca2' => 'LT',
            'cca3' => 'LTU',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_render_location_index_page(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get(LocationResource::getUrl('index'));

        $response->assertSuccessful();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_list_locations(): void
    {
        $this->actingAs($this->adminUser);

        $locations = Location::factory()->count(3)->create([
            'country_id' => $this->country->id,
        ]);

        Livewire::test(LocationResource\Pages\ListLocations::class)
            ->assertCanSeeTableRecords($locations);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_render_location_create_page(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get(LocationResource::getUrl('create'));

        $response->assertSuccessful();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_create_location(): void
    {
        $this->actingAs($this->adminUser);

        $newData = [
            'address' => 'Test Address 123',
            'city' => 'Vilnius',
            'state' => 'Vilnius County',
            'postal_code' => '01234',
            'country_id' => $this->country->id,
            'phone' => '+370 123 45678',
            'email' => 'test@location.com',
            'is_default' => false,
            'is_active' => true,
            // Multilanguage fields
            'name' => [
                'lt' => 'Testas Vieta',
                'en' => 'Test Location',
            ],
            'slug' => [
                'lt' => 'testas-vieta',
                'en' => 'test-location',
            ],
            'description' => [
                'lt' => 'Testas aprašymas',
                'en' => 'Test description',
            ],
        ];

        Livewire::test(LocationResource\Pages\CreateLocation::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('locations', [
            'address' => 'Test Address 123',
            'city' => 'Vilnius',
            'country_id' => $this->country->id,
            'email' => 'test@location.com',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_validate_required_fields(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(LocationResource\Pages\CreateLocation::class)
            ->fillForm([
                'address' => '',
                'country_id' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['country_id']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_render_location_edit_page(): void
    {
        $this->actingAs($this->adminUser);

        $location = Location::factory()->create([
            'country_id' => $this->country->id,
        ]);

        $response = $this->get(LocationResource::getUrl('edit', [
            'record' => $location,
        ]));

        $response->assertSuccessful();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_retrieve_location_data(): void
    {
        $this->actingAs($this->adminUser);

        $location = Location::factory()->create([
            'country_id' => $this->country->id,
            'address' => 'Original Address',
            'city' => 'Kaunas',
        ]);

        Livewire::test(LocationResource\Pages\EditLocation::class, [
            'record' => $location->getRouteKey(),
        ])
            ->assertFormSet([
                'address' => 'Original Address',
                'city' => 'Kaunas',
                'country_id' => $this->country->id,
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_save_location(): void
    {
        $this->actingAs($this->adminUser);

        $location = Location::factory()->create([
            'country_id' => $this->country->id,
        ]);

        $newData = [
            'address' => 'Updated Address 456',
            'city' => 'Klaipėda',
            'state' => 'Klaipėda County',
            'postal_code' => '56789',
            'country_id' => $this->country->id,
            'phone' => '+370 987 65432',
            'email' => 'updated@location.com',
            'is_default' => true,
            'is_active' => true,
        ];

        Livewire::test(LocationResource\Pages\EditLocation::class, [
            'record' => $location->getRouteKey(),
        ])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        expect($location->refresh())
            ->address
            ->toBe('Updated Address 456')
            ->city
            ->toBe('Klaipėda')
            ->email
            ->toBe('updated@location.com')
            ->is_default
            ->toBeTrue();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_delete_location(): void
    {
        $this->actingAs($this->adminUser);

        $location = Location::factory()->create([
            'country_id' => $this->country->id,
        ]);

        Livewire::test(LocationResource\Pages\EditLocation::class, [
            'record' => $location->getRouteKey(),
        ])
            ->callAction(TestAction::make('delete'))
            ->assertRedirect(LocationResource::getUrl('index'));

        $this->assertModelMissing($location);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_view_location(): void
    {
        $this->actingAs($this->adminUser);

        $location = Location::factory()->create([
            'country_id' => $this->country->id,
            'address' => 'View Test Address',
            'city' => 'Šiauliai',
        ]);

        $response = $this->get(LocationResource::getUrl('view', [
            'record' => $location,
        ]));

        $response->assertSuccessful();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_filter_locations_by_country(): void
    {
        $this->actingAs($this->adminUser);

        $otherCountry = Country::factory()->create([
            'name' => 'Latvia',
            'cca2' => 'LV',
            'cca3' => 'LVA',
        ]);

        $lithuanianLocation = Location::factory()->create([
            'country_id' => $this->country->id,
            'city' => 'Vilnius',
        ]);

        $latvianLocation = Location::factory()->create([
            'country_id' => $otherCountry->id,
            'city' => 'Riga',
        ]);

        Livewire::test(LocationResource\Pages\ListLocations::class)
            ->filterTable('country_id', $this->country->id)
            ->assertCanSeeTableRecords([$lithuanianLocation])
            ->assertCanNotSeeTableRecords([$latvianLocation]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_search_locations(): void
    {
        $this->actingAs($this->adminUser);

        $searchableLocation = Location::factory()->create([
            'country_id' => $this->country->id,
            'city' => 'Unique City Name',
        ]);

        $otherLocation = Location::factory()->create([
            'country_id' => $this->country->id,
            'city' => 'Different City',
        ]);

        Livewire::test(LocationResource\Pages\ListLocations::class)
            ->searchTable('Unique City')
            ->assertCanSeeTableRecords([$searchableLocation])
            ->assertCanNotSeeTableRecords([$otherLocation]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_sort_locations(): void
    {
        $this->actingAs($this->adminUser);

        $locationA = Location::factory()->create([
            'country_id' => $this->country->id,
            'city' => 'A City',
        ]);

        $locationZ = Location::factory()->create([
            'country_id' => $this->country->id,
            'city' => 'Z City',
        ]);

        Livewire::test(LocationResource\Pages\ListLocations::class)
            ->sortTable('city')
            ->assertCanSeeTableRecords([$locationA, $locationZ], inOrder: true)
            ->sortTable('city', 'desc')
            ->assertCanSeeTableRecords([$locationZ, $locationA], inOrder: true);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_bulk_delete_locations(): void
    {
        $this->actingAs($this->adminUser);

        $locations = Location::factory()->count(3)->create([
            'country_id' => $this->country->id,
        ]);

        Livewire::test(LocationResource\Pages\ListLocations::class)
            ->selectTableRecords($locations)
            ->callAction(TestAction::make('delete')->table()->bulk())
            ->assertCanNotSeeTableRecords($locations);

        foreach ($locations as $location) {
            $this->assertModelMissing($location);
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function validates_email_format(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(LocationResource\Pages\CreateLocation::class)
            ->fillForm([
                'email' => 'invalid-email',
                'country_id' => $this->country->id,
            ])
            ->call('create')
            ->assertHasFormErrors(['email']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function validates_phone_format(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(LocationResource\Pages\CreateLocation::class)
            ->fillForm([
                'phone' => 'invalid-phone-format-that-is-way-too-long-to-be-valid',
                'country_id' => $this->country->id,
            ])
            ->call('create')
            ->assertHasFormErrors(['phone']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_toggle_location_status(): void
    {
        $this->actingAs($this->adminUser);

        $location = Location::factory()->create([
            'country_id' => $this->country->id,
            'is_active' => true,
        ]);

        Livewire::test(LocationResource\Pages\EditLocation::class, [
            'record' => $location->getRouteKey(),
        ])
            ->fillForm(['is_active' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($location->refresh()->is_active)->toBeFalse();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_set_default_location(): void
    {
        $this->actingAs($this->adminUser);

        $existingDefault = Location::factory()->create([
            'country_id' => $this->country->id,
            'is_default' => true,
        ]);

        $newLocation = Location::factory()->create([
            'country_id' => $this->country->id,
            'is_default' => false,
        ]);

        Livewire::test(LocationResource\Pages\EditLocation::class, [
            'record' => $newLocation->getRouteKey(),
        ])
            ->fillForm(['is_default' => true])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($newLocation->refresh()->is_default)->toBeTrue();
    }
}
