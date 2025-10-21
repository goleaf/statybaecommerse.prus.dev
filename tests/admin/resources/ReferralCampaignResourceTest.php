<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ReferralCampaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Number;
use Livewire\Livewire;
use Tests\TestCase;

final class ReferralCampaignResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_can_list_referral_campaigns_with_custom_columns(): void
    {
        $campaign = ReferralCampaign::factory()->create([
            'name'                   => ['lt' => 'Pavasario kampanija', 'en' => 'Spring Campaign'],
            'description'            => ['lt' => 'Pavasario aprašymas', 'en' => 'Spring description'],
            'is_active'              => true,
            'start_date'             => now()->subDay(),
            'end_date'               => now()->addMonth(),
            'reward_amount'          => 75.5,
            'reward_type'            => 'discount',
            'max_referrals_per_user' => 5,
            'max_total_referrals'    => 100,
            'conditions'             => ['order_minimum' => '100'],
            'metadata'               => ['banner' => 'spring-campaign'],
        ]);

        Livewire::test(\App\Filament\Resources\ReferralCampaignResource\Pages\ListReferralCampaigns::class)
            ->assertCanSeeTableRecords([$campaign])
            ->assertCanRenderTableColumn('reward_amount')
            ->assertCanRenderTableColumn('reward_type')
            ->assertCanRenderTableColumn('max_referrals_per_user')
            ->assertCanRenderTableColumn('max_total_referrals')
            ->assertCanRenderTableColumn('is_active')
            ->assertSee(Number::currency($campaign->reward_amount, 'EUR', locale: app()->getLocale()))
            ->assertSee($campaign->reward_type)
            ->assertSee((string) $campaign->max_referrals_per_user)
            ->assertSee((string) $campaign->max_total_referrals);
    }

    public function test_can_create_referral_campaign_with_conditions_and_metadata(): void
    {
        $formData = [
            'name'                   => ['lt' => 'Žiemos kampanija', 'en' => 'Winter Campaign'],
            'description'            => ['lt' => 'Žiemos aprašymas', 'en' => 'Winter description'],
            'is_active'              => true,
            'start_date'             => now()->toDateString(),
            'end_date'               => now()->addMonth()->toDateString(),
            'reward_amount'          => 20,
            'reward_type'            => 'credit',
            'max_referrals_per_user' => 10,
            'max_total_referrals'    => 500,
            'conditions'             => [
                'order_minimum' => '150',
                'country'       => 'LT',
            ],
            'metadata' => [
                'banner'  => 'winter-launch',
                'segment' => 'loyal-customers',
            ],
        ];

        Livewire::test(\App\Filament\Resources\ReferralCampaignResource\Pages\CreateReferralCampaign::class)
            ->fillForm($formData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('referral_campaigns', [
            'name->en'                  => 'Winter Campaign',
            'reward_type'               => 'credit',
            'max_referrals_per_user'    => 10,
            'max_total_referrals'       => 500,
            'conditions->order_minimum' => '150',
            'metadata->banner'          => 'winter-launch',
        ]);
    }

    public function test_can_view_referral_campaign_details(): void
    {
        $campaign = ReferralCampaign::factory()->create([
            'name'                   => ['lt' => 'Rudens kampanija', 'en' => 'Autumn Campaign'],
            'description'            => ['lt' => 'Rudens aprašymas', 'en' => 'Autumn description'],
            'is_active'              => true,
            'start_date'             => now()->subDays(2),
            'end_date'               => now()->addDays(10),
            'reward_amount'          => 35,
            'reward_type'            => 'points',
            'max_referrals_per_user' => 8,
            'max_total_referrals'    => 250,
            'conditions'             => ['order_minimum' => '200'],
            'metadata'               => ['landing_page' => 'autumn-referrals'],
        ]);

        Livewire::test(\App\Filament\Resources\ReferralCampaignResource\Pages\ViewReferralCampaign::class, [
            'record' => $campaign->getRouteKey(),
        ])
            ->assertCanSeeRecord($campaign)
            ->assertSee('Autumn Campaign')
            ->assertSee('points')
            ->assertSee('autumn-referrals');
    }

    public function test_can_edit_referral_campaign(): void
    {
        $campaign = ReferralCampaign::factory()->create([
            'name'                   => ['lt' => 'Sena kampanija', 'en' => 'Old Campaign'],
            'description'            => ['lt' => 'Senas aprašymas', 'en' => 'Old description'],
            'is_active'              => false,
            'start_date'             => now()->subWeek(),
            'end_date'               => now()->addWeek(),
            'reward_amount'          => 10,
            'reward_type'            => 'gift',
            'max_referrals_per_user' => 3,
            'max_total_referrals'    => 50,
            'conditions'             => ['order_minimum' => '100'],
            'metadata'               => ['banner' => 'old'],
        ]);

        Livewire::test(\App\Filament\Resources\ReferralCampaignResource\Pages\EditReferralCampaign::class, [
            'record' => $campaign->getRouteKey(),
        ])
            ->fillForm([
                'name'                   => ['lt' => 'Atnaujinta kampanija', 'en' => 'Updated Campaign'],
                'description'            => ['lt' => 'Atnaujintas aprašymas', 'en' => 'Updated description'],
                'is_active'              => true,
                'reward_amount'          => 45,
                'reward_type'            => 'discount',
                'max_referrals_per_user' => null,
                'max_total_referrals'    => null,
                'conditions'             => ['order_minimum' => '250', 'country' => 'LV'],
                'metadata'               => ['banner' => 'updated', 'segment' => 'vip'],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('referral_campaigns', [
            'id'                  => $campaign->id,
            'name->en'            => 'Updated Campaign',
            'reward_amount'       => 45,
            'reward_type'         => 'discount',
            'conditions->country' => 'LV',
            'metadata->segment'   => 'vip',
        ]);
    }

    public function test_can_filter_referral_campaigns_by_status_and_reward_type(): void
    {
        $activeCampaign = ReferralCampaign::factory()->create([
            'name'          => ['lt' => 'Aktyvi kampanija', 'en' => 'Active Campaign'],
            'description'   => ['lt' => 'Aktyvi', 'en' => 'Active'],
            'is_active'     => true,
            'start_date'    => now()->subDay(),
            'end_date'      => now()->addDay(),
            'reward_type'   => 'points',
            'reward_amount' => 15,
        ]);

        $inactiveCampaign = ReferralCampaign::factory()->create([
            'name'          => ['lt' => 'Neaktyvi kampanija', 'en' => 'Inactive Campaign'],
            'description'   => ['lt' => 'Neaktyvi', 'en' => 'Inactive'],
            'is_active'     => false,
            'start_date'    => null,
            'end_date'      => null,
            'reward_type'   => 'credit',
            'reward_amount' => 12,
        ]);

        $discountCampaign = ReferralCampaign::factory()->create([
            'name'          => ['lt' => 'Nuolaidų kampanija', 'en' => 'Discount Campaign'],
            'description'   => ['lt' => 'Nuolaida', 'en' => 'Discount'],
            'is_active'     => true,
            'start_date'    => now()->subDay(),
            'end_date'      => now()->addDay(),
            'reward_type'   => 'discount',
            'reward_amount' => 22,
        ]);

        Livewire::test(\App\Filament\Resources\ReferralCampaignResource\Pages\ListReferralCampaigns::class)
            ->filterTable('is_active', 'true')
            ->assertCanSeeTableRecords([$activeCampaign, $discountCampaign])
            ->assertCanNotSeeTableRecords([$inactiveCampaign]);

        Livewire::test(\App\Filament\Resources\ReferralCampaignResource\Pages\ListReferralCampaigns::class)
            ->filterTable('reward_type', 'credit')
            ->assertCanSeeTableRecords([$inactiveCampaign])
            ->assertCanNotSeeTableRecords([$activeCampaign, $discountCampaign]);
    }
}
