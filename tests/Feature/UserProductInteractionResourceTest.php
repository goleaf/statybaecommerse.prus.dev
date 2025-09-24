<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\UserProductInteractionResource;
use App\Models\Product;
use App\Models\User;
use App\Models\UserProductInteraction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class UserProductInteractionResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private User $testUser;

    private Product $testProduct;

    private UserProductInteraction $testInteraction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['email' => 'admin@example.com']);
        $this->adminUser->assignRole('super_admin');

        $this->testUser = User::factory()->create(['email' => 'user@example.com']);
        $this->testProduct = Product::factory()->create(['name' => 'Test Product']);
        $this->testInteraction = UserProductInteraction::factory()->create([
            'user_id' => $this->testUser->id,
            'product_id' => $this->testProduct->id,
            'interaction_type' => 'view',
            'rating' => 4.5,
            'count' => 3,
        ]);
    }

    public function test_can_list_user_product_interactions(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(UserProductInteractionResource\Pages\ListUserProductInteractions::class)
            ->assertCanSeeTableRecords([$this->testInteraction]);
    }

    public function test_can_view_user_product_interaction(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(UserProductInteractionResource\Pages\ViewUserProductInteraction::class, [
            'record' => $this->testInteraction->getRouteKey(),
        ])
            ->assertCanSeeTableRecords([$this->testInteraction]);
    }

    public function test_can_create_user_product_interaction(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(UserProductInteractionResource\Pages\CreateUserProductInteraction::class)
            ->fillForm([
                'user_id' => $this->testUser->id,
                'product_id' => $this->testProduct->id,
                'interaction_type' => 'click',
                'rating' => 3.5,
                'count' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('user_product_interactions', [
            'user_id' => $this->testUser->id,
            'product_id' => $this->testProduct->id,
            'interaction_type' => 'click',
            'rating' => 3.5,
            'count' => 1,
        ]);
    }

    public function test_can_edit_user_product_interaction(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(UserProductInteractionResource\Pages\EditUserProductInteraction::class, [
            'record' => $this->testInteraction->getRouteKey(),
        ])
            ->fillForm([
                'interaction_type' => 'purchase',
                'rating' => 5.0,
                'count' => 5,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->testInteraction->refresh();
        $this->assertEquals('purchase', $this->testInteraction->interaction_type);
        $this->assertEquals(5.0, $this->testInteraction->rating);
        $this->assertEquals(5, $this->testInteraction->count);
    }

    public function test_can_delete_user_product_interaction(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(UserProductInteractionResource\Pages\EditUserProductInteraction::class, [
            'record' => $this->testInteraction->getRouteKey(),
        ])
            ->callAction('delete')
            ->assertHasNoActionErrors();

        $this->assertDatabaseMissing('user_product_interactions', [
            'id' => $this->testInteraction->id,
        ]);
    }

    public function test_can_increment_interaction(): void
    {
        $this->actingAs($this->adminUser);
        $originalCount = $this->testInteraction->count;

        Livewire::test(UserProductInteractionResource\Pages\ListUserProductInteractions::class)
            ->callTableAction('increment', $this->testInteraction)
            ->assertHasNoTableActionErrors();

        $this->testInteraction->refresh();
        $this->assertEquals($originalCount + 1, $this->testInteraction->count);
    }

    public function test_can_reset_count(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(UserProductInteractionResource\Pages\ListUserProductInteractions::class)
            ->callTableAction('reset_count', $this->testInteraction)
            ->assertHasNoTableActionErrors();

        $this->testInteraction->refresh();
        $this->assertEquals(1, $this->testInteraction->count);
    }

    public function test_can_duplicate_interaction(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(UserProductInteractionResource\Pages\ListUserProductInteractions::class)
            ->callTableAction('duplicate', $this->testInteraction)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('user_product_interactions', [
            'user_id' => $this->testInteraction->user_id,
            'product_id' => $this->testInteraction->product_id,
            'interaction_type' => $this->testInteraction->interaction_type,
            'count' => 1,
        ]);
    }

    public function test_can_bulk_increment_interactions(): void
    {
        $this->actingAs($this->adminUser);
        $interaction2 = UserProductInteraction::factory()->create([
            'user_id' => $this->testUser->id,
            'product_id' => $this->testProduct->id,
            'count' => 2,
        ]);

        Livewire::test(UserProductInteractionResource\Pages\ListUserProductInteractions::class)
            ->callTableBulkAction('increment_all', [$this->testInteraction, $interaction2])
            ->assertHasNoTableBulkActionErrors();

        $this->testInteraction->refresh();
        $interaction2->refresh();
        $this->assertEquals(4, $this->testInteraction->count);
        $this->assertEquals(3, $interaction2->count);
    }

    public function test_can_bulk_reset_counts(): void
    {
        $this->actingAs($this->adminUser);
        $interaction2 = UserProductInteraction::factory()->create([
            'user_id' => $this->testUser->id,
            'product_id' => $this->testProduct->id,
            'count' => 5,
        ]);

        Livewire::test(UserProductInteractionResource\Pages\ListUserProductInteractions::class)
            ->callTableBulkAction('reset_all_counts', [$this->testInteraction, $interaction2])
            ->assertHasNoTableBulkActionErrors();

        $this->testInteraction->refresh();
        $interaction2->refresh();
        $this->assertEquals(1, $this->testInteraction->count);
        $this->assertEquals(1, $interaction2->count);
    }

    public function test_can_bulk_mark_anonymous(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(UserProductInteractionResource\Pages\ListUserProductInteractions::class)
            ->callTableBulkAction('mark_anonymous', [$this->testInteraction])
            ->assertHasNoTableBulkActionErrors();

        $this->testInteraction->refresh();
        $this->assertTrue($this->testInteraction->is_anonymous);
    }

    public function test_can_bulk_mark_non_anonymous(): void
    {
        $this->actingAs($this->adminUser);
        $this->testInteraction->update(['is_anonymous' => true]);

        Livewire::test(UserProductInteractionResource\Pages\ListUserProductInteractions::class)
            ->callTableBulkAction('mark_non_anonymous', [$this->testInteraction])
            ->assertHasNoTableBulkActionErrors();

        $this->testInteraction->refresh();
        $this->assertFalse($this->testInteraction->is_anonymous);
    }

    public function test_can_filter_by_user(): void
    {
        $this->actingAs($this->adminUser);
        $otherUser = User::factory()->create();
        $otherInteraction = UserProductInteraction::factory()->create([
            'user_id' => $otherUser->id,
            'product_id' => $this->testProduct->id,
        ]);

        Livewire::test(UserProductInteractionResource\Pages\ListUserProductInteractions::class)
            ->filterTable('user_id', $this->testUser->id)
            ->assertCanSeeTableRecords([$this->testInteraction])
            ->assertCanNotSeeTableRecords([$otherInteraction]);
    }

    public function test_can_filter_by_product(): void
    {
        $this->actingAs($this->adminUser);
        $otherProduct = Product::factory()->create();
        $otherInteraction = UserProductInteraction::factory()->create([
            'user_id' => $this->testUser->id,
            'product_id' => $otherProduct->id,
        ]);

        Livewire::test(UserProductInteractionResource\Pages\ListUserProductInteractions::class)
            ->filterTable('product_id', $this->testProduct->id)
            ->assertCanSeeTableRecords([$this->testInteraction])
            ->assertCanNotSeeTableRecords([$otherInteraction]);
    }

    public function test_can_filter_by_interaction_type(): void
    {
        $this->actingAs($this->adminUser);
        $otherInteraction = UserProductInteraction::factory()->create([
            'user_id' => $this->testUser->id,
            'product_id' => $this->testProduct->id,
            'interaction_type' => 'click',
        ]);

        Livewire::test(UserProductInteractionResource\Pages\ListUserProductInteractions::class)
            ->filterTable('interaction_type', 'view')
            ->assertCanSeeTableRecords([$this->testInteraction])
            ->assertCanNotSeeTableRecords([$otherInteraction]);
    }

    public function test_can_filter_by_high_rating(): void
    {
        $this->actingAs($this->adminUser);
        $lowRatingInteraction = UserProductInteraction::factory()->create([
            'user_id' => $this->testUser->id,
            'product_id' => $this->testProduct->id,
            'rating' => 2.0,
        ]);

        Livewire::test(UserProductInteractionResource\Pages\ListUserProductInteractions::class)
            ->filterTable('high_rating')
            ->assertCanSeeTableRecords([$this->testInteraction])
            ->assertCanNotSeeTableRecords([$lowRatingInteraction]);
    }

    public function test_can_filter_by_recent_interactions(): void
    {
        $this->actingAs($this->adminUser);
        $oldInteraction = UserProductInteraction::factory()->create([
            'user_id' => $this->testUser->id,
            'product_id' => $this->testProduct->id,
            'last_interaction' => now()->subDays(10),
        ]);

        Livewire::test(UserProductInteractionResource\Pages\ListUserProductInteractions::class)
            ->filterTable('recent_interactions')
            ->assertCanSeeTableRecords([$this->testInteraction])
            ->assertCanNotSeeTableRecords([$oldInteraction]);
    }

    public function test_can_sort_by_last_interaction(): void
    {
        $this->actingAs($this->adminUser);
        $newerInteraction = UserProductInteraction::factory()->create([
            'user_id' => $this->testUser->id,
            'product_id' => $this->testProduct->id,
            'last_interaction' => now()->addHour(),
        ]);

        Livewire::test(UserProductInteractionResource\Pages\ListUserProductInteractions::class)
            ->sortTable('last_interaction', 'desc')
            ->assertCanSeeTableRecords([$newerInteraction, $this->testInteraction], inOrder: true);
    }

    public function test_can_search_by_user_name(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(UserProductInteractionResource\Pages\ListUserProductInteractions::class)
            ->searchTable($this->testUser->name)
            ->assertCanSeeTableRecords([$this->testInteraction]);
    }

    public function test_can_search_by_product_name(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(UserProductInteractionResource\Pages\ListUserProductInteractions::class)
            ->searchTable($this->testProduct->name)
            ->assertCanSeeTableRecords([$this->testInteraction]);
    }

    public function test_can_toggle_columns(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(UserProductInteractionResource\Pages\ListUserProductInteractions::class)
            ->assertCanSeeTableRecords([$this->testInteraction])
            ->assertTableColumnExists('id')
            ->assertTableColumnExists('rating')
            ->assertTableColumnExists('count');
    }

    public function test_can_export_selected(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(UserProductInteractionResource\Pages\ListUserProductInteractions::class)
            ->callTableBulkAction('export_selected', [$this->testInteraction])
            ->assertHasNoTableBulkActionErrors();
    }

    public function test_requires_authentication(): void
    {
        $this->get(UserProductInteractionResource::getUrl('index'))
            ->assertRedirect('/login');
    }

    public function test_requires_permission(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(UserProductInteractionResource::getUrl('index'))
            ->assertForbidden();
    }
}
