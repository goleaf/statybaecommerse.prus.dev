<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class OrderCreatedAtIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_completed_order_range_query_uses_created_at_index(): void
    {
        $start = Carbon::now()->subMonths(2)->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        Order::factory()->count(5)->completed()->create([
            'created_at' => $start->copy()->addDays(3),
            'updated_at' => $start->copy()->addDays(3),
        ]);

        Order::factory()->count(3)->completed()->create([
            'created_at' => $end->copy()->subDays(5),
            'updated_at' => $end->copy()->subDays(5),
        ]);

        Order::factory()->count(2)->completed()->create([
            'created_at' => $start->copy()->subMonths(6),
            'updated_at' => $start->copy()->subMonths(6),
        ]);

        $query = Order::query()
            ->completed()
            // Apply the reusable scope instead of a raw predicate to guarantee index alignment.
            ->createdBetween($start, $end)
            ->selectRaw('SUM(total) AS total');

        $plan = DB::select(
            $this->explain($query->toSql()),
            $query->getBindings()
        );

        $this->assertOrdersPlanUsesCreatedAtIndex($plan);
    }

    /**
     * @param array<int, object> $plan
     */
    private function assertOrdersPlanUsesCreatedAtIndex(array $plan): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $details = collect($plan)
                ->map(static fn (object $row): string => (string) ($row->detail ?? ''))
                ->filter();

            $this->assertTrue(
                $details->contains(static fn (string $detail): bool => str_contains($detail, 'orders_created_at_index')),
                'Expected SQLite query plan to reference orders_created_at_index. Details: ' . implode(' | ', $details->all())
            );

            return;
        }

        foreach ($plan as $row) {
            $key = $row->key ?? $row->Key ?? null;
            $this->assertSame('orders_created_at_index', $key, 'Expected MySQL query plan to use orders_created_at_index.');
        }
    }

    private function explain(string $sql): string
    {
        return match (DB::getDriverName()) {
            'sqlite' => 'EXPLAIN QUERY PLAN ' . $sql,
            default  => 'EXPLAIN ' . $sql,
        };
    }
}
