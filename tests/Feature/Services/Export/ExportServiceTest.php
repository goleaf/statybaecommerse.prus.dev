<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Export;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\User;
use App\Notifications\ExportCompletedNotification;
use App\Services\Export\ExportFormat;
use App\Services\Export\ExportService;
use App\Services\Export\ExportStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ExportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_process_persists_artifact_and_notifies_user(): void
    {
        Storage::fake(config('export.disk'));
        Notification::fake();

        $user = User::factory()->create(['is_admin' => true]);
        $orders = Order::factory()->count(2)->create();

        /** @var ExportService $service */
        $service = app(ExportService::class);

        $export = $service->queueResourceExport(
            resourceClass: OrderResource::class,
            records: $orders,
            columnKeys: array_keys(OrderResource::availableExportColumns()),
            format: ExportFormat::Csv,
            requestedBy: $user,
        );

        $service->process($export);
        $export->refresh();

        $this->assertSame(ExportStatus::Completed, $export->status);
        Storage::disk(config('export.disk'))->assertExists($export->path);
        Notification::assertSentTo($user, ExportCompletedNotification::class);
    }
}
