<?php

declare(strict_types=1);

use App\Models\FailedJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

// Keep database state isolated for each scenario; the Pest bootstrap wires the Laravel TestCase globally.
uses(RefreshDatabase::class);

it('derives the job name from the payload display name', function (): void {
    // Create a failed job payload that contains the display name metadata.
    $failedJob = FailedJob::factory()
        ->withDisplayName('App\\Jobs\\SendInvoiceJob')
        ->create([
            'connection' => 'redis',
            'queue'      => 'default',
            'failed_at'  => Carbon::parse('2024-01-01 10:00:00'),
        ]);

    // The accessor should expose a friendly job name derived from the payload.
    expect($failedJob->job_name)->toBe('App\\Jobs\\SendInvoiceJob');
});

it('falls back to unknown when the payload is missing a display name', function (): void {
    // Store a failed job with an empty payload to verify the graceful fallback.
    $failedJob = FailedJob::factory()
        ->withoutDisplayName()
        ->create([
            'connection' => 'redis',
            'queue'      => 'default',
            'exception'  => 'Example exception stack trace.',
            'failed_at'  => Carbon::parse('2024-01-01 11:00:00'),
        ]);

    // Because the payload is missing the display name, the accessor should return the fallback label.
    expect($failedJob->job_name)->toBe('unknown');
});

it('orders records by the latest failure timestamp when using the ordered scope', function (): void {
    // Create two failed jobs with unique timestamps to validate the ordering scope.
    $olderFailure = FailedJob::factory()
        ->withoutDisplayName()
        ->create([
            'exception' => 'Older failure',
            'failed_at' => Carbon::parse('2024-01-01 10:00:00'),
        ]);

    $newerFailure = FailedJob::factory()
        ->withoutDisplayName()
        ->create([
            'exception' => 'Newer failure',
            'failed_at' => Carbon::parse('2024-01-01 12:00:00'),
        ]);

    // The scope should return the newer failure first.
    $firstFailure = FailedJob::query()->orderedByLatest()->first();

    expect($firstFailure?->is($newerFailure))->toBeTrue();
    expect($firstFailure?->is($olderFailure))->toBeFalse();
});

it('casts the failed_at attribute to a Carbon instance', function (): void {
    // Create a failed job record to ensure the cast is applied automatically.
    $failedJob = FailedJob::factory()
        ->withoutDisplayName()
        ->create([
            'exception' => 'Cast verification failure',
            'failed_at' => Carbon::parse('2024-01-01 13:00:00'),
        ]);

    // The attribute should be an instance of Carbon after retrieval.
    expect($failedJob->failed_at)->toBeInstanceOf(Carbon::class);
});
