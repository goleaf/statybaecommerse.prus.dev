<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\User;
use App\Support\Storage\SecureStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

final class UserAvatarUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_avatar_upload_rejects_disallowed_mime_type(): void
    {
        // Ensure we do not touch the real filesystem during the test run.
        $disk = SecureStorage::disk();
        Storage::fake($disk);

        $user = User::factory()->create();

        $response = $this->withSession(['_token' => 'test-token'])
            ->actingAs($user)
            ->post('/user/avatar', [
                '_token' => 'test-token',
                'avatar' => UploadedFile::fake()->create('avatar.txt', 10, 'text/plain'),
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('avatar');
    }

    public function test_avatar_upload_sanitizes_filename_and_prevents_path_traversal(): void
    {
        // Fake the secure disk so assertions can inspect the stored path safely.
        $disk = SecureStorage::disk();
        Storage::fake($disk);

        $user = User::factory()->create();

        $response = $this->withSession(['_token' => 'test-token'])
            ->actingAs($user)
            ->post('/user/avatar', [
                '_token' => 'test-token',
                // Simulate a malicious filename that attempts to break out of the avatars directory.
                'avatar' => UploadedFile::fake()->image('../..//sneaky.png', 320, 320)->size(512),
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $user->refresh();
        $this->assertNotNull($user->avatar_url);
        $this->assertStringNotContainsString('..', $user->avatar_url);
        $this->assertTrue(Str::startsWith($user->avatar_url, 'avatars/'));

        Storage::disk($disk)->assertExists($user->avatar_url);
    }
}
