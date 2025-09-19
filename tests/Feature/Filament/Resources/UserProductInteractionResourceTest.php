<?php declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\UserProductInteractionResource;
use App\Models\Product;
use App\Models\User;
use App\Models\UserProductInteraction;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * UserProductInteractionResourceTest
 *
 * Comprehensive test suite for UserProductInteractionResource covering all CRUD operations,
 * filters, actions, and relationships in the Filament admin panel.
 */
final class UserProductInteractionResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        // Create admin user with proper permissions
        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->adminUser->assignRole('administrator');
    }

    public function test_can_list_user_product_interactions(): void
    {
        // Arrange
        $user = User::factory()->create();
        $product = Product::factory()->create();

        UserProductInteraction::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'interaction_type' => 'view',
            'count' => 5,
        ]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListRecords::class, ['resource' => UserProductInteractionResource::class])
            ->assertCanSeeTableRecords([UserProductInteraction::first()])
            ->assertCanSeeTableColumns([
                'user.name',
                'product.name',
                'interaction_type',
                'rating',
                'count',
                'first_interaction',
                'last_interaction',
            ]);
    }

    public function test_can_create_user_product_interaction(): void
    {
        // Arrange
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $data = [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'interaction_type' => 'view',
            'rating' => 4.5,
            'count' => 3,
            'first_interaction' => now()->subDays(7),
            'last_interaction' => now(),
        ];

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(CreateRecord::class, ['resource' => UserProductInteractionResource::class])
            ->fillForm($data)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('user_product_interactions', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'interaction_type' => 'view',
            'rating' => 4.5,
            'count' => 3,
        ]);
    }

    public function test_can_edit_user_product_interaction(): void
    {
        // Arrange
        $interaction = UserProductInteraction::factory()->create();
        $newUser = User::factory()->create();
        $newProduct = Product::factory()->create();

        $data = [
            'user_id' => $newUser->id,
            'product_id' => $newProduct->id,
            'interaction_type' => 'purchase',
            'rating' => 5.0,
            'count' => 1,
        ];

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(EditRecord::class, ['resource' => UserProductInteractionResource::class, 'record' => $interaction->getRouteKey()])
            ->fillForm($data)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('user_product_interactions', [
            'id' => $interaction->id,
            'user_id' => $newUser->id,
            'product_id' => $newProduct->id,
            'interaction_type' => 'purchase',
            'rating' => 5.0,
            'count' => 1,
        ]);
    }

    public function test_can_view_user_product_interaction(): void
    {
        // Arrange
        $interaction = UserProductInteraction::factory()->create();

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ViewRecord::class, ['resource' => UserProductInteractionResource::class, 'record' => $interaction->getRouteKey()])
            ->assertCanSeeFormData([
                'user_id' => $interaction->user_id,
                'product_id' => $interaction->product_id,
                'interaction_type' => $interaction->interaction_type,
                'rating' => $interaction->rating,
                'count' => $interaction->count,
            ]);
    }

    public function test_can_filter_by_user(): void
    {
        // Arrange
        $user1 = User::factory()->create(['name' => 'John Doe']);
        $user2 = User::factory()->create(['name' => 'Jane Smith']);
        $product = Product::factory()->create();

        UserProductInteraction::factory()->create([
            'user_id' => $user1->id,
            'product_id' => $product->id,
        ]);

        UserProductInteraction::factory()->create([
            'user_id' => $user2->id,
            'product_id' => $product->id,
        ]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListRecords::class, ['resource' => UserProductInteractionResource::class])
            ->filterTable('user_id', $user1->id)
            ->assertCanSeeTableRecords([UserProductInteraction::where('user_id', $user1->id)->first()])
            ->assertCanNotSeeTableRecords([UserProductInteraction::where('user_id', $user2->id)->first()]);
    }

    public function test_can_filter_by_product(): void
    {
        // Arrange
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['name' => 'Product A']);
        $product2 = Product::factory()->create(['name' => 'Product B']);

        UserProductInteraction::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product1->id,
        ]);

        UserProductInteraction::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product2->id,
        ]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListRecords::class, ['resource' => UserProductInteractionResource::class])
            ->filterTable('product_id', $product1->id)
            ->assertCanSeeTableRecords([UserProductInteraction::where('product_id', $product1->id)->first()])
            ->assertCanNotSeeTableRecords([UserProductInteraction::where('product_id', $product2->id)->first()]);
    }

    public function test_can_filter_by_interaction_type(): void
    {
        // Arrange
        $user = User::factory()->create();
        $product = Product::factory()->create();

        UserProductInteraction::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'interaction_type' => 'view',
        ]);

        UserProductInteraction::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'interaction_type' => 'purchase',
        ]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListRecords::class, ['resource' => UserProductInteractionResource::class])
            ->filterTable('interaction_type', 'view')
            ->assertCanSeeTableRecords([UserProductInteraction::where('interaction_type', 'view')->first()])
            ->assertCanNotSeeTableRecords([UserProductInteraction::where('interaction_type', 'purchase')->first()]);
    }

    public function test_can_filter_by_has_rating(): void
    {
        // Arrange
        $user = User::factory()->create();
        $product = Product::factory()->create();

        UserProductInteraction::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 4.5,
        ]);

        UserProductInteraction::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => null,
        ]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListRecords::class, ['resource' => UserProductInteractionResource::class])
            ->filterTable('has_rating')
            ->assertCanSeeTableRecords([UserProductInteraction::whereNotNull('rating')->first()])
            ->assertCanNotSeeTableRecords([UserProductInteraction::whereNull('rating')->first()]);
    }

    public function test_can_filter_by_high_rating(): void
    {
        // Arrange
        $user = User::factory()->create();
        $product = Product::factory()->create();

        UserProductInteraction::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 4.5,
        ]);

        UserProductInteraction::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 3.0,
        ]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListRecords::class, ['resource' => UserProductInteractionResource::class])
            ->filterTable('high_rating')
            ->assertCanSeeTableRecords([UserProductInteraction::where('rating', '>=', 4.0)->first()])
            ->assertCanNotSeeTableRecords([UserProductInteraction::where('rating', '<', 4.0)->first()]);
    }

    public function test_can_filter_by_recent_interactions(): void
    {
        // Arrange
        $user = User::factory()->create();
        $product = Product::factory()->create();

        UserProductInteraction::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'last_interaction' => now()->subDays(3),
        ]);

        UserProductInteraction::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'last_interaction' => now()->subDays(10),
        ]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListRecords::class, ['resource' => UserProductInteractionResource::class])
            ->filterTable('recent_interactions')
            ->assertCanSeeTableRecords([UserProductInteraction::where('last_interaction', '>=', now()->subDays(7))->first()])
            ->assertCanNotSeeTableRecords([UserProductInteraction::where('last_interaction', '<', now()->subDays(7))->first()]);
    }

    public function test_can_increment_interaction(): void
    {
        // Arrange
        $interaction = UserProductInteraction::factory()->create([
            'count' => 5,
            'rating' => 3.0,
        ]);

        $originalCount = $interaction->count;
        $originalRating = $interaction->rating;

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListRecords::class, ['resource' => UserProductInteractionResource::class])
            ->callTableAction('increment', $interaction)
            ->assertHasNoTableActionErrors();

        $interaction->refresh();
        $this->assertEquals($originalCount + 1, $interaction->count);
        $this->assertEquals($originalRating, $interaction->rating);
    }

    public function test_can_bulk_increment_interactions(): void
    {
        // Arrange
        $interactions = UserProductInteraction::factory()->count(3)->create();

        $originalCounts = $interactions->pluck('count')->toArray();

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListRecords::class, ['resource' => UserProductInteractionResource::class])
            ->callTableBulkAction('increment_all', $interactions)
            ->assertHasNoTableBulkActionErrors();

        foreach ($interactions as $index => $interaction) {
            $interaction->refresh();
            $this->assertEquals($originalCounts[$index] + 1, $interaction->count);
        }
    }

    public function test_can_delete_user_product_interaction(): void
    {
        // Arrange
        $interaction = UserProductInteraction::factory()->create();

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(EditRecord::class, ['resource' => UserProductInteractionResource::class, 'record' => $interaction->getRouteKey()])
            ->call('delete')
            ->assertHasNoFormErrors();

        $this->assertDatabaseMissing('user_product_interactions', [
            'id' => $interaction->id,
        ]);
    }

    public function test_can_bulk_delete_user_product_interactions(): void
    {
        // Arrange
        $interactions = UserProductInteraction::factory()->count(3)->create();
        $interactionIds = $interactions->pluck('id')->toArray();

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListRecords::class, ['resource' => UserProductInteractionResource::class])
            ->callTableBulkAction('delete', $interactions)
            ->assertHasNoTableBulkActionErrors();

        foreach ($interactionIds as $id) {
            $this->assertDatabaseMissing('user_product_interactions', [
                'id' => $id,
            ]);
        }
    }

    public function test_validation_requires_user_and_product(): void
    {
        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(CreateRecord::class, ['resource' => UserProductInteractionResource::class])
            ->fillForm([
                'interaction_type' => 'view',
            ])
            ->call('create')
            ->assertHasFormErrors(['user_id', 'product_id']);
    }

    public function test_validation_requires_interaction_type(): void
    {
        // Arrange
        $user = User::factory()->create();
        $product = Product::factory()->create();

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(CreateRecord::class, ['resource' => UserProductInteractionResource::class])
            ->fillForm([
                'user_id' => $user->id,
                'product_id' => $product->id,
            ])
            ->call('create')
            ->assertHasFormErrors(['interaction_type']);
    }

    public function test_rating_must_be_between_0_and_5(): void
    {
        // Arrange
        $user = User::factory()->create();
        $product = Product::factory()->create();

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(CreateRecord::class, ['resource' => UserProductInteractionResource::class])
            ->fillForm([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'interaction_type' => 'review',
                'rating' => 6.0,
            ])
            ->call('create')
            ->assertHasFormErrors(['rating']);
    }

    public function test_count_must_be_positive(): void
    {
        // Arrange
        $user = User::factory()->create();
        $product = Product::factory()->create();

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(CreateRecord::class, ['resource' => UserProductInteractionResource::class])
            ->fillForm([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'interaction_type' => 'view',
                'count' => -1,
            ])
            ->call('create')
            ->assertHasFormErrors(['count']);
    }

    public function test_table_is_sortable_by_default_columns(): void
    {
        // Arrange
        UserProductInteraction::factory()->count(5)->create();

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListRecords::class, ['resource' => UserProductInteractionResource::class])
            ->sortTable('user.name')
            ->sortTable('product.name')
            ->sortTable('interaction_type')
            ->sortTable('rating')
            ->sortTable('count')
            ->sortTable('first_interaction')
            ->sortTable('last_interaction')
            ->assertHasNoTableSortErrors();
    }

    public function test_table_columns_are_toggleable(): void
    {
        // Arrange
        UserProductInteraction::factory()->create();

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListRecords::class, ['resource' => UserProductInteractionResource::class])
            ->assertCanToggleTableColumn('user.name')
            ->assertCanToggleTableColumn('product.name')
            ->assertCanToggleTableColumn('rating')
            ->assertCanToggleTableColumn('count')
            ->assertCanToggleTableColumn('first_interaction')
            ->assertCanToggleTableColumn('last_interaction');
    }

    public function test_table_polls_for_updates(): void
    {
        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListRecords::class, ['resource' => UserProductInteractionResource::class])
            ->assertPropertyWired('poll');
    }
}
