<?php

declare(strict_types=1);

namespace Tests\Admin\Resources;

use App\Filament\Resources\UserPreferenceResource;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class UserPreferenceResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create([
            'email' => 'admin@example.com',
            'name' => 'Test Admin',
        ]);

        $this->actingAs($this->user);
    }

    public function test_user_preference_resource_can_render_index_page(): void
    {
        $this->get(UserPreferenceResource::getUrl('index'))
            ->assertOk();
    }

    public function test_user_preference_resource_can_render_create_page(): void
    {
        $this->get(UserPreferenceResource::getUrl('create'))
            ->assertOk();
    }

    public function test_user_preference_resource_can_create_record(): void
    {
        $user = User::factory()->create();

        Livewire::test(UserPreferenceResource\Pages\CreateUserPreference::class)
            ->fillForm([
                'user_id' => $user->id,
                'preference_type' => 'category',
                'preference_key' => 'electronics',
                'preference_score' => 0.85,
                'last_updated' => now(),
                'metadata' => ['source' => 'purchase_history'],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $user->id,
            'preference_type' => 'category',
            'preference_key' => 'electronics',
            'preference_score' => 0.85,
        ]);
    }

    public function test_user_preference_resource_can_edit_record(): void
    {
        $user = User::factory()->create();
        $userPreference = UserPreference::factory()->create([
            'user_id' => $user->id,
            'preference_type' => 'brand',
            'preference_key' => 'apple',
            'preference_score' => 0.75,
        ]);

        Livewire::test(UserPreferenceResource\Pages\EditUserPreference::class, [
            'record' => $userPreference->getRouteKey(),
        ])
            ->fillForm([
                'preference_score' => 0.95,
                'metadata' => ['source' => 'updated_preference'],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('user_preferences', [
            'id' => $userPreference->id,
            'preference_score' => 0.95,
            'metadata' => json_encode(['source' => 'updated_preference']),
        ]);
    }

    public function test_user_preference_resource_can_view_record(): void
    {
        $user = User::factory()->create();
        $userPreference = UserPreference::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->get(UserPreferenceResource::getUrl('view', ['record' => $userPreference]))
            ->assertOk();
    }

    public function test_user_preference_resource_can_delete_record(): void
    {
        $user = User::factory()->create();
        $userPreference = UserPreference::factory()->create([
            'user_id' => $user->id,
        ]);

        Livewire::test(UserPreferenceResource\Pages\EditUserPreference::class, [
            'record' => $userPreference->getRouteKey(),
        ])
            ->callAction('delete')
            ->assertOk();

        $this->assertDatabaseMissing('user_preferences', [
            'id' => $userPreference->id,
        ]);
    }

    public function test_user_preference_resource_has_required_form_validation(): void
    {
        Livewire::test(UserPreferenceResource\Pages\CreateUserPreference::class)
            ->fillForm([
                'preference_type' => '',
                'preference_score' => 1.5, // Invalid: should be <= 1
            ])
            ->call('create')
            ->assertHasFormErrors(['user_id', 'preference_type', 'preference_score']);
    }

    public function test_user_preference_resource_preference_score_validation(): void
    {
        $user = User::factory()->create();

        Livewire::test(UserPreferenceResource\Pages\CreateUserPreference::class)
            ->fillForm([
                'user_id' => $user->id,
                'preference_type' => 'category',
                'preference_score' => -0.1, // Invalid: should be >= 0
            ])
            ->call('create')
            ->assertHasFormErrors(['preference_score']);
    }

    public function test_user_preference_resource_reset_preference_action(): void
    {
        $user = User::factory()->create();
        $userPreference = UserPreference::factory()->create([
            'user_id' => $user->id,
            'preference_score' => 0.85,
        ]);

        Livewire::test(UserPreferenceResource\Pages\ListUserPreferences::class)
            ->callTableAction('reset_preference', $userPreference)
            ->assertOk();

        $this->assertDatabaseHas('user_preferences', [
            'id' => $userPreference->id,
            'preference_score' => 0,
        ]);
    }

    public function test_user_preference_resource_bulk_reset_preferences(): void
    {
        $user = User::factory()->create();
        $userPreferences = UserPreference::factory()->count(3)->create([
            'user_id' => $user->id,
            'preference_score' => 0.85,
        ]);

        Livewire::test(UserPreferenceResource\Pages\ListUserPreferences::class)
            ->callTableBulkAction('reset_preferences', $userPreferences)
            ->assertOk();

        foreach ($userPreferences as $preference) {
            $this->assertDatabaseHas('user_preferences', [
                'id' => $preference->id,
                'preference_score' => 0,
            ]);
        }
    }

    public function test_user_preference_resource_filters_by_user(): void
    {
        $user1 = User::factory()->create(['name' => 'John Doe']);
        $user2 = User::factory()->create(['name' => 'Jane Smith']);

        UserPreference::factory()->create(['user_id' => $user1->id]);
        UserPreference::factory()->create(['user_id' => $user2->id]);

        Livewire::test(UserPreferenceResource\Pages\ListUserPreferences::class)
            ->filterTable('user_id', $user1->id)
            ->assertCanSeeTableRecords(UserPreference::where('user_id', $user1->id)->get())
            ->assertCanNotSeeTableRecords(UserPreference::where('user_id', $user2->id)->get());
    }

    public function test_user_preference_resource_filters_by_preference_type(): void
    {
        UserPreference::factory()->create(['preference_type' => 'category']);
        UserPreference::factory()->create(['preference_type' => 'brand']);

        Livewire::test(UserPreferenceResource\Pages\ListUserPreferences::class)
            ->filterTable('preference_type', 'category')
            ->assertCanSeeTableRecords(UserPreference::where('preference_type', 'category')->get())
            ->assertCanNotSeeTableRecords(UserPreference::where('preference_type', 'brand')->get());
    }

    public function test_user_preference_resource_score_range_filter(): void
    {
        UserPreference::factory()->create(['preference_score' => 0.3]);
        UserPreference::factory()->create(['preference_score' => 0.7]);
        UserPreference::factory()->create(['preference_score' => 0.9]);

        Livewire::test(UserPreferenceResource\Pages\ListUserPreferences::class)
            ->filterTable('score_range', [
                'min_score' => 0.5,
                'max_score' => 0.8,
            ])
            ->assertCanSeeTableRecords(UserPreference::whereBetween('preference_score', [0.5, 0.8])->get())
            ->assertCanNotSeeTableRecords(UserPreference::where('preference_score', '<', 0.5)->get())
            ->assertCanNotSeeTableRecords(UserPreference::where('preference_score', '>', 0.8)->get());
    }

    public function test_user_preference_resource_searchable_columns(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        UserPreference::factory()->create([
            'user_id' => $user->id,
            'preference_key' => 'electronics',
        ]);

        Livewire::test(UserPreferenceResource\Pages\ListUserPreferences::class)
            ->searchTable('John')
            ->assertCanSeeTableRecords(UserPreference::whereHas('user', fn ($q) => $q->where('name', 'like', '%John%'))->get());
    }

    public function test_user_preference_resource_sorts_by_score_descending(): void
    {
        UserPreference::factory()->create(['preference_score' => 0.3]);
        UserPreference::factory()->create(['preference_score' => 0.9]);
        UserPreference::factory()->create(['preference_score' => 0.6]);

        $records = UserPreference::orderByDesc('preference_score')->get();

        Livewire::test(UserPreferenceResource\Pages\ListUserPreferences::class)
            ->assertCanSeeTableRecords($records);
    }

    public function test_user_preference_resource_relationship_select(): void
    {
        $user = User::factory()->create(['name' => 'Test User']);

        Livewire::test(UserPreferenceResource\Pages\CreateUserPreference::class)
            ->fillForm([
                'user_id' => $user->id,
                'preference_type' => 'category',
            ])
            ->assertFormSet([
                'user_id' => $user->id,
            ]);
    }

    public function test_user_preference_resource_metadata_key_value_storage(): void
    {
        $user = User::factory()->create();

        $metadata = [
            'source' => 'purchase_history',
            'frequency' => 'high',
            'category_preference' => 'electronics',
        ];

        Livewire::test(UserPreferenceResource\Pages\CreateUserPreference::class)
            ->fillForm([
                'user_id' => $user->id,
                'preference_type' => 'category',
                'preference_key' => 'electronics',
                'metadata' => $metadata,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $user->id,
            'metadata' => json_encode($metadata),
        ]);
    }

    public function test_user_preference_resource_navigation_labels(): void
    {
        $this->assertEquals('admin.user_preferences.navigation_label', UserPreferenceResource::getNavigationLabel());
        $this->assertEquals('admin.user_preferences.plural_model_label', UserPreferenceResource::getPluralModelLabel());
        $this->assertEquals('admin.user_preferences.model_label', UserPreferenceResource::getModelLabel());
    }

    public function test_user_preference_resource_navigation_group(): void
    {
        $this->assertEquals('Users', UserPreferenceResource::getNavigationGroup());
        $this->assertEquals(6, UserPreferenceResource::getNavigationSort());
    }

    public function test_user_preference_resource_model_relationship(): void
    {
        $user = User::factory()->create();
        $userPreference = UserPreference::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $userPreference->user);
        $this->assertEquals($user->id, $userPreference->user->id);
    }
}
