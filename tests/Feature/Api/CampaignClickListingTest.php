<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\CampaignClick;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CampaignClickListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_per_page_is_capped(): void
    {
        CampaignClick::factory()->count(120)->create();

        $response = $this->getJson('/campaign-clicks?per_page=500');

        $response->assertOk()
            ->assertJsonPath('meta.per_page', 100)
            ->assertJsonCount(100, 'data');
    }

    public function test_invalid_sort_returns_validation_error(): void
    {
        $response = $this->getJson('/campaign-clicks?sort=invalid:asc');

        $response->assertStatus(422);
    }

    public function test_empty_result_returns_empty_payload(): void
    {
        $response = $this->getJson('/campaign-clicks?filter[campaign_id]=999999');

        $response->assertOk()
            ->assertJsonPath('data', [])
            ->assertJsonPath('meta.total', 0);
    }

    public function test_last_page_indicators_are_correct(): void
    {
        CampaignClick::factory()->count(3)->create();

        $response = $this->getJson('/campaign-clicks?per_page=2&page=2&sort=clicked_at:desc');

        $response->assertOk()
            ->assertJsonPath('meta.page', 2)
            ->assertJsonPath('meta.total_pages', 2)
            ->assertJsonPath('links.next', null)
            ->assertJsonPath('links.prev', fn ($value) => is_string($value));
    }
}
