<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\SystemSettingResource\Pages\CreateSystemSetting;
use App\Filament\Resources\SystemSettingResource\Pages\ListSystemSettings;
use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class SystemSettingResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Initialise the Filament admin panel so cached schema metadata aligns with production boot order.
        $this->resolveAdminPanel();

        // Seed the permissions blueprint to ensure the super admin role controls system settings.
        $this->seed(RolesAndPermissionsSeeder::class);

        // Provision a deterministic administrator and grant super admin privileges for configuration tasks.
        $this->adminUser = User::factory()->create([
            'email'    => 'settings-admin@example.test',
            'is_admin' => true,
        ]);
        $this->adminUser->assignRole('super_admin');
    }

    public function test_list_page_displays_active_and_inactive_settings(): void
    {
        // Create paired settings to verify the resource surfaces rows regardless of activation state.
        $active = SystemSetting::factory()->active()->create();
        $inactive = SystemSetting::factory()->inactive()->create();

        Livewire::actingAs($this->adminUser)
            ->test(ListSystemSettings::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$active, $inactive]);
    }

    public function test_category_filter_scopes_visible_settings(): void
    {
        // Generate settings across categories so filtering isolates the selected grouping.
        $primaryCategory = SystemSettingCategory::factory()->create(['name' => 'Primary', 'slug' => 'primary']);
        $secondaryCategory = SystemSettingCategory::factory()->create(['name' => 'Secondary', 'slug' => 'secondary']);

        $primarySetting = SystemSetting::factory()->for($primaryCategory, 'categoryRelation')->create();
        $secondarySetting = SystemSetting::factory()->for($secondaryCategory, 'categoryRelation')->create();

        Livewire::actingAs($this->adminUser)
            ->test(ListSystemSettings::class)
            ->call('loadTable')
            ->filterTable('category_id', (string) $primaryCategory->getKey())
            ->assertCanSeeTableRecords([$primarySetting])
            ->assertCanNotSeeTableRecords([$secondarySetting]);
    }

    public function test_admin_can_create_system_setting(): void
    {
        $category = SystemSettingCategory::factory()->create([
            'name'      => 'Integration',
            'slug'      => 'integration',
            'is_active' => true,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(CreateSystemSetting::class)
            ->fillForm([
                'key'         => 'integration.api_key',
                'name'        => 'Integration API Key',
                'category_id' => $category->getKey(),
                'description' => 'API credential used for integration testing.',
                'help_text'   => 'Store the staging API key here.',
                'type'        => 'string',
                'value'       => 'staging-key-123',
                'group'       => 'integrations',
                'unit'        => null,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_settings', [
            'key'         => 'integration.api_key',
            'category_id' => $category->getKey(),
        ]);
    }
}
