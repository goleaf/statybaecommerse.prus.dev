<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class UserPrivacySettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_user_can_update_privacy_settings_and_audit_is_recorded(): void
    {
        $user = User::factory()->create([
            'password'         => Hash::make('secret-password'),
            'privacy_settings' => ['personalization' => true],
        ]);

        $payload = [
            'privacy_settings' => ['personalization' => false, 'newsletter' => false],
        ];

        $response = $this->actingAs($user)
            ->post(route('users.privacy.update'), $payload);

        $response->assertRedirect(route('users.profile'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertSame($payload['privacy_settings'], $user->getAttribute('privacy_settings'));

        $this->assertDatabaseHas('admin_activity_logs', [
            'user_id' => $user->getKey(),
            'action'  => 'privacy_settings_updated',
        ]);
    }
}
