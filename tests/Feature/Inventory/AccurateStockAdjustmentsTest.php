<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Models\StockMovement;
use App\Models\User;
use App\Models\VariantInventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\TestCase;

final class AccurateStockAdjustmentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure migrations run for the sqlite connection so transactional tests have full schema coverage.
        $this->artisan('migrate', ['--database' => 'sqlite']);
    }

    public function test_concurrent_decrements_do_not_oversell(): void
    {
        // Prepare a stock record with a small quantity to simulate competing decrements.
        $inventory = VariantInventory::factory()->create([
            'stock'    => 5,
            'reserved' => 0,
        ]);

        $firstCorrelation = Str::uuid()->toString();
        $secondCorrelation = Str::uuid()->toString();

        // First removal succeeds because sufficient stock exists.
        $firstAttempt = $inventory->removeStock(3, 'sale', null, $firstCorrelation);
        // Simulate a concurrent request by reloading a fresh instance before issuing the second attempt.
        $secondAttempt = VariantInventory::withoutGlobalScopes()
            ->findOrFail($inventory->getKey())
            ->removeStock(3, 'sale', null, $secondCorrelation);

        $this->assertTrue($firstAttempt, 'Initial decrement should succeed.');
        $this->assertFalse($secondAttempt, 'Second decrement should be rejected to avoid oversell.');
        $this->assertSame(2, $inventory->fresh()->stock, 'Stock count should not fall below zero.');
    }

    public function test_negative_stock_is_prevented(): void
    {
        // Create a stock row with limited quantity to validate guard clauses.
        $inventory = VariantInventory::factory()->create([
            'stock'    => 2,
            'reserved' => 0,
        ]);

        $correlationId = Str::uuid()->toString();

        $result = $inventory->removeStock(5, 'damage', null, $correlationId);

        $this->assertFalse($result, 'Removing more stock than available must fail.');
        $this->assertSame(2, $inventory->fresh()->stock, 'Stock value should remain unchanged after a failed removal.');
    }

    public function test_audit_entry_is_written_with_metadata(): void
    {
        $user = User::factory()->create();
        $inventory = VariantInventory::factory()->create([
            'stock'    => 1,
            'reserved' => 0,
        ]);

        $correlationId = Str::uuid()->toString();
        $notes = 'Restock after supplier delivery.';

        $result = $inventory->addStock(4, 'restock', $user->getKey(), $correlationId, 'PO-100', $notes);

        $this->assertTrue($result, 'Stock increment should succeed.');

        $movement = StockMovement::query()->where('correlation_id', $correlationId)->first();

        $this->assertNotNull($movement, 'Audit entry should be persisted.');
        $this->assertSame(4, $movement->quantity, 'Audit quantity should match the adjustment.');
        $this->assertSame('in', $movement->type, 'Inbound adjustments should be tagged as "in".');
        $this->assertSame('restock', $movement->reason, 'Reason should match the supplied value.');
        $this->assertSame($user->getKey(), $movement->user_id, 'Actor id should be captured.');
        $this->assertSame('PO-100', $movement->reference, 'Reference metadata should be preserved.');
        $this->assertSame($notes, $movement->notes, 'Notes should be written alongside the audit row.');
    }
}
