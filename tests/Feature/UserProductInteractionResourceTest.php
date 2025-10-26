<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\UserProductInteractionResource;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\UserProductInteraction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class UserProductInteractionResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('super_admin');
    }

    /**
     * Ensure the listing page renders existing interactions with the updated
     * event column.
     */
    public function test_admin_can_list_interactions(): void
    {
        $interaction = UserProductInteraction::factory()->create(['event' => 'view']);

        $this->actingAs($this->adminUser);

        Livewire::test(UserProductInteractionResource\Pages\ListUserProductInteractions::class)
            ->assertCanSeeTableRecords([$interaction]);
    }

    /**
     * Verify the create page accepts event, occurred_at, and meta values when
     * storing a new interaction.
     */
    public function test_admin_can_create_interaction(): void
    {
        $this->actingAs($this->adminUser);

        $user = User::factory()->create();
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        $occurredAt = now()->addHour()->format('Y-m-d H:i');

        Livewire::test(UserProductInteractionResource\Pages\CreateUserProductInteraction::class)
            ->fillForm([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'product_variant_id' => $variant->id,
                'event' => 'click',
                'occurred_at' => $occurredAt,
                'meta' => [
                    'rating' => '4.5',
                    'count' => '2',
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('user_product_interactions', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'event' => 'click',
        ]);
    }

    /**
     * Confirm the table filter targets the event column.
     */
    public function test_event_filter_limits_table_results(): void
    {
        $this->actingAs($this->adminUser);

        $viewInteraction = UserProductInteraction::factory()->create(['event' => 'view']);
        $clickInteraction = UserProductInteraction::factory()->create(['event' => 'click']);

        Livewire::test(UserProductInteractionResource\Pages\ListUserProductInteractions::class)
            ->filterTable('event', 'view')
            ->assertCanSeeTableRecords([$viewInteraction])
            ->assertCanNotSeeTableRecords([$clickInteraction]);
    }
}
