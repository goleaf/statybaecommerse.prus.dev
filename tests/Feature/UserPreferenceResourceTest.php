<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\UserPreferenceResource\Pages\CreateUserPreference;
use App\Filament\Resources\UserPreferenceResource\Pages\EditUserPreference;
use App\Filament\Resources\UserPreferenceResource\Pages\ListUserPreferences;
use App\Filament\Resources\UserPreferenceResource\Pages\ViewUserPreference;
use App\Models\User;
use App\Models\UserPreference;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class UserPreferenceResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Boot the Filament admin panel so schemas, navigation, and authorization are initialised for the tests.
        $this->resolveAdminPanel();

        // Seed the canonical Spatie roles and permissions to grant the acting user full Filament access.
        $this->seed(RolesAndPermissionsSeeder::class);

        // Create an acting administrator and elevate them to the super_admin role for unrestricted access.
        $this->adminUser = User::factory()->create([
            'email' => 'admin-user-preferences@example.test',
        ]);
        $this->adminUser->assignRole('super_admin');
    }

    public function test_admin_can_list_user_preferences(): void
    {
        // Prepare a collection of preferences so we can assert the table renders real records.
        $preferences = UserPreference::factory()->count(3)->create();

        Livewire::actingAs($this->adminUser)
            ->test(ListUserPreferences::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords($preferences);
    }

    public function test_admin_can_create_user_preference(): void
    {
        // Create a related user to associate with the new preference entry.
        $user = User::factory()->create([
            'name'  => 'Preference Owner',
            'email' => 'preference-owner@example.test',
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(CreateUserPreference::class)
            ->fillForm([
                'user_id'          => $user->id,
                'preference_type'  => 'category',
                'preference_key'   => 'electronics',
                'preference_score' => 0.85,
                'last_updated'     => now()->format('Y-m-d H:i'),
                'metadata'         => [
                    'source'     => 'manual_override',
                    'frequency'  => 'high',
                    'confidence' => '0.95',
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('user_preferences', [
            'user_id'          => $user->id,
            'preference_type'  => 'category',
            'preference_key'   => 'electronics',
            'preference_score' => 0.85,
        ]);
    }

    public function test_admin_can_edit_user_preference(): void
    {
        // Seed a baseline preference so we can exercise the edit workflow.
        $preference = UserPreference::factory()->create([
            'preference_type'  => 'brand',
            'preference_key'   => 'acme',
            'preference_score' => 0.25,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(EditUserPreference::class, ['record' => $preference->getKey()])
            ->fillForm([
                'preference_type'  => 'brand',
                'preference_key'   => 'updated-brand',
                'preference_score' => 0.6,
                'metadata'         => [
                    'source'     => 'analytics',
                    'frequency'  => 'medium',
                    'confidence' => '0.80',
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('user_preferences', [
            'id'               => $preference->id,
            'preference_key'   => 'updated-brand',
            'preference_score' => 0.6,
        ]);
    }

    public function test_admin_can_view_user_preference_details(): void
    {
        // Persist a preference record so the view page has real content to render.
        $preference = UserPreference::factory()->create([
            'preference_type' => 'category',
            'preference_key'  => 'viewable-key',
            'preference_score'=> 0.42,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ViewUserPreference::class, ['record' => $preference->getKey()])
            ->assertFormSet([
                'preference_type'  => 'category',
                'preference_key'   => 'viewable-key',
                'preference_score' => 0.42,
            ]);
    }

    public function test_score_range_filter_limits_table_results(): void
    {
        // Create deterministic scores so the range filter can include and exclude records reliably.
        $highScore = UserPreference::factory()->create(['preference_score' => 0.9]);
        $midScore = UserPreference::factory()->create(['preference_score' => 0.5]);
        $lowScore = UserPreference::factory()->create(['preference_score' => 0.1]);

        Livewire::actingAs($this->adminUser)
            ->test(ListUserPreferences::class)
            ->call('loadTable')
            ->filterTable('score_range', [
                'min_score' => '0.4',
                'max_score' => '0.95',
            ])
            ->assertCanSeeTableRecords([$highScore, $midScore])
            ->assertCanNotSeeTableRecords([$lowScore]);
    }

    public function test_reset_preference_action_sets_score_to_zero(): void
    {
        // Persist a high score so we can confirm the row-level action performs the reset logic.
        $preference = UserPreference::factory()->create([
            'preference_score' => 0.77,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ListUserPreferences::class)
            ->call('loadTable')
            ->callTableAction('reset_preference', $preference)
            ->assertNotified();

        $this->assertDatabaseHas('user_preferences', [
            'id'               => $preference->id,
            'preference_score' => 0.0,
        ]);
    }

    public function test_bulk_reset_preferences_sets_all_scores_to_zero(): void
    {
        // Prepare several preferences so the bulk action exercises the collection handler.
        $preferences = UserPreference::factory()->count(3)->create([
            'preference_score' => 0.6,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ListUserPreferences::class)
            ->call('loadTable')
            ->callTableBulkAction('reset_preferences', $preferences)
            ->assertNotified();

        foreach ($preferences as $preference) {
            $this->assertDatabaseHas('user_preferences', [
                'id'               => $preference->id,
                'preference_score' => 0.0,
            ]);
        }
    }
}
