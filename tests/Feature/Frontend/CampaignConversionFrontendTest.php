<?php declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\CampaignConversion;
use App\Models\Campaign;
use App\Models\Order;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignConversionFrontendTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_campaign_conversions_index(): void
    {
        $conversions = CampaignConversion::factory()->count(3)->create();

        $response = $this->get('/campaign-conversions');

        $response->assertOk();
        $response->assertSee(__('campaign_conversions.pages.index.title'));
        $response->assertSee($conversions->first()->id);
    }

    public function test_can_filter_campaign_conversions_by_campaign(): void
    {
        $campaign1 = Campaign::factory()->create(['name' => 'Test Campaign 1']);
        $campaign2 = Campaign::factory()->create(['name' => 'Test Campaign 2']);

        $conversion1 = CampaignConversion::factory()->create(['campaign_id' => $campaign1->id]);
        $conversion2 = CampaignConversion::factory()->create(['campaign_id' => $campaign2->id]);

        $response = $this->get("/campaign-conversions?campaign_id={$campaign1->id}");

        $response->assertOk();
        $response->assertSee($conversion1->id);
        $response->assertDontSee($conversion2->id);
    }

    public function test_can_filter_campaign_conversions_by_type(): void
    {
        $purchaseConversion = CampaignConversion::factory()->create(['conversion_type' => 'purchase']);
        $signupConversion = CampaignConversion::factory()->create(['conversion_type' => 'signup']);

        $response = $this->get('/campaign-conversions?conversion_type=purchase');

        $response->assertOk();
        $response->assertSee($purchaseConversion->id);
        $response->assertDontSee($signupConversion->id);
    }

    public function test_can_filter_campaign_conversions_by_status(): void
    {
        $completedConversion = CampaignConversion::factory()->create(['status' => 'completed']);
        $pendingConversion = CampaignConversion::factory()->create(['status' => 'pending']);

        $response = $this->get('/campaign-conversions?status=completed');

        $response->assertOk();
        $response->assertSee($completedConversion->id);
        $response->assertDontSee($pendingConversion->id);
    }

    public function test_can_filter_campaign_conversions_by_device_type(): void
    {
        $mobileConversion = CampaignConversion::factory()->create(['device_type' => 'mobile']);
        $desktopConversion = CampaignConversion::factory()->create(['device_type' => 'desktop']);

        $response = $this->get('/campaign-conversions?device_type=mobile');

        $response->assertOk();
        $response->assertSee($mobileConversion->id);
        $response->assertDontSee($desktopConversion->id);
    }

    public function test_can_filter_campaign_conversions_by_date_range(): void
    {
        $recentConversion = CampaignConversion::factory()->create(['converted_at' => now()->subDays(3)]);
        $oldConversion = CampaignConversion::factory()->create(['converted_at' => now()->subDays(10)]);

        $response = $this->get('/campaign-conversions?date_from=' . now()->subDays(5)->format('Y-m-d'));

        $response->assertOk();
        $response->assertSee($recentConversion->id);
        $response->assertDontSee($oldConversion->id);
    }

    public function test_can_view_campaign_conversion_details(): void
    {
        $conversion = CampaignConversion::factory()->create();

        $response = $this->get("/campaign-conversions/{$conversion->id}");

        $response->assertOk();
        $response->assertSee(__('campaign_conversions.pages.show.title'));
        $response->assertSee($conversion->id);
        $response->assertSee($conversion->conversion_type);
        $response->assertSee($conversion->conversion_value);
    }

    public function test_can_view_create_campaign_conversion_form(): void
    {
        $response = $this->get('/campaign-conversions/create');

        $response->assertOk();
        $response->assertSee(__('campaign_conversions.pages.create.title'));
        $response->assertSee('campaign_id');
        $response->assertSee('conversion_type');
        $response->assertSee('conversion_value');
    }

    public function test_can_create_campaign_conversion(): void
    {
        $campaign = Campaign::factory()->create();

        $conversionData = [
            'campaign_id' => $campaign->id,
            'conversion_type' => 'purchase',
            'conversion_value' => 150.50,
            'status' => 'completed',
            'converted_at' => now()->format('Y-m-d H:i:s'),
            'source' => 'google',
            'medium' => 'cpc',
            'device_type' => 'desktop',
            'country' => 'LT',
            'city' => 'Vilnius',
        ];

        $response = $this->post('/campaign-conversions', $conversionData);

        $this->assertDatabaseHas('campaign_conversions', [
            'campaign_id' => $campaign->id,
            'conversion_type' => 'purchase',
            'conversion_value' => 150.50,
            'status' => 'completed',
            'source' => 'google',
            'medium' => 'cpc',
            'device_type' => 'desktop',
            'country' => 'LT',
            'city' => 'Vilnius',
        ]);

        $response->assertRedirect();
    }

    public function test_can_view_edit_campaign_conversion_form(): void
    {
        $conversion = CampaignConversion::factory()->create();

        $response = $this->get("/campaign-conversions/{$conversion->id}/edit");

        $response->assertOk();
        $response->assertSee(__('campaign_conversions.pages.edit.title'));
        $response->assertSee($conversion->conversion_type);
        $response->assertSee($conversion->conversion_value);
    }

    public function test_can_update_campaign_conversion(): void
    {
        $conversion = CampaignConversion::factory()->create([
            'conversion_value' => 100,
            'status' => 'pending',
        ]);

        $updateData = [
            'campaign_id' => $conversion->campaign_id,
            'conversion_type' => $conversion->conversion_type,
            'conversion_value' => 200,
            'status' => 'completed',
            'converted_at' => $conversion->converted_at->format('Y-m-d H:i:s'),
            'source' => 'facebook',
            'medium' => 'social',
        ];

        $response = $this->put("/campaign-conversions/{$conversion->id}", $updateData);

        $this->assertDatabaseHas('campaign_conversions', [
            'id' => $conversion->id,
            'conversion_value' => 200,
            'status' => 'completed',
            'source' => 'facebook',
            'medium' => 'social',
        ]);

        $response->assertRedirect();
    }

    public function test_can_delete_campaign_conversion(): void
    {
        $conversion = CampaignConversion::factory()->create();

        $response = $this->delete("/campaign-conversions/{$conversion->id}");

        $this->assertDatabaseMissing('campaign_conversions', [
            'id' => $conversion->id,
        ]);

        $response->assertRedirect();
    }

    public function test_can_export_campaign_conversions(): void
    {
        CampaignConversion::factory()->count(5)->create();

        $response = $this->get('/campaign-conversions/export/csv');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_can_get_analytics_data(): void
    {
        CampaignConversion::factory()->count(10)->create([
            'conversion_value' => 100,
            'converted_at' => now()->subDays(5),
        ]);

        $response = $this->get('/campaign-conversions/analytics/data');

        $response->assertOk();
        $response->assertJsonStructure([
            'total_conversions',
            'total_value',
            'average_value',
            'conversion_rate',
            'roi',
            'roas',
            'by_type',
            'by_status',
            'by_device',
            'by_source',
            'by_medium',
            'by_country',
            'daily_trends',
        ]);

        $data = $response->json();
        $this->assertEquals(10, $data['total_conversions']);
        $this->assertEquals(1000, $data['total_value']);
        $this->assertEquals(100, $data['average_value']);
    }

    public function test_analytics_data_filters_by_date_range(): void
    {
        CampaignConversion::factory()->create(['converted_at' => now()->subDays(5)]);
        CampaignConversion::factory()->create(['converted_at' => now()->subDays(15)]);

        $response = $this->get('/campaign-conversions/analytics/data?date_from=' . now()->subDays(10)->format('Y-m-d'));

        $response->assertOk();
        $data = $response->json();
        $this->assertEquals(1, $data['total_conversions']);
    }

    public function test_analytics_data_filters_by_campaign(): void
    {
        $campaign1 = Campaign::factory()->create();
        $campaign2 = Campaign::factory()->create();

        CampaignConversion::factory()->create(['campaign_id' => $campaign1->id]);
        CampaignConversion::factory()->create(['campaign_id' => $campaign2->id]);

        $response = $this->get("/campaign-conversions/analytics/data?campaign_id={$campaign1->id}");

        $response->assertOk();
        $data = $response->json();
        $this->assertEquals(1, $data['total_conversions']);
    }

    public function test_validation_works_for_required_fields(): void
    {
        $response = $this->post('/campaign-conversions', []);

        $response->assertSessionHasErrors(['campaign_id', 'conversion_type', 'conversion_value', 'converted_at']);
    }

    public function test_validation_works_for_numeric_fields(): void
    {
        $campaign = Campaign::factory()->create();

        $response = $this->post('/campaign-conversions', [
            'campaign_id' => $campaign->id,
            'conversion_type' => 'purchase',
            'conversion_value' => 'invalid',
            'converted_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertSessionHasErrors(['conversion_value']);
    }

    public function test_validation_works_for_existing_campaign(): void
    {
        $response = $this->post('/campaign-conversions', [
            'campaign_id' => 999999,
            'conversion_type' => 'purchase',
            'conversion_value' => 100,
            'converted_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertSessionHasErrors(['campaign_id']);
    }

    public function test_campaign_conversion_relationships_display_correctly(): void
    {
        $campaign = Campaign::factory()->create(['name' => 'Test Campaign']);
        $customer = Customer::factory()->create(['email' => 'test@example.com']);
        $order = Order::factory()->create();

        $conversion = CampaignConversion::factory()->create([
            'campaign_id' => $campaign->id,
            'customer_id' => $customer->id,
            'order_id' => $order->id,
        ]);

        $response = $this->get("/campaign-conversions/{$conversion->id}");

        $response->assertOk();
        $response->assertSee('Test Campaign');
        $response->assertSee('test@example.com');
        $response->assertSee($order->id);
    }

    public function test_analytics_cards_display_correct_data(): void
    {
        CampaignConversion::factory()->count(10)->create([
            'conversion_value' => 100,
            'converted_at' => now()->subDays(5),
        ]);

        $response = $this->get('/campaign-conversions');

        $response->assertOk();
        $response->assertSee('10'); // Total conversions
        $response->assertSee('€1,000.00'); // Total value
        $response->assertSee('€100.00'); // Average value
    }

    public function test_pagination_works_correctly(): void
    {
        CampaignConversion::factory()->count(25)->create();

        $response = $this->get('/campaign-conversions');

        $response->assertOk();
        $response->assertSee('25'); // Total count
        // Should show pagination links
        $response->assertSee('pagination');
    }

    public function test_empty_state_displays_correctly(): void
    {
        $response = $this->get('/campaign-conversions');

        $response->assertOk();
        $response->assertSee(__('campaign_conversions.messages.no_conversions'));
        $response->assertSee(__('campaign_conversions.actions.create_first'));
    }

    public function test_success_messages_display_after_operations(): void
    {
        $campaign = Campaign::factory()->create();

        // Test create success message
        $response = $this->post('/campaign-conversions', [
            'campaign_id' => $campaign->id,
            'conversion_type' => 'purchase',
            'conversion_value' => 100,
            'converted_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', __('campaign_conversions.messages.created_successfully'));

        $conversion = CampaignConversion::first();

        // Test update success message
        $response = $this->put("/campaign-conversions/{$conversion->id}", [
            'campaign_id' => $campaign->id,
            'conversion_type' => 'purchase',
            'conversion_value' => 200,
            'converted_at' => $conversion->converted_at->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', __('campaign_conversions.messages.updated_successfully'));

        // Test delete success message
        $response = $this->delete("/campaign-conversions/{$conversion->id}");

        $response->assertRedirect();
        $response->assertSessionHas('success', __('campaign_conversions.messages.deleted_successfully'));
    }
}

