<?php

declare(strict_types=1);

use App\Data\ExportRequestData;
use App\Enums\ExportStatus;
use App\Jobs\ProcessExportJob;
use App\Models\Order;
use App\Models\User;
use App\Notifications\ExportReadyNotification;
use App\Services\Export\ExportService;
use App\Services\Export\Exporters\OrderExport;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

test('it queues and processes exports', function (): void {
    config()->set('filesystems.default', 'public');
    Storage::fake('public');
    Notification::fake();
    Bus::fake();

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

    Bus::assertDispatched(ProcessExportJob::class, fn (ProcessExportJob $job): bool => $job->exportId === $export->getKey());

    (new ProcessExportJob($export->getKey()))->handle($service);

    $export->refresh();

    expect($export->status)->toBe(ExportStatus::Completed)
        ->and($export->total_rows)->toBe(3)
        ->and(Storage::disk('public')->exists($export->artifact_path))->toBeTrue();

    Notification::assertSentTo($user, ExportReadyNotification::class, function (ExportReadyNotification $notification) use ($export): bool {
        $data = $notification->toArray($user);

        return $data['export_id'] === $export->getKey();
    });
});

test('it returns signed download responses', function (): void {
    config()->set('filesystems.default', 'public');
    Storage::fake('public');
    Notification::fake();

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
    (new ProcessExportJob($export->getKey()))->handle($service);
    $export->refresh();

    $url = $service->downloadUrl($export, 5);

    $response = $this->get($url);

    $response->assertOk();
    $response->assertHeader('content-disposition');
    $response->assertSee('number');
});
