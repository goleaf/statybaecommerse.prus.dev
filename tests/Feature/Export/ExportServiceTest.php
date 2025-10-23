<?php

declare(strict_types=1);

use App\Data\ExportRequestData;
use App\Enums\ExportStatus;
use App\Jobs\ProcessExportJob;
use App\Models\Order;
use App\Models\User;
use App\Notifications\ExportReadyNotification;
use App\Services\Export\Exporters\OrderExport;
use App\Services\Export\ExportService;
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

    $user = User::factory()->create();
    $orders = Order::factory()->count(3)->create();
    /** @var array<int, int> $orderIds */
    $orderIds = $orders->pluck('id')->map(static fn ($id): int => (int) $id)->all();

    $service = app(ExportService::class);
    $request = new ExportRequestData(
        name: 'Orders Export',
        exportable: OrderExport::class,
        format: 'csv',
        columns: ['number', 'status'],
        recordIds: $orderIds,
        userId: (int) $user->getKey(),
    );

    $export = $service->queue($request);

    Bus::assertDispatched(ProcessExportJob::class, fn (ProcessExportJob $job): bool => $job->exportId === (int) $export->getKey());

    (new ProcessExportJob((int) $export->getKey()))->handle($service);

    $export->refresh();

    expect($export->status)->toBe(ExportStatus::Completed)
        ->and($export->total_rows)->toBe(3)
        ->and(Storage::disk($disk)->exists($export->artifact_path))->toBeTrue();

    Notification::assertSentTo($user, ExportReadyNotification::class, function (ExportReadyNotification $notification) use ($export, $user): bool {
        $data = $notification->toArray($user);

        return $data['export_id'] === (int) $export->getKey();
    });
});

test('it returns signed download responses', function (): void {
    $disk = SecureStorage::disk();
    config()->set('filesystems.default', $disk);
    Storage::fake($disk);
    Notification::fake();

    $user = User::factory()->create();
    $orders = Order::factory()->count(2)->create();
    /** @var array<int, int> $orderIds */
    $orderIds = $orders->pluck('id')->map(static fn ($id): int => (int) $id)->all();

    $service = app(ExportService::class);
    $request = new ExportRequestData(
        name: 'Orders Export',
        exportable: OrderExport::class,
        format: 'csv',
        columns: ['number', 'status'],
        recordIds: $orderIds,
        userId: (int) $user->getKey(),
    );

    $export = $service->queue($request);
    (new ProcessExportJob((int) $export->getKey()))->handle($service);
    $export->refresh();

    $url = $service->downloadUrl($export, 5);

    $response = get($url);

    $response->assertOk();
    $response->assertHeader('content-disposition');
    $response->assertSee('number');
});
