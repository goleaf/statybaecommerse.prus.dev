<?php

declare(strict_types=1);

namespace Tests\Feature\Exports;

use App\Data\ExportRequestData;
use App\Enums\ExportFormat;
use App\Enums\ExportType;
use App\Jobs\ProcessExportJob;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\ExportReadyNotification;
use App\Services\Export\ExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelReader;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('feature: queues, processes, and downloads order exports', function (): void {
    Storage::fake('local');
    Notification::fake();
    Queue::fake();

    $user = User::factory()->create();
    Order::factory()->count(3)->create(['user_id' => $user->getKey()]);

    $service = app(ExportService::class);

    $request = ExportRequestData::from([
        'entity' => ExportType::ORDERS->value,
        'format' => ExportFormat::CSV->value,
        'columns' => ['number', 'status', 'payment_status', 'total', 'customer', 'items', 'created_at'],
        'filters' => [],
        'locale' => 'en',
        'timezone' => 'UTC',
        'ids' => [],
    ]);

    $export = $service->queueExport($request, $user);

    Queue::assertPushed(ProcessExportJob::class, function (ProcessExportJob $job) use ($export): bool {
        return $job->export->is($export);
    });

    $service->process($export->fresh());

    Notification::assertSentTo($user, ExportReadyNotification::class);

    $export->refresh();

    Storage::disk('local')->assertExists((string) $export->file_path);

    $csv = Storage::disk('local')->get((string) $export->file_path);
    $rows = array_map('str_getcsv', array_filter(explode("\n", trim($csv))));

    expect($rows[0])->toBe([
        'Order Number',
        'Status',
        'Payment Status',
        'Grand Total',
        'Customer',
        'Items',
        'Created At',
    ]);

    actingAs($user, 'sanctum');
    $response = $this->get($export->signedUrl(now()->addMinutes(5)));
    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv');
    $response->assertHeaderContains('content-disposition', 'attachment');
});

it('feature: streams one hundred thousand users without exhausting memory', function (): void {
    Storage::fake('local');
    Notification::fake();
    Queue::fake();

    $requester = User::factory()->create();

    $password = bcrypt('password');
    $now = now();

    $batch = [];
    for ($i = 0; $i < 100000; $i++) {
        $batch[] = [
            'name' => 'Bulk User '.$i,
            'email' => "bulk{$i}@example.test",
            'email_verified_at' => $now,
            'password' => $password,
            'preferred_locale' => 'en',
            'is_admin' => false,
            'remember_token' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if (count($batch) === 1000) {
            DB::table('users')->insert($batch);
            $batch = [];
        }
    }

    if ($batch !== []) {
        DB::table('users')->insert($batch);
    }

    $service = app(ExportService::class);

    $request = ExportRequestData::from([
        'entity' => ExportType::USERS->value,
        'format' => ExportFormat::CSV->value,
        'columns' => [],
        'filters' => [],
        'locale' => 'en',
        'timezone' => 'UTC',
        'ids' => [],
    ]);

    $export = $service->queueExport($request, $requester);

    Queue::assertPushed(ProcessExportJob::class);

    $service->process($export->fresh());

    $export->refresh();

    expect($export->total_rows)->toBeGreaterThanOrEqual(100000);
    Storage::disk('local')->assertExists((string) $export->file_path);
    expect(Storage::disk('local')->size((string) $export->file_path))->toBeGreaterThan(0);
    gc_collect_cycles();
    expect(memory_get_usage(true))->toBeLessThan(256 * 1024 * 1024);
});

it('feature: generates aligned columns for csv, xlsx, and pdf formats', function (): void {
    Storage::fake('local');
    Notification::fake();
    Queue::fake();

    $user = User::factory()->create();
    $product = Product::factory()->create([
        'price' => 19.99,
        'stock_quantity' => 5,
        'status' => 'published',
    ]);

    $service = app(ExportService::class);

    $csvRequest = ExportRequestData::from([
        'entity' => ExportType::PRODUCTS->value,
        'format' => ExportFormat::CSV->value,
        'columns' => ['sku', 'name', 'status', 'price', 'stock', 'created_at'],
        'filters' => ['status' => 'published'],
        'locale' => 'en',
        'timezone' => 'UTC',
        'ids' => [$product->getKey()],
    ]);

    $csvExport = $service->queueExport($csvRequest, $user);
    $service->process($csvExport->fresh());
    $csvExport->refresh();

    $csvContent = Storage::disk('local')->get((string) $csvExport->file_path);
    $csvRows = array_map('str_getcsv', array_filter(explode("\n", trim($csvContent))));
    expect($csvRows[0])->toBe([
        'SKU',
        'Name',
        'Status',
        'Price',
        'Stock',
        'Created At',
    ]);

    $xlsxRequest = ExportRequestData::from([
        'entity' => ExportType::PRODUCTS->value,
        'format' => ExportFormat::XLSX->value,
        'columns' => ['sku', 'name', 'status', 'price', 'stock', 'created_at'],
        'filters' => ['status' => 'published'],
        'locale' => 'en',
        'timezone' => 'UTC',
        'ids' => [$product->getKey()],
    ]);

    $xlsxExport = $service->queueExport($xlsxRequest, $user);
    $service->process($xlsxExport->fresh());
    $xlsxExport->refresh();

    $xlsxPath = Storage::disk('local')->path((string) $xlsxExport->file_path);
    $xlsxRows = SimpleExcelReader::create($xlsxPath)->getRows()->toArray();
    expect(array_keys($xlsxRows[0]))->toBe([
        'SKU',
        'Name',
        'Status',
        'Price',
        'Stock',
        'Created At',
    ]);

    $pdfRequest = ExportRequestData::from([
        'entity' => ExportType::ORDERS->value,
        'format' => ExportFormat::PDF->value,
        'columns' => ['number', 'status', 'payment_status', 'total', 'customer', 'items', 'created_at'],
        'filters' => [],
        'locale' => 'en',
        'timezone' => 'UTC',
        'ids' => [],
    ]);

    Order::factory()->create(['total' => 42.50]);
    $pdfExport = $service->queueExport($pdfRequest, $user);
    $service->process($pdfExport->fresh());
    $pdfExport->refresh();

    $pdfRaw = Storage::disk('local')->get((string) $pdfExport->file_path);
    expect($pdfRaw)->toContain('Order Number');
    expect($pdfRaw)->toContain('Grand Total');
    expect($pdfRaw)->toContain('Total rows:');

    Queue::assertPushed(ProcessExportJob::class, 3);
});
