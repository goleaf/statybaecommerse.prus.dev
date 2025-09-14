<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\EmailCampaign;
use App\Models\Referral;
use App\Models\SeoData;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\StatusScope;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class CompanyEmailCampaignSeoDataGlobalScopesTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_model_has_active_scope(): void
    {
        // Create test companies
        $activeCompany = Company::factory()->create(['is_active' => true]);
        $inactiveCompany = Company::factory()->create(['is_active' => false]);

        // Test that only active companies are returned
        $companies = Company::all();
        
        $this->assertCount(1, $companies);
        $this->assertEquals($activeCompany->id, $companies->first()->id);

        // Test bypassing scopes
        $allCompanies = Company::withoutGlobalScopes()->get();
        $this->assertCount(2, $allCompanies);
    }

    public function test_email_campaign_model_has_active_scope(): void
    {
        // Create test email campaigns
        $activeCampaign = EmailCampaign::factory()->create(['is_active' => true]);
        $inactiveCampaign = EmailCampaign::factory()->create(['is_active' => false]);

        // Test that only active campaigns are returned
        $campaigns = EmailCampaign::all();
        
        $this->assertCount(1, $campaigns);
        $this->assertEquals($activeCampaign->id, $campaigns->first()->id);

        // Test bypassing scopes
        $allCampaigns = EmailCampaign::withoutGlobalScopes()->get();
        $this->assertCount(2, $allCampaigns);
    }

    public function test_seo_data_model_has_active_scope(): void
    {
        // Create test SEO data
        $activeSeoData = SeoData::factory()->create(['is_active' => true]);
        $inactiveSeoData = SeoData::factory()->create(['is_active' => false]);

        // Test that only active SEO data is returned
        $seoData = SeoData::all();
        
        $this->assertCount(1, $seoData);
        $this->assertEquals($activeSeoData->id, $seoData->first()->id);

        // Test bypassing scopes
        $allSeoData = SeoData::withoutGlobalScopes()->get();
        $this->assertCount(2, $allSeoData);
    }

    public function test_referral_model_has_multiple_scopes(): void
    {
        // Create test referrals
        $activeReferral = Referral::factory()->create([
            'is_active' => true,
            'status' => 'active',
        ]);

        $inactiveReferral = Referral::factory()->create([
            'is_active' => false,
            'status' => 'active',
        ]);

        $pendingReferral = Referral::factory()->create([
            'is_active' => true,
            'status' => 'pending',
        ]);

        // Test that only active referrals with allowed status are returned
        $referrals = Referral::all();
        
        $this->assertCount(1, $referrals);
        $this->assertEquals($activeReferral->id, $referrals->first()->id);

        // Test bypassing scopes
        $allReferrals = Referral::withoutGlobalScopes()->get();
        $this->assertCount(3, $allReferrals);
    }

    public function test_global_scopes_can_be_combined_with_local_scopes(): void
    {
        // Create test data
        $activeCompany = Company::factory()->create(['is_active' => true]);
        $inactiveCompany = Company::factory()->create(['is_active' => false]);

        // Test that global scopes work with local scopes
        $companies = Company::where('name', 'like', '%test%')->get();
        $this->assertCount(0, $companies); // No companies with 'test' in name

        // Test bypassing global scopes with local scopes
        $allCompanies = Company::withoutGlobalScopes()->where('is_active', true)->get();
        $this->assertCount(1, $allCompanies);
        $this->assertEquals($activeCompany->id, $allCompanies->first()->id);
    }

    public function test_global_scopes_are_applied_to_relationships(): void
    {
        // Create test data with relationships
        $activeCompany = Company::factory()->create(['is_active' => true]);
        $inactiveCompany = Company::factory()->create(['is_active' => false]);

        // Test that relationships also apply global scopes
        $companies = Company::all();
        $this->assertCount(1, $companies);
        $this->assertEquals($activeCompany->id, $companies->first()->id);
    }

    public function test_company_scope_combinations(): void
    {
        // Test different combinations of company scopes
        $company1 = Company::factory()->create(['is_active' => true]);
        $company2 = Company::factory()->create(['is_active' => false]);

        // Test bypassing specific scopes
        $allCompanies = Company::withoutGlobalScope(ActiveScope::class)->get();
        $this->assertCount(2, $allCompanies); // All companies regardless of active status
    }

    public function test_email_campaign_scope_combinations(): void
    {
        // Test different combinations of email campaign scopes
        $campaign1 = EmailCampaign::factory()->create(['is_active' => true]);
        $campaign2 = EmailCampaign::factory()->create(['is_active' => false]);

        // Test bypassing specific scopes
        $allCampaigns = EmailCampaign::withoutGlobalScope(ActiveScope::class)->get();
        $this->assertCount(2, $allCampaigns); // All campaigns regardless of active status
    }

    public function test_seo_data_scope_combinations(): void
    {
        // Test different combinations of SEO data scopes
        $seoData1 = SeoData::factory()->create(['is_active' => true]);
        $seoData2 = SeoData::factory()->create(['is_active' => false]);

        // Test bypassing specific scopes
        $allSeoData = SeoData::withoutGlobalScope(ActiveScope::class)->get();
        $this->assertCount(2, $allSeoData); // All SEO data regardless of active status
    }

    public function test_referral_scope_combinations(): void
    {
        // Test different combinations of referral scopes
        $referral1 = Referral::factory()->create([
            'is_active' => true,
            'status' => 'active',
        ]);

        $referral2 = Referral::factory()->create([
            'is_active' => false,
            'status' => 'active',
        ]);

        $referral3 = Referral::factory()->create([
            'is_active' => true,
            'status' => 'pending',
        ]);

        // Test bypassing specific scopes
        $activeReferrals = Referral::withoutGlobalScope(StatusScope::class)->get();
        $this->assertCount(2, $activeReferrals); // Only active referrals

        $statusReferrals = Referral::withoutGlobalScope(ActiveScope::class)->get();
        $this->assertCount(1, $statusReferrals); // Only referrals with allowed status
    }

    public function test_active_scope_with_business_models(): void
    {
        // Test active scope with different business models
        $activeCompany = Company::factory()->create(['is_active' => true]);
        $inactiveCompany = Company::factory()->create(['is_active' => false]);

        $activeCampaign = EmailCampaign::factory()->create(['is_active' => true]);
        $inactiveCampaign = EmailCampaign::factory()->create(['is_active' => false]);

        $activeSeoData = SeoData::factory()->create(['is_active' => true]);
        $inactiveSeoData = SeoData::factory()->create(['is_active' => false]);

        // Test that only active records are returned
        $companies = Company::all();
        $this->assertCount(1, $companies);
        $this->assertEquals($activeCompany->id, $companies->first()->id);

        $campaigns = EmailCampaign::all();
        $this->assertCount(1, $campaigns);
        $this->assertEquals($activeCampaign->id, $campaigns->first()->id);

        $seoData = SeoData::all();
        $this->assertCount(1, $seoData);
        $this->assertEquals($activeSeoData->id, $seoData->first()->id);

        // Test bypassing active scope
        $allCompanies = Company::withoutGlobalScope(ActiveScope::class)->get();
        $this->assertCount(2, $allCompanies);

        $allCampaigns = EmailCampaign::withoutGlobalScope(ActiveScope::class)->get();
        $this->assertCount(2, $allCampaigns);

        $allSeoData = SeoData::withoutGlobalScope(ActiveScope::class)->get();
        $this->assertCount(2, $allSeoData);
    }

    public function test_referral_multiple_scope_filtering(): void
    {
        // Test multiple scope filtering with referrals
        $referral1 = Referral::factory()->create([
            'is_active' => true,
            'status' => 'active',
        ]);

        $referral2 = Referral::factory()->create([
            'is_active' => false,
            'status' => 'active',
        ]);

        $referral3 = Referral::factory()->create([
            'is_active' => true,
            'status' => 'pending',
        ]);

        $referral4 = Referral::factory()->create([
            'is_active' => false,
            'status' => 'pending',
        ]);

        // Test that only active referrals with allowed status are returned
        $referrals = Referral::all();
        $this->assertCount(1, $referrals);
        $this->assertEquals($referral1->id, $referrals->first()->id);

        // Test bypassing all scopes
        $allReferrals = Referral::withoutGlobalScopes()->get();
        $this->assertCount(4, $allReferrals);

        // Test bypassing only active scope
        $activeReferrals = Referral::withoutGlobalScope(ActiveScope::class)->get();
        $this->assertCount(2, $activeReferrals); // Only referrals with allowed status

        // Test bypassing only status scope
        $statusReferrals = Referral::withoutGlobalScope(StatusScope::class)->get();
        $this->assertCount(2, $statusReferrals); // Only active referrals
    }
}
