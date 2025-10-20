<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\DiscountRedemption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\TestCase;

final class DiscountRedemptionPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_page_renders_successfully(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('frontend.discount-redemptions.index'));

        $response->assertOk();
    }

    public function test_create_page_renders_successfully(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('frontend.discount-redemptions.create'));

        $response->assertOk();
    }

    public function test_show_page_renders_successfully_for_owner(): void
    {
        $user = User::factory()->create();
        $redemption = DiscountRedemption::factory()->for($user)->create();

        $response = $this->actingAs($user)->get(route('frontend.discount-redemptions.show', $redemption));

        $response->assertOk();
    }
}
