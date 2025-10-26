<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Export;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * @coversNothing
 */
final class ExportDownloadControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_stream_completed_export(): void
    {
        // Fake the storage disk so no real files are touched during the test run.
        Storage::fake('public');

        $user = User::factory()->create();

        /** @var Export $export */
        $export = Export::factory()
            ->completed()
            ->create([
                'format'            => 'csv',
                'artifact_disk'     => 'public',
                'artifact_path'     => 'exports/report.csv',
                'artifact_filename' => 'report.csv',
                'requested_by'      => $user->getKey(),
            ]);

        // Seed the fake disk with a simple CSV payload that will be streamed back to the caller.
        Storage::disk('public')->put('exports/report.csv', "id,name\n1,Foo");

        $signedUrl = URL::temporarySignedRoute('api.exports.download', now()->addMinutes(5), ['export' => $export->uuid]);

        $response = $this->actingAs($user)->get($signedUrl);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertSame("id,name\n1,Foo", $response->streamedContent());
    }

    public function test_guest_cannot_download_user_bound_export(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create();

        $export = Export::factory()
            ->completed()
            ->create([
                'format'            => 'csv',
                'artifact_disk'     => 'public',
                'artifact_path'     => 'exports/report.csv',
                'artifact_filename' => 'report.csv',
                'requested_by'      => $owner->getKey(),
            ]);

        Storage::disk('public')->put('exports/report.csv', 'content');

        $signedUrl = URL::temporarySignedRoute('api.exports.download', now()->addMinutes(5), ['export' => $export->uuid]);

        $response = $this->get($signedUrl);

        $response->assertUnauthorized();
    }

    public function test_different_user_receives_forbidden_response(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $export = Export::factory()
            ->completed()
            ->create([
                'format'            => 'csv',
                'artifact_disk'     => 'public',
                'artifact_path'     => 'exports/report.csv',
                'artifact_filename' => 'report.csv',
                'requested_by'      => $owner->getKey(),
            ]);

        Storage::disk('public')->put('exports/report.csv', 'content');

        $signedUrl = URL::temporarySignedRoute('api.exports.download', now()->addMinutes(5), ['export' => $export->uuid]);

        $response = $this->actingAs($otherUser)->get($signedUrl);

        $response->assertForbidden();
    }

    public function test_missing_artifact_returns_not_found(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $export = Export::factory()
            ->completed()
            ->create([
                'format'            => 'csv',
                'artifact_disk'     => 'public',
                'artifact_path'     => 'exports/missing.csv',
                'artifact_filename' => 'missing.csv',
                'requested_by'      => $user->getKey(),
            ]);

        // Intentionally do not create the file on disk to mimic a pruned artifact scenario.

        $signedUrl = URL::temporarySignedRoute('api.exports.download', now()->addMinutes(5), ['export' => $export->uuid]);

        $response = $this->actingAs($user)->get($signedUrl);

        $response->assertNotFound();
    }
}
