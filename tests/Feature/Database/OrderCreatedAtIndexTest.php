<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrderCreatedAtIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_created_between_scope_uses_created_at_index(): void
    {
        Order::factory()->count(3)->create(['created_at' => now()->subDays(2)]);

        $query = Order::query()->createdBetween(now()->subDays(3), now());

        $planDetails = $this->explainPlan($query->toSql(), $query->getBindings());

        $this->assertStringContainsString('orders_created_at_index', $planDetails);
    }

    public function test_created_since_scope_uses_created_at_index(): void
    {
        Order::factory()->count(3)->create(['created_at' => now()->subDays(2)]);

        $query = Order::query()->createdSince(now()->subDays(3));

        $planDetails = $this->explainPlan($query->toSql(), $query->getBindings());

        $this->assertStringContainsString('orders_created_at_index', $planDetails);
    }

    private function explainPlan(string $sql, array $bindings): string
    {
        $plan = DB::select('EXPLAIN QUERY PLAN '.$sql, $bindings);

        return collect($plan)
            ->map(function ($row) {
                $values = array_map('strval', (array) $row);

                return strtolower(implode(' ', $values));
            })
            ->implode(' ');
    }
}
