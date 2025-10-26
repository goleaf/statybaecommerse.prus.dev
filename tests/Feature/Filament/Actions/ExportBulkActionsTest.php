<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Actions;

use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Jobs\ProcessExport;
use App\Models\Export;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Export\Exporters\OrderExport;
use App\Services\Export\Exporters\ProductExport;
use App\Services\Export\Exporters\UserExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

final class ExportBulkActionsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolveAdminPanel();

        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        config()->set('export.disk', 'public');
        config()->set('export.formats', [
            'csv'  => \App\Services\Export\Writers\CsvExportWriter::class,
            'xlsx' => \App\Services\Export\Writers\XlsxExportWriter::class,
            'pdf'  => \App\Services\Export\Writers\PdfExportWriter::class,
        ]);

        Storage::fake('public');
        Notification::fake();
        Bus::fake();

        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_orders_bulk_export_dispatches_job_with_selected_columns(): void
    {
        $orders = Order::factory()->count(3)->create();

        Livewire::test(ListOrders::class)
            ->call('loadTable')
            ->callTableBulkAction('export_selected', $orders, [
                'format'  => 'csv',
                'columns' => ['number', 'status'],
            ])
            ->assertHasNoTableBulkActionErrors();

        $export = Export::query()->latest()->first();

        self::assertNotNull($export);
        self::assertSame(OrderExport::class, $export->exportable_type);
        self::assertSame(['number', 'status'], $export->columns);
        self::assertSame($this->admin->getKey(), $export->requested_by);

        Bus::assertDispatched(ProcessExport::class, fn (ProcessExport $job): bool => $job->exportId === $export->getKey());
    }

    public function test_products_bulk_export_uses_selected_format(): void
    {
        Product::factory()->count(2)->create();

        Livewire::test(ListProducts::class)
            ->call('loadTable')
            ->callTableBulkAction('export_selected', Product::all(), [
                'format'  => 'xlsx',
                'columns' => ['sku', 'name'],
            ])
            ->assertHasNoTableBulkActionErrors();

        $export = Export::query()->latest()->first();

        self::assertNotNull($export);
        self::assertSame(ProductExport::class, $export->exportable_type);
        self::assertSame('xlsx', $export->format);
        self::assertSame(['sku', 'name'], $export->columns);
    }

    public function test_users_bulk_export_defaults_to_csv_when_no_format_selected(): void
    {
        $users = User::factory()->count(2)->create();

        Livewire::test(ListUsers::class)
            ->call('loadTable')
            ->callTableBulkAction('export_selected', $users)
            ->assertHasNoTableBulkActionErrors();

        $export = Export::query()->latest()->first();

        self::assertNotNull($export);
        self::assertSame(UserExport::class, $export->exportable_type);
        self::assertSame('csv', $export->format);
        self::assertNotEmpty($export->columns);
    }
}
