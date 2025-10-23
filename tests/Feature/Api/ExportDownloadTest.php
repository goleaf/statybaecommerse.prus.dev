<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Export;
use App\Services\Export\ExportFormat;
use App\Services\Export\ExportService;
use App\Services\Export\ExportStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ExportDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_download_returns_file(): void
    {
        Storage::fake(config('export.disk'));

        $export = Export::factory()
            ->completed('exports/example.csv')
            ->create([
                'format' => ExportFormat::Csv,
                'status' => ExportStatus::Completed,
            ]);

        Storage::disk(config('export.disk'))->put('exports/example.csv', 'header,rows');

        /** @var ExportService $service */
        $service = app(ExportService::class);
        $url = $service->makeSignedDownloadUrl($export, now()->addMinutes(5));

        $response = $this->get($url);

        $response->assertOk();
        $response->assertHeader('content-disposition', 'attachment; filename="'.$export->name.'.'.$export->format->extension().'"');
    }
}
