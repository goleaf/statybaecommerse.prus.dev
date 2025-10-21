<?php

declare(strict_types=1);

namespace Tests\Unit\Observers;

use App\Models\DiscountCode;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserAttributionObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sets_authenticated_user_ids_on_models(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $discountCode = DiscountCode::factory()
            ->state([
                'created_by' => null,
                'updated_by' => null,
            ])
            ->create();

        $this->assertSame($user->id, $discountCode->created_by);
        $this->assertSame($user->id, $discountCode->updated_by);

        $editor = User::factory()->create();
        $this->actingAs($editor);

        $discountCode->update(['name' => 'Updated Name']);

        $this->assertSame($editor->id, $discountCode->fresh()->updated_by);
    }

    public function test_it_falls_back_to_system_user_in_console_context(): void
    {
        $systemUser = User::factory()->create();

        config()->set('attribution.system_user_id', $systemUser->id);

        $setting = SystemSetting::factory()
            ->state([
                'updated_by' => null,
            ])
            ->create();

        $this->assertSame($systemUser->id, $setting->updated_by);

        $setting->update(['name' => 'Renamed Setting']);

        $this->assertSame($systemUser->id, $setting->fresh()->updated_by);
    }
}
