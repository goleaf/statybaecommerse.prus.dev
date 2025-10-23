<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Actions;

use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Jobs\ProcessExport;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Export\ExportFormat;
use App\Services\Export\ExportStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

final class ExportBulkActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_bulk_export_dispatches_job(): void
    {
        Queue::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $orders = Order::factory()->count(2)->create();

        $this->actingAs($admin);

        Livewire::test(ListOrders::class)
            ->callTableBulkAction('export_orders', $orders, [
                'format' => ExportFormat::Csv->value,
                'columns' => array_keys(OrderResource::availableExportColumns()),
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('exports', [
            'resource' => OrderResource::class,
            'user_id' => $admin->id,
            'status' => ExportStatus::Pending->value,
        ]);

        Queue::assertPushed(ProcessExport::class);
    }

    public function test_product_bulk_export_dispatches_job(): void
    {
        Queue::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $products = Product::factory()->count(2)->create();

        $this->actingAs($admin);

        Livewire::test(ListProducts::class)
            ->callTableBulkAction('export_products', $products, [
                'format' => ExportFormat::Pdf->value,
                'columns' => array_keys(ProductResource::availableExportColumns()),
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('exports', [
            'resource' => ProductResource::class,
            'user_id' => $admin->id,
            'status' => ExportStatus::Pending->value,
        ]);

        Queue::assertPushed(ProcessExport::class);
    }

    public function test_user_bulk_export_dispatches_job(): void
    {
        Queue::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $users = User::factory()->count(2)->create();

        $this->actingAs($admin);

        Livewire::test(ListUsers::class)
            ->callTableBulkAction('export_users', $users, [
                'format' => ExportFormat::Xlsx->value,
                'columns' => array_keys(UserResource::availableExportColumns()),
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('exports', [
            'resource' => UserResource::class,
            'user_id' => $admin->id,
            'status' => ExportStatus::Pending->value,
        ]);

        Queue::assertPushed(ProcessExport::class);
    }
}
