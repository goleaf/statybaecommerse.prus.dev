<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\UserProductInteractions\Pages\CreateUserProductInteraction;
use App\Filament\Resources\UserProductInteractions\Pages\EditUserProductInteraction;
use App\Filament\Resources\UserProductInteractions\Pages\ListUserProductInteractions;
use App\Filament\Resources\UserProductInteractions\UserProductInteractionResource;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\UserProductInteraction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Feature coverage for the Filament v4 user product interaction resource.
 */
final class UserProductInteractionsResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Resolve the admin panel context before booting Livewire components.
        $this->resolveAdminPanel();

        // Lock application locale to English so factories return deterministic strings.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Authenticate as an administrator to bypass Filament authorization gates in tests.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_resource_uses_v4_slug(): void
    {
        // Assert the resource exposes the custom slug to avoid route conflicts with the legacy page.
        self::assertSame('user-product-interactions-v4', UserProductInteractionResource::getSlug());
    }

    public function test_list_page_displays_interactions(): void
    {
        // Seed a visible interaction so the listing table has content to render.
        $interaction = UserProductInteraction::factory()->create([
            'event' => 'review',
            'meta'  => ['rating' => 4.5],
        ]);

        // Ensure the Livewire table hydrates and exposes the seeded record.
        Livewire::test(ListUserProductInteractions::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$interaction]);
    }

    public function test_table_registers_expected_columns(): void
    {
        // Confirm the table definition registers the critical columns introduced during the v4 migration.
        Livewire::test(ListUserProductInteractions::class)
            ->call('loadTable')
            ->assertTableColumnExists('user.name')
            ->assertTableColumnExists('product.name')
            ->assertTableColumnExists('event')
            ->assertTableColumnExists('occurred_at')
            ->assertTableColumnExists('rating')
            ->assertTableColumnExists('count');
    }

    public function test_admin_can_create_interaction(): void
    {
        // Prepare related entities so relationship selects have options.
        $user = User::factory()->create(['name' => 'Analytics User']);
        $product = Product::factory()->create(['name' => 'Tracked Product']);
        $variant = ProductVariant::factory()->for($product, 'product')->create([
            'name' => 'Tracked Variant',
        ]);

        $occurredAt = Carbon::now()->subDay();

        // Submit the create form using the streamlined Filament v4 schema.
        Livewire::test(CreateUserProductInteraction::class)
            ->fillForm([
                'user_id'            => $user->getKey(),
                'product_id'         => $product->getKey(),
                'product_variant_id' => $variant->getKey(),
                'event'              => 'wishlist',
                'occurred_at'        => $occurredAt->format('Y-m-d H:i:s'),
                'meta'               => [
                    'notes'  => 'Saved for later',
                    'rating' => '4.0',
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        // Verify the interaction persisted with the provided payload and relationships.
        $this->assertDatabaseHas('user_product_interactions', [
            'user_id'    => $user->getKey(),
            'product_id' => $product->getKey(),
            'event'      => 'wishlist',
        ]);
    }

    public function test_admin_can_update_interaction(): void
    {
        // Seed an interaction to update via the edit record page.
        $interaction = UserProductInteraction::factory()->create([
            'event'       => 'view',
            'meta'        => ['rating' => 2, 'notes' => 'Initial note'],
            'occurred_at' => Carbon::now()->subDays(2),
        ]);

        $newTimestamp = Carbon::now()->subHour();

        // Update the interaction event and supporting metadata through the Filament edit form.
        Livewire::test(EditUserProductInteraction::class, ['record' => $interaction->getRouteKey()])
            ->fillForm([
                'user_id'    => $interaction->user_id,
                'product_id' => $interaction->product_id,
                'event'      => 'purchase',
                'occurred_at'=> $newTimestamp->format('Y-m-d H:i:s'),
                'meta'       => [
                    'notes'  => 'Upgraded to purchase',
                    'rating' => '5',
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        // Refresh the model and assert the updated state reflects the new payload.
        $interaction->refresh();

        self::assertSame('purchase', $interaction->event);
        self::assertSame('Upgraded to purchase', $interaction->meta['notes'] ?? null);
        self::assertSame(5.0, $interaction->meta['rating'] ?? null);
        // Compare the timestamps using the formatted string to avoid microsecond mismatches from database casting.
        self::assertSame(
            $newTimestamp->format('Y-m-d H:i:s'),
            $interaction->occurred_at?->format('Y-m-d H:i:s'),
        );
    }

    public function test_admin_can_delete_interaction(): void
    {
        // Create an interaction that will be removed via the edit page delete action.
        $interaction = UserProductInteraction::factory()->create();

        // Trigger the delete action exposed on the edit page and ensure it succeeds.
        Livewire::test(EditUserProductInteraction::class, ['record' => $interaction->getRouteKey()])
            ->callAction('delete')
            ->assertHasNoActionErrors();

        // Confirm the interaction record has been removed from persistence.
        $this->assertDatabaseMissing('user_product_interactions', [
            'id' => $interaction->id,
        ]);
    }
}
