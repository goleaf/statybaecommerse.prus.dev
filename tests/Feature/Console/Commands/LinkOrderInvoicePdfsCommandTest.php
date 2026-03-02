<?php

declare(strict_types=1);

use App\Models\File;
use App\Models\Order;
use App\Models\OrderInvoice;
use App\Models\User;
use App\Support\Storage\SecureStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File as FileSystem;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Config::set('invoices.enabled', false);
    Config::set('media-security.disk', 'secure-media');
    Storage::fake(SecureStorage::disk());
});

it('links existing order pdf files into invoice history', function (): void {
    $uploader = User::factory()->create();
    $order = Order::factory()->create();

    $olderFile = File::query()->create([
        'name'          => 'legacy-older.pdf',
        'original_name' => 'legacy-older.pdf',
        'path'          => "orders/{$order->getKey()}/invoices/legacy-older.pdf",
        'disk'          => SecureStorage::disk(),
        'mime_type'     => 'application/pdf',
        'size'          => 1234,
        'hash'          => hash('sha256', 'legacy-older'),
        'fileable_type' => Order::class,
        'fileable_id'   => $order->getKey(),
        'uploaded_by'   => $uploader->getKey(),
        'metadata'      => ['full_number' => 'LEG-0001'],
        'created_at'    => now()->subMinute(),
        'updated_at'    => now()->subMinute(),
    ]);

    $newerFile = File::query()->create([
        'name'          => 'legacy-newer.pdf',
        'original_name' => 'legacy-newer.pdf',
        'path'          => "orders/{$order->getKey()}/invoices/legacy-newer.pdf",
        'disk'          => SecureStorage::disk(),
        'mime_type'     => 'application/pdf',
        'size'          => 5678,
        'hash'          => hash('sha256', 'legacy-newer'),
        'fileable_type' => Order::class,
        'fileable_id'   => $order->getKey(),
        'uploaded_by'   => $uploader->getKey(),
        'metadata'      => ['full_number' => 'LEG-0002'],
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);

    artisan('orders:invoices:link-pdfs')->assertExitCode(0);

    $this->assertDatabaseHas('order_invoices', [
        'order_id' => $order->getKey(),
        'file_id'  => $olderFile->getKey(),
        'status'   => OrderInvoice::STATUS_READY,
    ]);

    $this->assertDatabaseHas('order_invoices', [
        'order_id' => $order->getKey(),
        'file_id'  => $newerFile->getKey(),
        'status'   => OrderInvoice::STATUS_READY,
    ]);

    $currentInvoice = OrderInvoice::query()
        ->where('order_id', $order->getKey())
        ->where('is_current', true)
        ->first();

    expect($currentInvoice)->not->toBeNull()
        ->and($currentInvoice?->file_id)->toBe($newerFile->getKey());
});

it('does not create invoice links when run in dry-run mode', function (): void {
    $uploader = User::factory()->create();
    $order = Order::factory()->create();

    File::query()->create([
        'name'          => 'legacy-dry-run.pdf',
        'original_name' => 'legacy-dry-run.pdf',
        'path'          => "orders/{$order->getKey()}/invoices/legacy-dry-run.pdf",
        'disk'          => SecureStorage::disk(),
        'mime_type'     => 'application/pdf',
        'size'          => 1234,
        'hash'          => hash('sha256', 'legacy-dry-run'),
        'fileable_type' => Order::class,
        'fileable_id'   => $order->getKey(),
        'uploaded_by'   => $uploader->getKey(),
        'metadata'      => [],
    ]);

    artisan('orders:invoices:link-pdfs --dry-run')->assertExitCode(0);

    expect(OrderInvoice::query()->count())->toBe(0);
});

it('imports legacy disk pdfs and links them to orders when filename contains order id', function (): void {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->getKey(),
    ]);

    $legacyPath = "documents/{$order->getKey()}_2026-02-27_14-20-00.pdf";
    Storage::disk(SecureStorage::disk())->put($legacyPath, 'legacy-pdf-content');

    artisan('orders:invoices:link-pdfs')->assertExitCode(0);

    $this->assertDatabaseHas('files', [
        'path'          => $legacyPath,
        'fileable_type' => Order::class,
        'fileable_id'   => $order->getKey(),
    ]);

    $file = File::query()->where('path', $legacyPath)->first();

    expect($file)->not->toBeNull();

    $this->assertDatabaseHas('order_invoices', [
        'order_id'        => $order->getKey(),
        'file_id'         => (int) $file?->getKey(),
        'status'          => OrderInvoice::STATUS_READY,
        'generation_mode' => OrderInvoice::MODE_BACKFILL,
    ]);
});

it('creates unresolved legacy pdf report for files that cannot be mapped from storage', function (): void {
    Storage::disk(SecureStorage::disk())->put('documents/legacy-orphan.pdf', 'dummy-pdf-content');

    $reportPath = storage_path('app/reports/test-unresolved-order-invoice-pdfs.csv');
    if (is_file($reportPath)) {
        FileSystem::delete($reportPath);
    }

    artisan('orders:invoices:link-pdfs --report-path=' . str_replace('\\', '/', $reportPath))->assertExitCode(0);

    expect(is_file($reportPath))->toBeTrue();

    $report = file_get_contents($reportPath);
    expect(is_string($report))->toBeTrue()
        ->and($report)->toContain('relative_path')
        ->and($report)->toContain('documents/legacy-orphan.pdf');
});
