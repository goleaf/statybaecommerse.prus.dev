<?php declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\Campaign;
use App\Models\Category;
use App\Models\Channel;
use App\Models\CustomerGroup;
use App\Models\Product;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class CampaignResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $user->assignRole('administrator');
        $this->actingAs($user);
    }

    public function test_can_list_campaigns(): void
    {
        $campaigns = Campaign::factory()->count(3)->create();

        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\ListCampaigns::class)
            ->assertCanSeeTableRecords($campaigns);
    }

    public function test_can_create_campaign(): void
    {
        $channel = Channel::factory()->create();
        $zone = Zone::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create();
        $customerGroup = CustomerGroup::factory()->create();

        $newCampaign = Campaign::factory()->make([
            'channel_id' => $channel->id,
            'zone_id' => $zone->id,
        ]);

        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\CreateCampaign::class)
            ->fillForm([
                'name' => $newCampaign->name,
                'slug' => $newCampaign->slug,
                'description' => $newCampaign->description,
                'type' => $newCampaign->type,
                'status' => $newCampaign->status,
                'start_date' => $newCampaign->start_date,
                'end_date' => $newCampaign->end_date,
                'budget' => $newCampaign->budget,
                'budget_limit' => $newCampaign->budget_limit,
                'channel_id' => $channel->id,
                'zone_id' => $zone->id,
                'display_priority' => $newCampaign->display_priority,
                'max_uses' => $newCampaign->max_uses,
                'subject' => $newCampaign->subject,
                'content' => $newCampaign->content,
                'cta_text' => $newCampaign->cta_text,
                'cta_url' => $newCampaign->cta_url,
                'target_audience' => $newCampaign->target_audience,
                'is_featured' => $newCampaign->is_featured,
                'send_notifications' => $newCampaign->send_notifications,
                'track_conversions' => $newCampaign->track_conversions,
                'auto_start' => $newCampaign->auto_start,
                'auto_end' => $newCampaign->auto_end,
                'auto_pause_on_budget' => $newCampaign->auto_pause_on_budget,
                'meta_title' => $newCampaign->meta_title,
                'meta_description' => $newCampaign->meta_description,
                'social_media_ready' => $newCampaign->social_media_ready,
                'target_categories' => [$category->id],
                'target_products' => [$product->id],
                'target_customer_groups' => [$customerGroup->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('discount_campaigns', [
            'name' => $newCampaign->name,
            'slug' => $newCampaign->slug,
            'type' => $newCampaign->type,
            'status' => $newCampaign->status,
            'channel_id' => $channel->id,
            'zone_id' => $zone->id,
        ]);

        $campaign = Campaign::where('slug', $newCampaign->slug)->first();
        $this->assertTrue($campaign->targetCategories->contains($category));
        $this->assertTrue($campaign->targetProducts->contains($product));
        $this->assertTrue($campaign->targetCustomerGroups->contains($customerGroup));
    }

    public function test_can_view_campaign(): void
    {
        $campaign = Campaign::factory()->create();

        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\ViewCampaign::class, [
            'record' => $campaign->getRouteKey(),
        ])
            ->assertCanSeeRecord($campaign);
    }

    public function test_can_edit_campaign(): void
    {
        $campaign = Campaign::factory()->create();
        $channel = Channel::factory()->create();

        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\EditCampaign::class, [
            'record' => $campaign->getRouteKey(),
        ])
            ->fillForm([
                'name' => 'Updated Campaign Name',
                'channel_id' => $channel->id,
                'budget' => 2000.0,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('discount_campaigns', [
            'id' => $campaign->id,
            'name' => 'Updated Campaign Name',
            'channel_id' => $channel->id,
            'budget' => 2000.0,
        ]);
    }

    public function test_can_delete_campaign(): void
    {
        $campaign = Campaign::factory()->create();

        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\ListCampaigns::class)
            ->callTableAction('delete', $campaign);

        $this->assertSoftDeleted('discount_campaigns', [
            'id' => $campaign->id,
        ]);
    }

    public function test_can_activate_campaign(): void
    {
        $campaign = Campaign::factory()->create(['status' => 'draft']);

        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\ListCampaigns::class)
            ->callTableAction('activate', $campaign);

        $this->assertDatabaseHas('discount_campaigns', [
            'id' => $campaign->id,
            'status' => 'active',
        ]);
    }

    public function test_can_pause_campaign(): void
    {
        $campaign = Campaign::factory()->create(['status' => 'active']);

        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\ListCampaigns::class)
            ->callTableAction('pause', $campaign);

        $this->assertDatabaseHas('discount_campaigns', [
            'id' => $campaign->id,
            'status' => 'paused',
        ]);
    }

    public function test_can_filter_campaigns_by_status(): void
    {
        $activeCampaign = Campaign::factory()->create(['status' => 'active']);
        $draftCampaign = Campaign::factory()->create(['status' => 'draft']);

        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\ListCampaigns::class)
            ->filterTable('status', 'active')
            ->assertCanSeeTableRecords([$activeCampaign])
            ->assertCanNotSeeTableRecords([$draftCampaign]);
    }

    public function test_can_filter_campaigns_by_type(): void
    {
        $emailCampaign = Campaign::factory()->create(['type' => 'email']);
        $smsCampaign = Campaign::factory()->create(['type' => 'sms']);

        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\ListCampaigns::class)
            ->filterTable('type', 'email')
            ->assertCanSeeTableRecords([$emailCampaign])
            ->assertCanNotSeeTableRecords([$smsCampaign]);
    }

    public function test_can_filter_campaigns_by_channel(): void
    {
        $channel = Channel::factory()->create();
        $campaignWithChannel = Campaign::factory()->create(['channel_id' => $channel->id]);
        $campaignWithoutChannel = Campaign::factory()->create(['channel_id' => null]);

        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\ListCampaigns::class)
            ->filterTable('channel_id', $channel->id)
            ->assertCanSeeTableRecords([$campaignWithChannel])
            ->assertCanNotSeeTableRecords([$campaignWithoutChannel]);
    }

    public function test_can_filter_campaigns_by_featured(): void
    {
        $featuredCampaign = Campaign::factory()->create(['is_featured' => true]);
        $regularCampaign = Campaign::factory()->create(['is_featured' => false]);

        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\ListCampaigns::class)
            ->filterTable('is_featured', true)
            ->assertCanSeeTableRecords([$featuredCampaign])
            ->assertCanNotSeeTableRecords([$regularCampaign]);
    }

    public function test_can_filter_active_campaigns(): void
    {
        $activeCampaign = Campaign::factory()->create([
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);
        $inactiveCampaign = Campaign::factory()->create(['status' => 'paused']);

        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\ListCampaigns::class)
            ->filterTable('active')
            ->assertCanSeeTableRecords([$activeCampaign])
            ->assertCanNotSeeTableRecords([$inactiveCampaign]);
    }

    public function test_can_filter_scheduled_campaigns(): void
    {
        $scheduledCampaign = Campaign::factory()->create(['status' => 'scheduled']);
        $activeCampaign = Campaign::factory()->create(['status' => 'active']);

        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\ListCampaigns::class)
            ->filterTable('scheduled')
            ->assertCanSeeTableRecords([$scheduledCampaign])
            ->assertCanNotSeeTableRecords([$activeCampaign]);
    }

    public function test_can_filter_expired_campaigns(): void
    {
        $expiredCampaign = Campaign::factory()->create([
            'status' => 'active',
            'ends_at' => now()->subDay(),
        ]);
        $activeCampaign = Campaign::factory()->create([
            'status' => 'active',
            'ends_at' => now()->addDay(),
        ]);

        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\ListCampaigns::class)
            ->filterTable('expired')
            ->assertCanSeeTableRecords([$expiredCampaign])
            ->assertCanNotSeeTableRecords([$activeCampaign]);
    }

    public function test_can_filter_featured_campaigns(): void
    {
        $featuredCampaign = Campaign::factory()->create(['is_featured' => true]);
        $regularCampaign = Campaign::factory()->create(['is_featured' => false]);

        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\ListCampaigns::class)
            ->filterTable('featured')
            ->assertCanSeeTableRecords([$featuredCampaign])
            ->assertCanNotSeeTableRecords([$regularCampaign]);
    }

    public function test_can_bulk_activate_campaigns(): void
    {
        $campaigns = Campaign::factory()->count(3)->create(['status' => 'draft']);

        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\ListCampaigns::class)
            ->callTableBulkAction('activate', $campaigns);

        foreach ($campaigns as $campaign) {
            $this->assertDatabaseHas('discount_campaigns', [
                'id' => $campaign->id,
                'status' => 'active',
            ]);
        }
    }

    public function test_can_bulk_pause_campaigns(): void
    {
        $campaigns = Campaign::factory()->count(3)->create(['status' => 'active']);

        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\ListCampaigns::class)
            ->callTableBulkAction('pause', $campaigns);

        foreach ($campaigns as $campaign) {
            $this->assertDatabaseHas('discount_campaigns', [
                'id' => $campaign->id,
                'status' => 'paused',
            ]);
        }
    }

    public function test_can_bulk_delete_campaigns(): void
    {
        $campaigns = Campaign::factory()->count(3)->create();

        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\ListCampaigns::class)
            ->callTableBulkAction('delete', $campaigns);

        foreach ($campaigns as $campaign) {
            $this->assertSoftDeleted('discount_campaigns', [
                'id' => $campaign->id,
            ]);
        }
    }

    public function test_can_search_campaigns(): void
    {
        $searchableCampaign = Campaign::factory()->create(['name' => 'Searchable Campaign']);
        $otherCampaign = Campaign::factory()->create(['name' => 'Other Campaign']);

        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\ListCampaigns::class)
            ->searchTable('Searchable')
            ->assertCanSeeTableRecords([$searchableCampaign])
            ->assertCanNotSeeTableRecords([$otherCampaign]);
    }

    public function test_can_sort_campaigns_by_created_at(): void
    {
        $olderCampaign = Campaign::factory()->create(['created_at' => now()->subDay()]);
        $newerCampaign = Campaign::factory()->create(['created_at' => now()]);

        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\ListCampaigns::class)
            ->sortTable('created_at', 'desc')
            ->assertCanSeeTableRecords([$newerCampaign, $olderCampaign]);
    }

    public function test_form_validation_works(): void
    {
        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\CreateCampaign::class)
            ->fillForm([
                'name' => '',  // Required field
                'type' => 'invalid_type',  // Invalid option
            ])
            ->call('create')
            ->assertHasFormErrors(['name', 'type']);
    }

    public function test_slug_is_auto_generated_from_name(): void
    {
        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\CreateCampaign::class)
            ->fillForm([
                'name' => 'Test Campaign Name',
                'type' => 'email',
                'status' => 'draft',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('discount_campaigns', [
            'name' => 'Test Campaign Name',
            'slug' => 'test-campaign-name',
        ]);
    }

    public function test_end_date_must_be_after_start_date(): void
    {
        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\CreateCampaign::class)
            ->fillForm([
                'name' => 'Test Campaign',
                'type' => 'email',
                'status' => 'draft',
                'start_date' => now()->addDay(),
                'end_date' => now()->subDay(),
            ])
            ->call('create')
            ->assertHasFormErrors(['end_date']);
    }

    public function test_budget_must_be_numeric(): void
    {
        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\CreateCampaign::class)
            ->fillForm([
                'name' => 'Test Campaign',
                'type' => 'email',
                'status' => 'draft',
                'budget' => 'invalid',
            ])
            ->call('create')
            ->assertHasFormErrors(['budget']);
    }

    public function test_budget_must_be_minimum_zero(): void
    {
        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\CreateCampaign::class)
            ->fillForm([
                'name' => 'Test Campaign',
                'type' => 'email',
                'status' => 'draft',
                'budget' => -100,
            ])
            ->call('create')
            ->assertHasFormErrors(['budget']);
    }

    public function test_slug_must_be_unique(): void
    {
        $existingCampaign = Campaign::factory()->create(['slug' => 'existing-slug']);

        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\CreateCampaign::class)
            ->fillForm([
                'name' => 'Test Campaign',
                'slug' => 'existing-slug',
                'type' => 'email',
                'status' => 'draft',
            ])
            ->call('create')
            ->assertHasFormErrors(['slug']);
    }

    public function test_slug_must_be_alpha_dash(): void
    {
        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\CreateCampaign::class)
            ->fillForm([
                'name' => 'Test Campaign',
                'slug' => 'invalid slug!',
                'type' => 'email',
                'status' => 'draft',
            ])
            ->call('create')
            ->assertHasFormErrors(['slug']);
    }

    public function test_cta_url_must_be_valid_url(): void
    {
        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\CreateCampaign::class)
            ->fillForm([
                'name' => 'Test Campaign',
                'type' => 'email',
                'status' => 'draft',
                'cta_url' => 'invalid-url',
            ])
            ->call('create')
            ->assertHasFormErrors(['cta_url']);
    }

    public function test_can_restore_deleted_campaign(): void
    {
        $campaign = Campaign::factory()->create();
        $campaign->delete();

        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\ListCampaigns::class)
            ->filterTable('trashed')
            ->callTableAction('restore', $campaign);

        $this->assertDatabaseHas('discount_campaigns', [
            'id' => $campaign->id,
            'deleted_at' => null,
        ]);
    }

    public function test_can_force_delete_campaign(): void
    {
        $campaign = Campaign::factory()->create();
        $campaign->delete();

        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\ListCampaigns::class)
            ->filterTable('trashed')
            ->callTableAction('forceDelete', $campaign);

        $this->assertDatabaseMissing('discount_campaigns', [
            'id' => $campaign->id,
        ]);
    }
}
