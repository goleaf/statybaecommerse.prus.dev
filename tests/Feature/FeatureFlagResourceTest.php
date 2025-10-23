<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\FeatureFlag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class FeatureFlagResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'name' => 'Admin Example',
        ]);

        $this->actingAs($this->adminUser);
    }

    public function test_can_list_feature_flags(): void
    {
        FeatureFlag::factory()->count(3)->create();

        $this
            ->get('/admin/feature-flags')
            ->assertOk()
            ->assertSee('Feature Flags')
            ->assertSee($this->adminUser->name);
    }

    public function test_can_create_feature_flag(): void
    {
        $featureFlagData = [
            'name'        => 'New Feature',
            'key'         => 'new_feature',
            'description' => 'A new feature flag',
            'is_active'   => true,
            'is_enabled'  => false,
            'is_global'   => false,
            'environment' => 'production',
            'category'    => 'ui',
            'priority'    => 50,
        ];

        Livewire::test('App\Filament\Resources\FeatureFlagResource\Pages\CreateFeatureFlag')
            ->fillForm($featureFlagData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('feature_flags', [
            'name' => 'New Feature',
            'key' => 'new_feature',
            'created_by' => $this->adminUser->id,
            'created_by_name' => $this->adminUser->name,
            'updated_by' => $this->adminUser->id,
            'updated_by_name' => $this->adminUser->name,
        ]);
    }

    public function test_can_edit_feature_flag(): void
    {
        $featureFlag = FeatureFlag::factory()->create([
            'name' => 'Test Feature',
            'key'  => 'test_feature',
        ]);

        $updatedData = [
            'name'        => 'Updated Feature',
            'description' => 'Updated description',
            'is_enabled'  => true,
        ];

        Livewire::test('App\Filament\Resources\FeatureFlagResource\Pages\EditFeatureFlag', [
            'record' => $featureFlag->getRouteKey(),
        ])
            ->fillForm($updatedData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('feature_flags', [
            'id'          => $featureFlag->id,
            'name'        => 'Updated Feature',
            'description' => 'Updated description',
            'is_enabled'  => true,
        ]);
    }

    public function test_can_view_feature_flag(): void
    {
        $featureFlag = FeatureFlag::factory()->create();

        $this
            ->get("/admin/feature-flags/{$featureFlag->id}")
            ->assertOk()
            ->assertSee($featureFlag->name);
    }

    public function test_can_delete_feature_flag(): void
    {
        $featureFlag = FeatureFlag::factory()->create();

        Livewire::test('App\Filament\Resources\FeatureFlagResource\Pages\EditFeatureFlag', [
            'record' => $featureFlag->getRouteKey(),
        ])
            ->callAction('delete')
            ->assertHasNoActionErrors();

        $this->assertDatabaseMissing('feature_flags', [
            'id' => $featureFlag->id,
        ]);
    }

    public function test_can_filter_by_category(): void
    {
        FeatureFlag::factory()->create(['category' => 'ui']);
        FeatureFlag::factory()->create(['category' => 'performance']);

        $this
            ->get('/admin/feature-flags?tableFilters[category][value]=ui')
            ->assertOk()
            ->assertSee('ui')
            ->assertDontSee('performance');
    }

    public function test_can_filter_by_environment(): void
    {
        FeatureFlag::factory()->create(['environment' => 'production']);
        FeatureFlag::factory()->create(['environment' => 'staging']);

        $this
            ->get('/admin/feature-flags?tableFilters[environment][value]=production')
            ->assertOk();
    }

    public function test_can_filter_by_active_status(): void
    {
        FeatureFlag::factory()->create(['is_active' => true]);
        FeatureFlag::factory()->create(['is_active' => false]);

        $this
            ->get('/admin/feature-flags?tableFilters[is_active][value]=1')
            ->assertOk();
    }

    public function test_can_filter_by_enabled_status(): void
    {
        FeatureFlag::factory()->create(['is_enabled' => true]);
        FeatureFlag::factory()->create(['is_enabled' => false]);

        $this
            ->get('/admin/feature-flags?tableFilters[is_enabled][value]=1')
            ->assertOk();
    }

    public function test_can_filter_by_global_status(): void
    {
        FeatureFlag::factory()->create(['is_global' => true]);
        FeatureFlag::factory()->create(['is_global' => false]);

        $this
            ->get('/admin/feature-flags?tableFilters[is_global][value]=1')
            ->assertOk();
    }

    public function test_feature_flag_validation(): void
    {
        Livewire::test('App\Filament\Resources\FeatureFlagResource\Pages\CreateFeatureFlag')
            ->fillForm([
                'name' => '',
                'key'  => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['name', 'key']);
    }

    public function test_feature_flag_unique_key_validation(): void
    {
        FeatureFlag::factory()->create(['key' => 'existing_key']);

        Livewire::test('App\Filament\Resources\FeatureFlagResource\Pages\CreateFeatureFlag')
            ->fillForm([
                'name'        => 'Test Feature',
                'key'         => 'existing_key',
                'description' => 'Test description',
            ])
            ->call('create')
            ->assertHasFormErrors(['key']);
    }

    public function test_feature_flag_key_alpha_dash_validation(): void
    {
        Livewire::test('App\Filament\Resources\FeatureFlagResource\Pages\CreateFeatureFlag')
            ->fillForm([
                'name'        => 'Test Feature',
                'key'         => 'invalid key with spaces',
                'description' => 'Test description',
            ])
            ->call('create')
            ->assertHasFormErrors(['key']);
    }

    public function test_can_bulk_delete_feature_flags(): void
    {
        $featureFlags = FeatureFlag::factory()->count(3)->create();

        Livewire::test('App\Filament\Resources\FeatureFlagResource\Pages\ListFeatureFlags')
            ->callTableBulkAction('delete', $featureFlags);

        foreach ($featureFlags as $featureFlag) {
            $this->assertDatabaseMissing('feature_flags', [
                'id' => $featureFlag->id,
            ]);
        }
    }

    public function test_feature_flags_resource_can_create_feature_flag(): void
    {
        $featureFlagData = [
            'name' => 'Another Feature',
            'key' => 'another_feature',
            'description' => 'Another feature flag',
            'is_active' => true,
            'is_enabled' => false,
            'is_global' => false,
            'environment' => 'staging',
            'category' => 'backend',
            'priority' => 10,
        ];

        Livewire::test('App\Filament\Resources\FeatureFlags\Pages\CreateFeatureFlag')
            ->fillForm($featureFlagData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('feature_flags', [
            'name' => 'Another Feature',
            'key' => 'another_feature',
            'created_by' => $this->adminUser->id,
            'created_by_name' => $this->adminUser->name,
            'updated_by' => $this->adminUser->id,
            'updated_by_name' => $this->adminUser->name,
        ]);
    }

    public function test_feature_flags_resource_invalid_key_validation(): void
    {
        Livewire::test('App\Filament\Resources\FeatureFlags\Pages\CreateFeatureFlag')
            ->fillForm([
                'name' => 'Invalid Feature',
                'key' => 'invalid key!',
                'is_active' => true,
                'is_enabled' => false,
                'is_global' => false,
            ])
            ->call('create')
            ->assertHasFormErrors(['key']);
    }

    public function test_feature_flags_resource_duplicate_key_validation_on_edit(): void
    {
        FeatureFlag::factory()->create(['key' => 'existing_key']);
        $featureFlag = FeatureFlag::factory()->create(['key' => 'original_key']);

        Livewire::test('App\Filament\Resources\FeatureFlags\Pages\EditFeatureFlag', [
            'record' => $featureFlag->getRouteKey(),
        ])
            ->fillForm([
                'key' => 'existing_key',
            ])
            ->call('save')
            ->assertHasFormErrors(['key']);
    }
}
