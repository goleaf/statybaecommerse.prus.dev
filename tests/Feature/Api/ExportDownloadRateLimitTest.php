<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Enums\ExportStatus;
use App\Models\Export;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\Concerns\PreparesRateLimitTestDatabase;
use Tests\TestCase;

final class ExportDownloadRateLimitTest extends TestCase
{
    use PreparesRateLimitTestDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootRateLimitTestEnvironment();

        Schema::dropIfExists('exports');

        Schema::create('exports', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name')->nullable();
            $table->string('format');
            $table->string('status');
            $table->string('exportable_type')->nullable();
            $table->json('columns')->nullable();
            $table->json('exportable_options')->nullable();
            $table->unsignedBigInteger('total_rows')->nullable();
            $table->unsignedBigInteger('processed_rows')->nullable();
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
    }

    public function test_signed_export_download_allows_guest_access(): void
    {
        Storage::fake('public');

        $export = Export::factory()->create([
            'status'            => ExportStatus::Completed,
            'artifact_disk'     => 'public',
            'artifact_path'     => 'exports/report.csv',
            'artifact_filename' => 'report.csv',
        ]);

        Storage::disk('public')->put('exports/report.csv', 'csv-content');

        $url = URL::temporarySignedRoute('api.exports.download', now()->addMinutes(5), [
            'export' => $export,
        ]);

        $this->get($url, ['Accept' => 'application/json'])->assertOk();
    }

    public function test_signed_export_download_is_rate_limited(): void
    {
        Storage::fake('public');

        $export = Export::factory()->create([
            'status'            => ExportStatus::Completed,
            'artifact_disk'     => 'public',
            'artifact_path'     => 'exports/report.csv',
            'artifact_filename' => 'report.csv',
        ]);

        Storage::disk('public')->put('exports/report.csv', 'csv-content');

        $originalLimit = config('security.rate_limiting.api.exports');
        config(['security.rate_limiting.api.exports' => 1]);
        RateLimiter::clear('ip:127.0.0.1|exports');

        $url = URL::temporarySignedRoute('api.exports.download', now()->addMinutes(5), [
            'export' => $export,
        ]);

        try {
            $this->get($url, ['Accept' => 'application/json'])->assertOk();
            $this->get($url, ['Accept' => 'application/json'])->assertStatus(429);
        } finally {
            config(['security.rate_limiting.api.exports' => $originalLimit]);
            RateLimiter::clear('ip:127.0.0.1|exports');
        }
    }
}
