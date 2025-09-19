<?php declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\PartnerResource;
use App\Models\Partner;
use App\Models\PartnerTier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class PartnerResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);

        $this->adminUser = User::factory()->create(['email' => 'admin@test.com']);
        $this->adminUser->assignRole('super_admin');
    }

    public function test_can_render_index_page(): void
    {
        $this->actingAs($this->adminUser)
            ->get(PartnerResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_list_displays_partners(): void
    {
        $this->actingAs($this->adminUser);

        $partners = Partner::factory()->count(3)->create();

        Livewire::test(PartnerResource\Pages\ListPartners::class)
            ->assertCanSeeTableRecords($partners);
    }

    public function test_can_create_partner(): void
    {
        $this->actingAs($this->adminUser);

        $tier = PartnerTier::factory()->create();

        Livewire::test(PartnerResource\Pages\CreatePartner::class)
            ->fillForm([
                'name' => 'New Partner Co',
                'code' => 'new-partner-co',
                'contact_email' => 'partner@example.test',
                'contact_phone' => '+37060012345',
                'tier_id' => $tier->id,
                'is_enabled' => true,
                'discount_rate' => 0.0500,
                'commission_rate' => 0.0100,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('partners', [
            'code' => 'new-partner-co',
            'contact_email' => 'partner@example.test',
            'is_enabled' => 1,
        ]);
    }

    public function test_can_update_partner(): void
    {
        $this->actingAs($this->adminUser);
        $partner = Partner::factory()->create([
            'name' => 'Old Name',
            'code' => 'old-code-001',
            'contact_email' => 'old@example.test',
            'is_enabled' => true,
        ]);

        Livewire::test(PartnerResource\Pages\EditPartner::class, ['record' => $partner->getKey()])
            ->fillForm([
                'name' => 'Updated Name',
                'contact_email' => 'updated@example.test',
                'is_enabled' => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('partners', [
            'id' => $partner->id,
            'name' => 'Updated Name',
            'contact_email' => 'updated@example.test',
            'is_enabled' => 0,
        ]);
    }

    public function test_can_delete_partner(): void
    {
        $this->actingAs($this->adminUser);
        $partner = Partner::factory()->create();

        Livewire::test(PartnerResource\Pages\EditPartner::class, ['record' => $partner->getKey()])
            ->callAction('delete');

        $this->assertSoftDeleted('partners', ['id' => $partner->id]);
    }
}

