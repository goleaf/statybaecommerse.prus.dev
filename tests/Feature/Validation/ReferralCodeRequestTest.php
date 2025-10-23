<?php

declare(strict_types=1);

namespace Tests\Feature\Validation;

use App\Models\ReferralCode;
use App\Models\User;
use Tests\TestCase;

final class ReferralCodeRequestTest extends TestCase
{
    public function test_store_requires_title_returns_422(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->postJson('/referral-codes', [])
            ->assertStatus(422);
    }

    public function test_update_with_invalid_payload_returns_422(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $code = ReferralCode::factory()->create(['user_id' => $user->getKey()]);

        $this->putJson("/referral-codes/{$code->getKey()}", [
            'usage_limit' => -1,
        ])->assertStatus(422);
    }
}

