<?php

declare(strict_types=1);

use App\Enums\ExportStatus;
use App\Models\Export;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

beforeEach(function (): void {
    Storage::fake('public');
    config()->set('export.disk', 'public');
});

test('it downloads completed export artifacts via signed url', function (): void {
    $artifactPath = 'exports/orders.csv';
    Storage::disk('public')->put($artifactPath, "number,status\n1001,paid");

    $export = Export::factory()->create([
        'status'            => ExportStatus::Completed,
        'format'            => 'csv',
        'artifact_disk'     => 'public',
        'artifact_path'     => $artifactPath,
        'artifact_filename' => 'orders.csv',
    ]);

    $url = URL::temporarySignedRoute('api.exports.download', now()->addMinutes(5), ['export' => $export]);

    $response = $this->get($url);

    $response->assertOk();
    $response->assertHeader('content-disposition', 'attachment; filename="orders.csv"');
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    $response->assertSee('number');
});

test('it returns not found for missing or incomplete exports', function (): void {
    $queuedExport = Export::factory()->create([
        'status' => ExportStatus::Queued,
        'format' => 'csv',
    ]);

    $queuedUrl = URL::temporarySignedRoute('api.exports.download', now()->addMinutes(5), ['export' => $queuedExport]);
    $this->get($queuedUrl)->assertNotFound();

    $missingExport = Export::factory()->create([
        'status'            => ExportStatus::Completed,
        'artifact_disk'     => 'public',
        'artifact_path'     => 'exports/missing.csv',
        'artifact_filename' => 'missing.csv',
    ]);

    $missingUrl = URL::temporarySignedRoute('api.exports.download', now()->addMinutes(5), ['export' => $missingExport]);
    $this->get($missingUrl)->assertNotFound();
});
