<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Campaign;
use App\Models\CampaignView;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class CampaignViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes_are_locked_down(): void
    {
        // Ensure the mass-assignable columns remain intentional and predictable.
        $model = new CampaignView;

        $this->assertSame([
            'campaign_id',
            'session_id',
            'ip_address',
            'user_agent',
            'referer',
            'customer_id',
            'viewed_at',
        ], $model->getFillable());
    }

    public function test_timestamps_are_disabled(): void
    {
        // Campaign views rely on a single viewed_at column instead of created_at/updated_at.
        $model = new CampaignView;

        $this->assertFalse($model->timestamps);
    }

    public function test_viewed_at_casts_to_datetime_instance(): void
    {
        // Casting should promote the raw database value into a Carbon instance for convenience.
        $viewedAt = Carbon::create(2024, 1, 2, 12, 0, 0);

        $campaignView = CampaignView::factory()->create([
            'viewed_at' => $viewedAt,
        ]);

        $this->assertInstanceOf(Carbon::class, $campaignView->viewed_at);
        $this->assertTrue($campaignView->viewed_at->equalTo($viewedAt));
    }

    public function test_campaign_relationship_uses_belongs_to(): void
    {
        // Campaign views should point back to the originating campaign record.
        $campaignView = CampaignView::factory()->create();

        $this->assertInstanceOf(BelongsTo::class, $campaignView->campaign());
        $this->assertInstanceOf(Campaign::class, $campaignView->campaign);
    }

    public function test_customer_relationship_uses_belongs_to_with_user_model(): void
    {
        // A campaign view may belong to a logged-in customer stored in the users table.
        $campaignView = CampaignView::factory()->withCustomer()->create();

        $this->assertInstanceOf(BelongsTo::class, $campaignView->customer());
        $this->assertInstanceOf(User::class, $campaignView->customer);
    }
}
