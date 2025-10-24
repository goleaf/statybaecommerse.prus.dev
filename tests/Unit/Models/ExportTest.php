<?php declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\ExportStatus;
use App\Models\Export;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_can_be_created(): void
    {
        $user = User::factory()->create();

        $export = Export::factory()->create([
            'name' => 'Test Export',
            'format' => 'csv',
            'status' => ExportStatus::Queued,
            'exportable_type' => 'Product',
            'requested_by' => $user->id,
        ]);

        expect($export)
            ->toBeInstanceOf(Export::class)
            ->and($export->name)
            ->toBe('Test Export')
            ->and($export->format)
            ->toBe('csv')
            ->and($export->status)
            ->toBe(ExportStatus::Queued)
            ->and($export->exportable_type)
            ->toBe('Product')
            ->and($export->requested_by)
            ->toBe($user->id);
    }

    public function test_export_automatically_generates_uuid_on_creation(): void
    {
        $export = Export::factory()->create(['uuid' => null]);

        expect($export->uuid)
            ->not
            ->toBeNull()
            ->and($export->uuid)
            ->toBeString()
            ->and(strlen($export->uuid))
            ->toBe(36);
    }

    public function test_export_automatically_sets_requested_at_on_creation(): void
    {
        $export = Export::factory()->create(['requested_at' => null]);

        expect($export->requested_at)
            ->not
            ->toBeNull()
            ->and($export->requested_at)
            ->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    }

    public function test_export_automatically_sets_default_counter_values(): void
    {
        $export = Export::factory()->create([
            'total_rows' => null,
            'processed_rows' => null,
        ]);

        expect($export->total_rows)
            ->toBe(0)
            ->and($export->processed_rows)
            ->toBe(0);
    }

    public function test_export_belongs_to_user(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $export = Export::factory()->create(['requested_by' => $user->id]);

        expect($export->requestedBy)
            ->toBeInstanceOf(User::class)
            ->and($export->requestedBy->id)
            ->toBe($user->id)
            ->and($export->requestedBy->name)
            ->toBe('John Doe');
    }

    public function test_export_uses_uuid_as_route_key(): void
    {
        $export = Export::factory()->create();

        expect($export->getRouteKeyName())->toBe('uuid');
    }

    public function test_export_casts_columns_to_array(): void
    {
        $columns = ['id', 'name', 'email'];
        $export = Export::factory()->create(['columns' => $columns]);

        expect($export->columns)
            ->toBeArray()
            ->and($export->columns)
            ->toBe($columns);
    }

    public function test_export_casts_exportable_options_to_array(): void
    {
        $options = ['filter' => 'active', 'limit' => 100];
        $export = Export::factory()->create(['exportable_options' => $options]);

        expect($export->exportable_options)
            ->toBeArray()
            ->and($export->exportable_options)
            ->toBe($options);
    }

    public function test_export_casts_timestamps_to_carbon_instances(): void
    {
        $export = Export::factory()->create();

        expect($export->requested_at)
            ->toBeInstanceOf(\Illuminate\Support\Carbon::class)
            ->and($export->created_at)
            ->toBeInstanceOf(\Illuminate\Support\Carbon::class)
            ->and($export->updated_at)
            ->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    }

    public function test_export_casts_status_to_enum(): void
    {
        $export = Export::factory()->create(['status' => ExportStatus::Processing]);

        expect($export->status)
            ->toBeInstanceOf(ExportStatus::class)
            ->and($export->status)
            ->toBe(ExportStatus::Processing);
    }

    public function test_scope_queued_filters_queued_exports(): void
    {
        Export::factory()->count(2)->create(['status' => ExportStatus::Queued]);
        Export::factory()->create(['status' => ExportStatus::Processing]);
        Export::factory()->create(['status' => ExportStatus::Completed]);

        $queued = Export::queued()->get();

        expect($queued)
            ->toHaveCount(2)
            ->and($queued->every(fn($export) => $export->status === ExportStatus::Queued))
            ->toBeTrue();
    }

    public function test_scope_processing_filters_processing_exports(): void
    {
        Export::factory()->create(['status' => ExportStatus::Queued]);
        Export::factory()->count(3)->create(['status' => ExportStatus::Processing]);
        Export::factory()->create(['status' => ExportStatus::Completed]);

        $processing = Export::processing()->get();

        expect($processing)
            ->toHaveCount(3)
            ->and($processing->every(fn($export) => $export->status === ExportStatus::Processing))
            ->toBeTrue();
    }

    public function test_scope_completed_filters_completed_exports(): void
    {
        Export::factory()->create(['status' => ExportStatus::Queued]);
        Export::factory()->create(['status' => ExportStatus::Processing]);
        Export::factory()->count(4)->create(['status' => ExportStatus::Completed]);

        $completed = Export::completed()->get();

        expect($completed)
            ->toHaveCount(4)
            ->and($completed->every(fn($export) => $export->status === ExportStatus::Completed))
            ->toBeTrue();
    }

    public function test_scope_failed_filters_failed_exports(): void
    {
        Export::factory()->create(['status' => ExportStatus::Queued]);
        Export::factory()->count(2)->create(['status' => ExportStatus::Failed]);

        $failed = Export::failed()->get();

        expect($failed)
            ->toHaveCount(2)
            ->and($failed->every(fn($export) => $export->status === ExportStatus::Failed))
            ->toBeTrue();
    }

    public function test_scope_by_user_filters_exports_by_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Export::factory()->count(3)->create(['requested_by' => $user1->id]);
        Export::factory()->count(2)->create(['requested_by' => $user2->id]);

        $user1Exports = Export::byUser($user1->id)->get();

        expect($user1Exports)
            ->toHaveCount(3)
            ->and($user1Exports->every(fn($export) => $export->requested_by === $user1->id))
            ->toBeTrue();
    }

    public function test_scope_recent_first_orders_exports_by_requested_at_desc(): void
    {
        $old = Export::factory()->create(['requested_at' => now()->subDays(3)]);
        $recent = Export::factory()->create(['requested_at' => now()->subDay()]);
        $newest = Export::factory()->create(['requested_at' => now()]);

        $exports = Export::recentFirst()->get();

        expect($exports->first()->id)
            ->toBe($newest->id)
            ->and($exports->last()->id)
            ->toBe($old->id);
    }

    public function test_scope_of_type_filters_exports_by_exportable_type(): void
    {
        Export::factory()->count(2)->create(['exportable_type' => 'Product']);
        Export::factory()->count(3)->create(['exportable_type' => 'Order']);
        Export::factory()->create(['exportable_type' => 'Customer']);

        $productExports = Export::ofType('Product')->get();

        expect($productExports)
            ->toHaveCount(2)
            ->and($productExports->every(fn($export) => $export->exportable_type === 'Product'))
            ->toBeTrue();
    }

    public function test_file_extension_accessor_returns_format(): void
    {
        $export = Export::factory()->create(['format' => 'xlsx']);

        expect($export->file_extension)->toBe('xlsx');
    }

    public function test_progress_percentage_accessor_calculates_correctly(): void
    {
        $export = Export::factory()->create([
            'total_rows' => 1000,
            'processed_rows' => 750,
        ]);

        expect($export->progress_percentage)->toBe(75);
    }

    public function test_progress_percentage_accessor_returns_zero_when_total_rows_is_zero(): void
    {
        $export = Export::factory()->create([
            'total_rows' => 0,
            'processed_rows' => 0,
        ]);

        expect($export->progress_percentage)->toBe(0);
    }

    public function test_is_completed_accessor_returns_true_for_completed_status(): void
    {
        $export = Export::factory()->completed()->create();

        expect($export->is_completed)->toBeTrue();
    }

    public function test_is_completed_accessor_returns_false_for_non_completed_status(): void
    {
        $export = Export::factory()->create(['status' => ExportStatus::Processing]);

        expect($export->is_completed)->toBeFalse();
    }

    public function test_is_failed_accessor_returns_true_for_failed_status(): void
    {
        $export = Export::factory()->failed()->create();

        expect($export->is_failed)->toBeTrue();
    }

    public function test_is_failed_accessor_returns_false_for_non_failed_status(): void
    {
        $export = Export::factory()->create(['status' => ExportStatus::Queued]);

        expect($export->is_failed)->toBeFalse();
    }

    public function test_is_processing_accessor_returns_true_for_processing_status(): void
    {
        $export = Export::factory()->processing()->create();

        expect($export->is_processing)->toBeTrue();
    }

    public function test_is_processing_accessor_returns_false_for_non_processing_status(): void
    {
        $export = Export::factory()->create(['status' => ExportStatus::Queued]);

        expect($export->is_processing)->toBeFalse();
    }

    public function test_is_downloadable_accessor_returns_true_when_completed_with_artifact(): void
    {
        $export = Export::factory()->completed()->create();

        expect($export->is_downloadable)->toBeTrue();
    }

    public function test_is_downloadable_accessor_returns_false_when_not_completed(): void
    {
        $export = Export::factory()->create([
            'status' => ExportStatus::Processing,
            'artifact_path' => 'exports/test.csv',
            'artifact_filename' => 'test.csv',
        ]);

        expect($export->is_downloadable)->toBeFalse();
    }

    public function test_is_downloadable_accessor_returns_false_when_completed_without_artifact(): void
    {
        $export = Export::factory()->create([
            'status' => ExportStatus::Completed,
            'artifact_path' => null,
            'artifact_filename' => null,
        ]);

        expect($export->is_downloadable)->toBeFalse();
    }

    public function test_mark_as_processing_updates_status(): void
    {
        $export = Export::factory()->create(['status' => ExportStatus::Queued]);

        $result = $export->markAsProcessing();

        expect($result)
            ->toBeTrue()
            ->and($export->fresh()->status)
            ->toBe(ExportStatus::Processing);
    }

    public function test_mark_as_completed_updates_status_and_sets_artifact_details(): void
    {
        $export = Export::factory()->processing()->create();

        $result = $export->markAsCompleted('exports/test.csv', 'test.csv', 'public');

        expect($result)
            ->toBeTrue()
            ->and($export->fresh()->status)
            ->toBe(ExportStatus::Completed)
            ->and($export->fresh()->completed_at)
            ->not
            ->toBeNull()
            ->and($export->fresh()->artifact_path)
            ->toBe('exports/test.csv')
            ->and($export->fresh()->artifact_filename)
            ->toBe('test.csv')
            ->and($export->fresh()->artifact_disk)
            ->toBe('public');
    }

    public function test_mark_as_completed_uses_existing_disk_when_not_provided(): void
    {
        $export = Export::factory()->processing()->create(['artifact_disk' => 'local']);

        $export->markAsCompleted('exports/test.csv', 'test.csv');

        expect($export->fresh()->artifact_disk)->toBe('local');
    }

    public function test_mark_as_failed_updates_status_and_sets_failure_details(): void
    {
        $export = Export::factory()->processing()->create();

        $result = $export->markAsFailed('Database timeout occurred');

        expect($result)
            ->toBeTrue()
            ->and($export->fresh()->status)
            ->toBe(ExportStatus::Failed)
            ->and($export->fresh()->failed_at)
            ->not
            ->toBeNull()
            ->and($export->fresh()->failure_reason)
            ->toBe('Database timeout occurred');
    }

    public function test_update_progress_updates_processed_rows(): void
    {
        $export = Export::factory()->processing()->create([
            'total_rows' => 1000,
            'processed_rows' => 0,
        ]);

        $result = $export->updateProgress(500);

        expect($result)
            ->toBeTrue()
            ->and($export->fresh()->processed_rows)
            ->toBe(500);
    }

    public function test_update_progress_can_update_both_processed_and_total_rows(): void
    {
        $export = Export::factory()->processing()->create([
            'total_rows' => 1000,
            'processed_rows' => 0,
        ]);

        $result = $export->updateProgress(600, 1200);

        expect($result)
            ->toBeTrue()
            ->and($export->fresh()->processed_rows)
            ->toBe(600)
            ->and($export->fresh()->total_rows)
            ->toBe(1200);
    }

    public function test_factory_creates_export_with_default_state(): void
    {
        $export = Export::factory()->create();

        expect($export->status)
            ->toBe(ExportStatus::Queued)
            ->and($export->processed_rows)
            ->toBe(0)
            ->and($export->artifact_path)
            ->toBeNull()
            ->and($export->completed_at)
            ->toBeNull()
            ->and($export->failed_at)
            ->toBeNull();
    }

    public function test_factory_queued_state_creates_queued_export(): void
    {
        $export = Export::factory()->queued()->create();

        expect($export->status)
            ->toBe(ExportStatus::Queued)
            ->and($export->processed_rows)
            ->toBe(0)
            ->and($export->completed_at)
            ->toBeNull();
    }

    public function test_factory_processing_state_creates_processing_export(): void
    {
        $export = Export::factory()->processing()->create(['total_rows' => 1000]);

        expect($export->status)
            ->toBe(ExportStatus::Processing)
            ->and($export->processed_rows)
            ->toBeGreaterThan(0)
            ->and($export->completed_at)
            ->toBeNull();
    }

    public function test_factory_completed_state_creates_completed_export(): void
    {
        $export = Export::factory()->completed()->create();

        expect($export->status)
            ->toBe(ExportStatus::Completed)
            ->and($export->processed_rows)
            ->toBe($export->total_rows)
            ->and($export->completed_at)
            ->not
            ->toBeNull()
            ->and($export->artifact_path)
            ->not
            ->toBeNull()
            ->and($export->artifact_filename)
            ->not
            ->toBeNull();
    }

    public function test_factory_failed_state_creates_failed_export(): void
    {
        $export = Export::factory()->failed()->create();

        expect($export->status)
            ->toBe(ExportStatus::Failed)
            ->and($export->failed_at)
            ->not
            ->toBeNull()
            ->and($export->failure_reason)
            ->not
            ->toBeNull()
            ->and($export->artifact_path)
            ->toBeNull();
    }

    public function test_factory_for_products_state_creates_product_export(): void
    {
        $export = Export::factory()->forProducts()->create();

        expect($export->exportable_type)
            ->toBe('Product')
            ->and($export->name)
            ->toBe('Product Export')
            ->and($export->columns)
            ->toContain('sku');
    }

    public function test_factory_for_orders_state_creates_order_export(): void
    {
        $export = Export::factory()->forOrders()->create();

        expect($export->exportable_type)
            ->toBe('Order')
            ->and($export->name)
            ->toBe('Order Export')
            ->and($export->columns)
            ->toContain('number');
    }

    public function test_factory_for_customers_state_creates_customer_export(): void
    {
        $export = Export::factory()->forCustomers()->create();

        expect($export->exportable_type)
            ->toBe('Customer')
            ->and($export->name)
            ->toBe('Customer Export')
            ->and($export->columns)
            ->toContain('email');
    }
}
