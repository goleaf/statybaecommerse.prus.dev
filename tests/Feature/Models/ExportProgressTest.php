<?php

declare(strict_types=1);

use App\Enums\ExportStatus;
use App\Models\Export;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    Schema::dropIfExists('exports');

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
        $table->timestamp('requested_at')->nullable();
        $table->timestamp('completed_at')->nullable();
        $table->timestamp('failed_at')->nullable();
        $table->text('failure_reason')->nullable();
        $table->unsignedBigInteger('requested_by')->nullable();
        $table->timestamps();
    });
});

it('clamps progress percentage when processed rows exceed total rows', function (): void {
    $export = Export::query()->create([
        'name'            => 'Progress export',
        'format'          => 'csv',
        'status'          => ExportStatus::Processing,
        'exportable_type' => Export::class,
        'columns'         => ['id'],
        'total_rows'      => 100,
        'processed_rows'  => 250,
        'requested_at'    => now(),
    ]);

    expect($export->fresh()->progress_percentage)->toBe(100);
});

it('clamps updateProgress to total rows', function (): void {
    $export = Export::query()->create([
        'name'            => 'Clamp export',
        'format'          => 'csv',
        'status'          => ExportStatus::Processing,
        'exportable_type' => Export::class,
        'columns'         => ['id'],
        'total_rows'      => 100,
        'processed_rows'  => 0,
        'requested_at'    => now(),
    ]);

    $result = $export->updateProgress(250);

    expect($result)->toBeTrue()
        ->and($export->fresh()->processed_rows)->toBe(100);
});
