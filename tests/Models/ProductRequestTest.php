<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\Product;
use App\Models\ProductRequest;
use App\Models\Scopes\StatusScope;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class ProductRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_configuration_matches_contract(): void
    {
        // Instantiate the model in isolation to inspect its configuration without touching the database.
        $model = new ProductRequest;

        // Confirm that the guarded attributes allow mass-assignment only on the expected columns.
        self::assertSame([
            'product_id',
            'user_id',
            'name',
            'email',
            'phone',
            'message',
            'requested_quantity',
            'status',
            'admin_notes',
            'responded_at',
            'responded_by',
        ], $model->getFillable());

        // Validate the most critical cast definitions, including the soft delete column.
        self::assertSame('integer', $model->getCasts()['requested_quantity']);
        self::assertSame('datetime', $model->getCasts()['responded_at']);
        self::assertSame('datetime', $model->getCasts()['deleted_at']);

        // Ensure the table property is explicitly defined to avoid accidental renaming.
        self::assertSame('product_requests', $model->getTable());
    }

    public function test_relationships_resolve_models(): void
    {
        // Prepare related product and users to exercise the belongs-to relationships.
        $product = Product::factory()->create();
        $customer = User::factory()->create();
        $responder = User::factory()->create();

        // Persist a product request that references the prepared models for verification.
        $request = ProductRequest::factory()->create([
            'product_id'   => $product->id,
            'user_id'      => $customer->id,
            'responded_by' => $responder->id,
        ]);

        // Each relationship method should return a BelongsTo instance for fluent chaining.
        self::assertInstanceOf(BelongsTo::class, $request->product());
        self::assertInstanceOf(BelongsTo::class, $request->user());
        self::assertInstanceOf(BelongsTo::class, $request->respondedBy());

        // Accessing the dynamic properties should hydrate the corresponding models.
        self::assertTrue($product->is($request->product));
        self::assertTrue($customer->is($request->user));
        self::assertTrue($responder->is($request->respondedBy));
    }

    public function test_query_scopes_filter_by_status_and_sort_name(): void
    {
        // Create deterministic records to probe each query scope and ordering helper.
        $pending = ProductRequest::factory()->create(['status' => ProductRequest::STATUS_PENDING, 'name' => 'Charlie']);
        $inProgress = ProductRequest::factory()->create(['status' => ProductRequest::STATUS_IN_PROGRESS, 'name' => 'Bravo']);
        $completed = ProductRequest::factory()->create(['status' => ProductRequest::STATUS_COMPLETED, 'name' => 'Alpha']);
        $cancelled = ProductRequest::factory()->create(['status' => ProductRequest::STATUS_CANCELLED, 'name' => 'Delta']);

        // Each status-specific scope should isolate its respective record.
        self::assertSame([$pending->getKey()], ProductRequest::query()->pending()->pluck('id')->all());
        self::assertSame([$inProgress->getKey()], ProductRequest::query()->inProgress()->pluck('id')->all());
        self::assertSame([$completed->getKey()], ProductRequest::query()->completed()->pluck('id')->all());
        self::assertSame([$cancelled->getKey()], ProductRequest::query()->cancelled()->pluck('id')->all());

        // The orderedByName scope should respect ascending and descending directives.
        self::assertSame([
            $completed->name,
            $inProgress->name,
            $pending->name,
        ], ProductRequest::query()->orderedByName()->pluck('name')->all());

        self::assertSame([
            $pending->name,
            $inProgress->name,
            $completed->name,
        ], ProductRequest::query()->orderedByName('desc')->pluck('name')->all());

        self::assertSame([
            $completed->name,
            $inProgress->name,
            $pending->name,
            $cancelled->name,
        ], ProductRequest::query()->withoutGlobalScope(StatusScope::class)->orderedByName()->pluck('name')->all());
    }

    public function test_marking_helpers_update_statuses_and_timestamps(): void
    {
        // Freeze time to assert timestamp updates precisely.
        $now = Carbon::parse('2025-01-01 12:34:56');
        Carbon::setTestNow($now);

        // Create the actors involved in the lifecycle transitions.
        $customer = User::factory()->create();
        $responder = User::factory()->create();
        $alternateResponder = User::factory()->create();

        // Seed an initial pending request that will be transitioned through multiple states.
        $request = ProductRequest::factory()->create([
            'user_id'      => $customer->id,
            'status'       => ProductRequest::STATUS_PENDING,
            'responded_at' => null,
            'responded_by' => null,
            'admin_notes'  => null,
        ]);

        // Transition the request into progress and confirm fields update accordingly.
        $request->markAsInProgress($responder->id);
        $request->refresh();
        self::assertTrue($request->isInProgress());
        self::assertTrue($request->responded_at?->equalTo($now));
        self::assertSame($responder->getKey(), $request->responded_by);

        // Completing the request should capture resolution metadata and maintain timestamps.
        $request->markAsCompleted($responder->id, 'Resolved for customer');
        $request->refresh();
        self::assertTrue($request->isCompleted());
        self::assertSame('Resolved for customer', $request->admin_notes);
        self::assertSame($responder->getKey(), $request->responded_by);

        // Cancelling later should overwrite the responder and update helper accessors.
        $request->markAsCancelled($alternateResponder->id, 'Customer changed mind');
        $request->refresh();
        self::assertTrue($request->isCancelled());
        self::assertSame('Customer changed mind', $request->admin_notes);
        self::assertSame($alternateResponder->getKey(), $request->responded_by);
        self::assertSame(__('translations.status_cancelled'), $request->status_label);
        self::assertSame('danger', $request->status_color);

        // Always clean up the mocked time after assertions run.
        Carbon::setTestNow();
    }
}
