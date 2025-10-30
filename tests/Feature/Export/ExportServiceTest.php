<?php

declare(strict_types=1);

use App\Data\ExportRequestData;
use App\Enums\ExportStatus;
use App\Jobs\ProcessExport;
use App\Jobs\ProcessExportJob;
use App\Models\Order;
use App\Models\User;
use App\Notifications\ExportReadyNotification;
use App\Services\Export\Exporters\OrderExport;
use App\Services\Export\ExportService;
use App\Support\Exports\ExportUrlGenerator;
use App\Support\Storage\SecureStorage;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

test('it queues and processes exports', function (): void {
    $disk = SecureStorage::disk();
    config()->set('filesystems.default', $disk);
    Storage::fake($disk);
    Notification::fake();
    Bus::fake();

    app()->setLocale('lt');

    $user = User::factory()->create();
    $orders = Order::factory()->count(3)->create();

    $service = app(ExportService::class);
    $request = new ExportRequestData(
        name: 'Orders Export',
        exportable: OrderExport::class,
        format: 'csv',
        columns: ['number', 'status'],
        recordIds: $orders->pluck('id')->all(),
        userId: $user->getKey(),
    );

    $export = $service->queue($request);

    // Assert the legacy alias is queued for backwards compatibility so external queue monitors continue to detect export jobs.
    Bus::assertDispatched(ProcessExportJob::class, fn (ProcessExportJob $job): bool => $job->exportId === $export->getKey());

    (new ProcessExport($export->getKey()))->handle($service);

    $export->refresh();

    expect($export->status)->toBe(ExportStatus::Completed)
        ->and($export->total_rows)->toBe(3)
        ->and(Storage::disk($disk)->exists($export->artifact_path))->toBeTrue();

    Notification::assertSentTo($user, ExportReadyNotification::class, function (ExportReadyNotification $notification) use ($user, $export): bool {
        $data = $notification->toArray($user);

        return $data['export_id'] === $export->getKey();
    });
});

test('it returns signed download responses', function (): void {
    $disk = SecureStorage::disk();
    config()->set('filesystems.default', $disk);
    Storage::fake($disk);
    Notification::fake();

    app()->setLocale('lt');

    $user = User::factory()->create();
    $orders = Order::factory()->count(2)->create();

    $service = app(ExportService::class);
    $request = new ExportRequestData(
        name: 'Orders Export',
        exportable: OrderExport::class,
        format: 'csv',
        columns: ['number', 'status'],
        recordIds: $orders->pluck('id')->all(),
        userId: $user->getKey(),
    );

    $export = $service->queue($request);
    (new ProcessExport($export->getKey()))->handle($service);
    $export->refresh();

    $url = ExportUrlGenerator::temporarySignedDownloadUrl($export, 5);

    $response = $this->get($url);

    $response->assertOk();
    $response->assertHeader('content-disposition');

    $content = $response->streamedContent();

    expect($content)->toContain(__('orders.fields.order_number', [], 'lt'));
});
