<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\Settings\Pages\CreateSetting;
use App\Filament\Resources\Settings\Pages\EditSetting;
use App\Filament\Resources\Settings\Pages\ListSettings;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class SettingResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Prime the Filament admin panel context so Livewire components resolve shared services.
        $this->resolveAdminPanel();

        // Seed the expected role and permission map leveraged by Filament resource policies.
        $this->seed(RolesAndPermissionsSeeder::class);

        // Provision an administrator account mirroring the default Filament guard configuration.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->admin->assignRole('administrator');
    }

    public function test_list_settings_displays_seeded_records(): void
    {
        // Generate a deterministic settings collection so the table renders known rows.
        $settings = Setting::factory()->count(2)->create([
            'type' => 'string',
        ]);

        $this->actingAs($this->admin);

        // Hydrate the table before asserting the seeded records appear in the listing.
        Livewire::test(ListSettings::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords($settings);
    }

    public function test_admin_can_create_setting(): void
    {
        $this->actingAs($this->admin);

        // Provide concrete form input that mirrors a realistic configuration entry.
        $payload = [
            'key'          => 'site.tagline',
            'value'        => 'Filament powered storefront tagline.',
            'type'         => 'string',
            'description'  => 'Public facing marketing blurb.',
            'is_public'    => true,
            'display_name' => 'Site Tagline',
            'group'        => 'marketing',
            'is_required'  => false,
            'is_encrypted' => false,
        ];

        Livewire::test(CreateSetting::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();

        // Confirm the persisted record matches the submitted payload.
        $this->assertDatabaseHas('settings', [
            'key'          => 'site.tagline',
            'type'         => 'string',
            'group'        => 'marketing',
            'is_public'    => true,
            'is_encrypted' => false,
        ]);
    }

    public function test_admin_can_update_setting(): void
    {
        // Seed an existing setting row that will be updated through the edit form.
        $setting = Setting::factory()->create([
            'key'          => 'site.name',
            'value'        => 'Original name',
            'type'         => 'string',
            'description'  => 'Original description',
            'is_public'    => true,
            'display_name' => 'Site Name',
            'group'        => 'branding',
            'is_required'  => true,
            'is_encrypted' => false,
        ]);

        $this->actingAs($this->admin);

        // Update the setting with revised metadata and confirm validation passes.
        Livewire::test(EditSetting::class, ['record' => $setting->getRouteKey()])
            ->fillForm([
                'key'          => 'site.name',
                'value'        => 'Updated storefront name',
                'type'         => 'string',
                'description'  => 'Updated description for the storefront name.',
                'is_public'    => false,
                'display_name' => 'Storefront Name',
                'group'        => 'marketing',
                'is_required'  => false,
                'is_encrypted' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        // Ensure the database reflects the revised configuration.
        $this->assertDatabaseHas('settings', [
            'id'           => $setting->id,
            'value'        => 'Updated storefront name',
            'description'  => 'Updated description for the storefront name.',
            'group'        => 'marketing',
            'is_public'    => false,
            'is_encrypted' => true,
        ]);
    }

    public function test_admin_can_delete_setting(): void
    {
        // Create a disposable setting entry that will be removed through the delete action.
        $setting = Setting::factory()->create([
            'type'        => 'string',
            'is_public'   => false,
            'is_required' => false,
        ]);

        $this->actingAs($this->admin);

        // Trigger the delete action exposed on the edit page to remove the record.
        Livewire::test(EditSetting::class, ['record' => $setting->getRouteKey()])
            ->callAction('delete')
            ->assertHasNoActionErrors();

        // Verify the record no longer exists to confirm the delete action executed successfully.
        $this->assertDatabaseMissing('settings', [
            'id' => $setting->id,
        ]);
    }
}
