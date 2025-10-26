<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Campaign;
use App\Models\CampaignConversion;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

final class CampaignConversionTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_configuration_includes_core_columns(): void
    {
        $model = new CampaignConversion();

        $this->assertContains('campaign_id', $model->getFillable());
        $this->assertContains('conversion_type', $model->getFillable());
        $this->assertContains('conversion_value', $model->getFillable());
        $this->assertContains('campaign_name', $model->getFillable());
    }

    public function test_casts_configuration_preserves_expected_types(): void
    {
        $casts = (new CampaignConversion())->getCasts();

        $this->assertSame('decimal:2', $casts['conversion_value'] ?? null);
        $this->assertSame('array', $casts['conversion_data'] ?? null);
        $this->assertSame('datetime', $casts['converted_at'] ?? null);
        $this->assertSame('boolean', $casts['is_verified'] ?? null);
    }

    public function test_scope_ordered_by_name_sorts_by_campaign_name(): void
    {
        $alpha = CampaignConversion::factory()->create(['campaign_name' => 'Alpha Conversion']);
        $zulu = CampaignConversion::factory()->create(['campaign_name' => 'Zulu Conversion']);

        $orderedNames = CampaignConversion::query()
            ->orderedByName()
            ->pluck('campaign_name');

        $this->assertInstanceOf(Collection::class, $orderedNames);
        $this->assertSame([
            $alpha->campaign_name,
            $zulu->campaign_name,
        ], $orderedNames->all());
    }

    public function test_campaign_relationship_returns_belongs_to(): void
    {
        $campaign = Campaign::factory()->create();
        $conversion = CampaignConversion::factory()->for($campaign)->create();

        $this->assertInstanceOf(BelongsTo::class, $conversion->campaign());
        $this->assertTrue($conversion->campaign->is($campaign));
    }
}
