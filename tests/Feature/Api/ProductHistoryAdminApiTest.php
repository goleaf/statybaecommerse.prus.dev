<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Jobs\ProcessExportJob;
use App\Models\Product;
use App\Models\ProductHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class ProductHistoryAdminApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('authorization.testing.skip_checks', false);

        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'viewer', 'guard_name' => 'web']);
    }

    public function test_viewer_cannot_list_product_histories(): void
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');
        $product = Product::factory()->create();
        ProductHistory::factory()->for($product)->create();

        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson(route('api.admin.product-histories.index', [
            'product' => $product->getKey(),
        ]));

        $response->assertForbidden();
    }

    public function test_admin_can_list_histories_with_filters(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $product = Product::factory()->create();
        $matching = ProductHistory::factory()->for($product)->create(['action' => 'updated']);
        ProductHistory::factory()->for($product)->create(['action' => 'created']);
        $otherProduct = Product::factory()->create();
        ProductHistory::factory()->for($otherProduct)->create(['action' => 'updated']);

        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson(route('api.admin.product-histories.index', [
            'product' => $product->getKey(),
            'action' => 'updated',
            'per_page' => 10,
        ]));

        $response->assertOk();
        $this->assertSame($matching->getKey(), $response->json('data.histories.0.id'));
        $this->assertSame('updated', $response->json('meta.query.filters.action'));
    }

    public function test_export_dispatches_queue_and_streams_payload(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $user->assignRole('admin');
        $product = Product::factory()->create();
        ProductHistory::factory()->for($product)->count(2)->create();

        Sanctum::actingAs($user, ['*']);

        $response = $this->post(route('api.admin.product-histories.export', [
            'product' => $product->getKey(),
        ]), [
            'columns' => ['occurred_at', 'action'],
        ]);

        $response->assertOk();
        $content = $response->streamedContent();
        $this->assertStringContainsString('"event":"queued"', $content);

        Queue::assertPushed(ProcessExportJob::class, 1);
    }

    public function test_viewer_cannot_export_histories(): void
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');
        $product = Product::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $response = $this->post(route('api.admin.product-histories.export', [
            'product' => $product->getKey(),
        ]));

        $response->assertForbidden();
    }
}
