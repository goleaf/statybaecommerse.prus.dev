<?php

declare(strict_types=1);

namespace Tests\Filament;

use App\Filament\Resources\UserProductInteractions\Pages\CreateUserProductInteraction;
use App\Filament\Resources\UserProductInteractions\Pages\EditUserProductInteraction;
use App\Filament\Resources\UserProductInteractions\Pages\ListUserProductInteractions;
use App\Filament\Resources\UserProductInteractions\UserProductInteractionResource;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\UserProductInteraction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class UserProductInteractionsV4ResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an authenticated administrator so Filament pages have context.
        $this->adminUser = User::factory()->create();

        // Authenticate the user for the default guard used by the admin panel.
        $this->actingAs($this->adminUser);
    }

    public function test_resource_slug_is_namespaced_to_avoid_collisions(): void
    {
        // Assert that the v4 resource keeps a distinct slug from the legacy resource.
        self::assertSame('user-product-interactions-v4', UserProductInteractionResource::getSlug());
    }

    public function test_list_page_displays_existing_interactions(): void
    {
        // Seed a collection of interactions that should surface inside the table.
        $interactions = UserProductInteraction::factory()->count(3)->create();

        // Mount the Filament list page and ensure all records are visible.
        Livewire::test(ListUserProductInteractions::class)
            ->assertCanSeeTableRecords($interactions);
    }

    public function test_create_page_persists_new_interaction(): void
    {
        // Prepare related models so the form relationships can resolve options.
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->getKey(),
        ]);

        $formState = [
            // Link the interaction to concrete user and product records.
            'user_id'             => $user->getKey(),
            'product_id'          => $product->getKey(),
            'product_variant_id'  => $variant->getKey(),
            'event'               => 'add_to_cart',
            // Use an ISO timestamp so the flatpickr component hydrates correctly.
            'occurred_at'         => now()->toDateTimeString(),
            // Provide simple metadata to ensure array payloads round-trip.
            'meta'                => ['source' => 'test'],
        ];

        // Submit the create form and expect a successful validation run.
        Livewire::test(CreateUserProductInteraction::class)
            ->fillForm($formState)
            ->call('create')
            ->assertHasNoFormErrors();

        // Confirm the database now contains the freshly created interaction.
        $this->assertDatabaseHas('user_product_interactions', [
            'user_id'    => $user->getKey(),
            'product_id' => $product->getKey(),
            'event'      => 'add_to_cart',
        ]);
    }

    public function test_edit_page_updates_existing_interaction(): void
    {
        // Build a record with deterministic values for the edit assertions.
        $interaction = UserProductInteraction::factory()->create([
            'event' => 'click',
            'meta'  => ['from' => 'initial'],
        ]);

        $updatedState = [
            // Flip the event type and metadata payload to verify persistence.
            'event' => 'purchase',
            'meta'  => ['from' => 'edited'],
        ];

        // Drive the edit page workflow and assert the data is stored.
        Livewire::test(EditUserProductInteraction::class, ['record' => $interaction->getRouteKey()])
            ->fillForm($updatedState)
            ->call('save')
            ->assertHasNoFormErrors();

        // Validate that the database reflects the updated values.
        $this->assertDatabaseHas('user_product_interactions', [
            'id'    => $interaction->getKey(),
            'event' => 'purchase',
        ]);
    }
}
