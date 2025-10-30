<?php

declare(strict_types=1);

use App\Data\ExportRequestData;
use App\Enums\ExportStatus;
use App\Jobs\ProcessExport;
use App\Jobs\ProcessExportJob;
use App\Models\Export;
use App\Models\User;
use App\Notifications\ExportCompletedNotification;
use App\Notifications\ExportFailedNotification;
use App\Services\Export\Contracts\Exportable;
use App\Services\Export\ExportColumn;
use App\Services\Export\ExportService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Schema::dropIfExists('fake_exports');
    Schema::dropIfExists('exports');
    Schema::dropIfExists('users');

    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name')->nullable();
        $table->string('email')->unique();
        $table->string('password')->nullable();
        $table->boolean('is_admin')->default(false);
        $table->boolean('is_active')->default(true);
        $table->timestamp('email_verified_at')->nullable();
        $table->rememberToken();
        $table->timestamps();
        $table->softDeletes();
    });

    Schema::create('exports', function (Blueprint $table): void {
        $table->id();
        $table->uuid('uuid')->unique();
        $table->string('name');
        $table->string('format');
        $table->string('status');
        $table->string('exportable_type');
        $table->json('columns');
        $table->json('exportable_options')->nullable();
        $table->unsignedInteger('total_rows')->default(0);
        $table->unsignedInteger('processed_rows')->default(0);
        $table->string('artifact_disk')->nullable();
        $table->string('artifact_path')->nullable();
        $table->string('artifact_filename')->nullable();
        $table->timestamp('requested_at');
        $table->timestamp('completed_at')->nullable();
        $table->timestamp('failed_at')->nullable();
        $table->text('failure_reason')->nullable();
        $table->foreignId('requested_by')->nullable();
        $table->timestamps();
    });

    Schema::create('fake_exports', function (Blueprint $table): void {
        $table->id();
        $table->string('number');
        $table->string('status');
        $table->string('total');
        $table->timestamp('created_at')->nullable();
        $table->timestamp('updated_at')->nullable();
    });

    Storage::fake('public');
    Notification::fake();
    Bus::fake();

    DB::table('exports')->delete();
    DB::table('users')->delete();
    DB::table('fake_exports')->delete();
});

test('it queues export records and dispatches the processor job', function (): void {
    $userId = DB::table('users')->insertGetId([
        'name'       => 'Queue User',
        'email'      => 'queue@example.com',
        'password'   => 'secret',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $rows = [
        FakeExportRow::query()->create([
            'number'     => 'ORD-1001',
            'status'     => 'pending',
            'total'      => '120.00',
            'created_at' => now()->subDay(),
        ]),
        FakeExportRow::query()->create([
            'number'     => 'ORD-1002',
            'status'     => 'processing',
            'total'      => '220.00',
            'created_at' => now(),
        ]),
    ];

    $service = app(ExportService::class);
    $request = new ExportRequestData(
        name: 'Test Orders Export',
        exportable: TestOrderExportable::class,
        format: 'csv',
        columns: ['number', 'status', 'total'],
        recordIds: collect($rows)->map(fn (FakeExportRow $row): int => $row->getKey())->all(),
        userId: $userId,
    );

    $export = $service->queue($request);

    expect($export)
        ->status->toBe(ExportStatus::Queued)
        ->and($export->columns)->toBe(['number', 'status', 'total'])
        ->and($export->artifact_disk)->toBe('public');

    // Validate that the compatibility job alias is queued, ensuring legacy automation continues monitoring exports.
    Bus::assertDispatched(ProcessExportJob::class, fn (ProcessExportJob $job): bool => $job->exportId === $export->getKey());
});

test('it processes queued exports and stores downloadable artifacts', function (): void {
    $userId = DB::table('users')->insertGetId([
        'name'       => 'Process User',
        'email'      => 'process@example.com',
        'password'   => 'secret',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    collect([
        ['number' => 'ORD-2001', 'status' => 'pending', 'total' => '99.90', 'created_at' => now()->subDays(2)],
        ['number' => 'ORD-2002', 'status' => 'completed', 'total' => '149.50', 'created_at' => now()->subDay()],
        ['number' => 'ORD-2003', 'status' => 'processing', 'total' => '240.25', 'created_at' => now()],
    ])->each(fn (array $attributes): FakeExportRow => FakeExportRow::query()->create($attributes));

    $service = app(ExportService::class);
    $request = new ExportRequestData(
        name: 'Downloadable Export',
        exportable: TestOrderExportable::class,
        format: 'csv',
        columns: ['number', 'status', 'total'],
        recordIds: [],
        userId: $userId,
    );

    $export = $service->queue($request);

    (new ProcessExport($export->getKey()))->handle($service);

    $export->refresh();

    expect($export)
        ->status->toBe(ExportStatus::Completed)
        ->and($export->total_rows)->toBe(3)
        ->and($export->processed_rows)->toBe(3)
        ->and($export->artifact_path)->not->toBeNull()
        ->and(Storage::disk('public')->exists($export->artifact_path))->toBeTrue();

    $user = User::query()->find($userId);

    Notification::assertSentTo($user, ExportCompletedNotification::class, function (ExportCompletedNotification $notification) use ($user, $export): bool {
        $data = $notification->toArray($user);

        return $data['export_id'] === $export->getKey()
            && $data['format'] === $export->format
            && str_contains($data['download_url'], (string) $export->uuid);
    });
});

test('it marks exports as failed when processing throws an exception', function (): void {
    $userId = DB::table('users')->insertGetId([
        'name'       => 'Failing User',
        'email'      => 'failure@example.com',
        'password'   => 'secret',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    FakeExportRow::query()->create([
        'number'     => 'ORD-3001',
        'status'     => 'pending',
        'total'      => '15.00',
        'created_at' => now(),
    ]);

    app()->instance(FailingOrderExportable::class, new FailingOrderExportable);

    $service = app(ExportService::class);
    $request = new ExportRequestData(
        name: 'Broken Export',
        exportable: FailingOrderExportable::class,
        format: 'csv',
        columns: ['number'],
        recordIds: [],
        userId: $userId,
    );

    $export = $service->queue($request);

    (new ProcessExport($export->getKey()))->handle($service);

    $export->refresh();

    expect($export)
        ->status->toBe(ExportStatus::Failed)
        ->and($export->failure_reason)->toBe('broken export');

    $user = User::query()->find($userId);

    Notification::assertSentTo($user, ExportFailedNotification::class);
});

final class FakeExportRow extends Model
{
    protected $table = 'fake_exports';

    protected $guarded = [];

    public $timestamps = false;
}

class TestOrderExportable implements Exportable
{
    public function name(): string
    {
        return 'Orders Export';
    }

    public function columns(): array
    {
        return [
            'number' => new ExportColumn('number', 'Number', 'number'),
            'status' => new ExportColumn('status', 'Status', 'status'),
            'total'  => new ExportColumn('total', 'Total', 'total'),
        ];
    }

    public function defaultColumns(): array
    {
        return ['number', 'status'];
    }

    public function query(array $options = []): Builder
    {
        return FakeExportRow::query();
    }

    public function fileName(Export $export): string
    {
        return 'orders-export';
    }

    public function map(Model $model, array $columns): array
    {
        return collect($columns)
            ->map(fn (ExportColumn $column): string => $column->resolve($model))
            ->values()
            ->all();
    }
}

final class FailingOrderExportable extends TestOrderExportable
{
    public function map(Model $model, array $columns): array
    {
        throw new \RuntimeException('broken export');
    }
}
