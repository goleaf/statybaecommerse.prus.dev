<?php declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\Referral;
use App\Models\User;
use App\Models\ReferralReward;
use App\Models\Order;
use App\Models\Translations\ReferralTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ReferralResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->actingAs(User::factory()->create());
    }

    public function test_can_list_referrals(): void
    {
        $referrals = Referral::factory()->count(3)->create();

        Livewire::test(\App\Filament\Resources\ReferralResource\Pages\ListReferrals::class)
            ->assertCanSeeTableRecords($referrals);
    }

    public function test_can_create_referral(): void
    {
        $referrer = User::factory()->create();
        $referred = User::factory()->create();

        Livewire::test(\App\Filament\Resources\ReferralResource\Pages\CreateReferral::class)
            ->fillForm([
                'referrer_id' => $referrer->id,
                'referred_id' => $referred->id,
                'referral_code' => 'TEST123',
                'status' => 'pending',
                'title' => [
                    'en' => 'Test Referral',
                    'lt' => 'Testo Rekomendacija',
                ],
                'description' => [
                    'en' => 'Test Description',
                    'lt' => 'Testo Aprašymas',
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('referrals', [
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
            'referral_code' => 'TEST123',
            'status' => 'pending',
        ]);
    }

    public function test_can_edit_referral(): void
    {
        $referral = Referral::factory()->create();
        $newReferrer = User::factory()->create();

        Livewire::test(\App\Filament\Resources\ReferralResource\Pages\EditReferral::class, [
            'record' => $referral->getRouteKey(),
        ])
            ->fillForm([
                'referrer_id' => $newReferrer->id,
                'status' => 'completed',
                'title' => [
                    'en' => 'Updated Title',
                    'lt' => 'Atnaujintas Pavadinimas',
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('referrals', [
            'id' => $referral->id,
            'referrer_id' => $newReferrer->id,
            'status' => 'completed',
        ]);
    }

    public function test_can_view_referral(): void
    {
        $referral = Referral::factory()->create();

        Livewire::test(\App\Filament\Resources\ReferralResource\Pages\ViewReferral::class, [
            'record' => $referral->getRouteKey(),
        ])
            ->assertOk();
    }

    public function test_can_delete_referral(): void
    {
        $referral = Referral::factory()->create();

        Livewire::test(\App\Filament\Resources\ReferralResource\Pages\ListReferrals::class)
            ->callTableAction('delete', $referral);

        $this->assertSoftDeleted('referrals', ['id' => $referral->id]);
    }

    public function test_can_restore_referral(): void
    {
        $referral = Referral::factory()->create();
        $referral->delete();

        Livewire::test(\App\Filament\Resources\ReferralResource\Pages\ListReferrals::class)
            ->callTableAction('restore', $referral);

        $this->assertDatabaseHas('referrals', [
            'id' => $referral->id,
            'deleted_at' => null,
        ]);
    }

    public function test_can_force_delete_referral(): void
    {
        $referral = Referral::factory()->create();
        $referral->delete();

        Livewire::test(\App\Filament\Resources\ReferralResource\Pages\ListReferrals::class)
            ->callTableAction('forceDelete', $referral);

        $this->assertDatabaseMissing('referrals', ['id' => $referral->id]);
    }

    public function test_can_mark_referral_as_completed(): void
    {
        $referral = Referral::factory()->pending()->create();

        Livewire::test(\App\Filament\Resources\ReferralResource\Pages\ListReferrals::class)
            ->callTableAction('mark_completed', $referral);

        $this->assertDatabaseHas('referrals', [
            'id' => $referral->id,
            'status' => 'completed',
        ]);
    }

    public function test_can_mark_referral_as_expired(): void
    {
        $referral = Referral::factory()->pending()->create();

        Livewire::test(\App\Filament\Resources\ReferralResource\Pages\ListReferrals::class)
            ->callTableAction('mark_expired', $referral);

        $this->assertDatabaseHas('referrals', [
            'id' => $referral->id,
            'status' => 'expired',
        ]);
    }

    public function test_can_filter_by_status(): void
    {
        $pendingReferral = Referral::factory()->pending()->create();
        $completedReferral = Referral::factory()->completed()->create();

        Livewire::test(\App\Filament\Resources\ReferralResource\Pages\ListReferrals::class)
            ->filterTable('status', 'pending')
            ->assertCanSeeTableRecords([$pendingReferral])
            ->assertCanNotSeeTableRecords([$completedReferral]);
    }

    public function test_can_filter_by_source(): void
    {
        $websiteReferral = Referral::factory()->create(['source' => 'website']);
        $emailReferral = Referral::factory()->create(['source' => 'email']);

        Livewire::test(\App\Filament\Resources\ReferralResource\Pages\ListReferrals::class)
            ->filterTable('source', 'website')
            ->assertCanSeeTableRecords([$websiteReferral])
            ->assertCanNotSeeTableRecords([$emailReferral]);
    }

    public function test_can_filter_by_campaign(): void
    {
        $summerReferral = Referral::factory()->create(['campaign' => 'summer2024']);
        $winterReferral = Referral::factory()->create(['campaign' => 'winter2024']);

        Livewire::test(\App\Filament\Resources\ReferralResource\Pages\ListReferrals::class)
            ->filterTable('campaign', 'summer2024')
            ->assertCanSeeTableRecords([$summerReferral])
            ->assertCanNotSeeTableRecords([$winterReferral]);
    }

    public function test_can_filter_by_has_rewards(): void
    {
        $referralWithRewards = Referral::factory()->withRewards()->create();
        $referralWithoutRewards = Referral::factory()->create();

        Livewire::test(\App\Filament\Resources\ReferralResource\Pages\ListReferrals::class)
            ->filterTable('has_rewards', true)
            ->assertCanSeeTableRecords([$referralWithRewards])
            ->assertCanNotSeeTableRecords([$referralWithoutRewards]);
    }

    public function test_can_filter_by_about_to_expire(): void
    {
        $aboutToExpireReferral = Referral::factory()->create([
            'status' => 'pending',
            'expires_at' => now()->addDays(3),
        ]);
        $notExpiringReferral = Referral::factory()->create([
            'status' => 'pending',
            'expires_at' => now()->addDays(30),
        ]);

        Livewire::test(\App\Filament\Resources\ReferralResource\Pages\ListReferrals::class)
            ->filterTable('about_to_expire')
            ->assertCanSeeTableRecords([$aboutToExpireReferral])
            ->assertCanNotSeeTableRecords([$notExpiringReferral]);
    }

    public function test_can_filter_by_high_performance(): void
    {
        $highPerformanceReferral = Referral::factory()->completed()->withRewards()->create();
        $lowPerformanceReferral = Referral::factory()->pending()->create();

        Livewire::test(\App\Filament\Resources\ReferralResource\Pages\ListReferrals::class)
            ->filterTable('high_performance')
            ->assertCanSeeTableRecords([$highPerformanceReferral])
            ->assertCanNotSeeTableRecords([$lowPerformanceReferral]);
    }

    public function test_can_search_referrals(): void
    {
        $referral1 = Referral::factory()->create(['referral_code' => 'SEARCH123']);
        $referral2 = Referral::factory()->create(['referral_code' => 'DIFFERENT']);

        Livewire::test(\App\Filament\Resources\ReferralResource\Pages\ListReferrals::class)
            ->searchTable('SEARCH123')
            ->assertCanSeeTableRecords([$referral1])
            ->assertCanNotSeeTableRecords([$referral2]);
    }

    public function test_can_sort_referrals(): void
    {
        $referral1 = Referral::factory()->create(['created_at' => now()->subDays(2)]);
        $referral2 = Referral::factory()->create(['created_at' => now()->subDays(1)]);

        Livewire::test(\App\Filament\Resources\ReferralResource\Pages\ListReferrals::class)
            ->sortTable('created_at', 'desc')
            ->assertCanSeeTableRecords([$referral2, $referral1]);
    }

    public function test_can_bulk_delete_referrals(): void
    {
        $referrals = Referral::factory()->count(3)->create();

        Livewire::test(\App\Filament\Resources\ReferralResource\Pages\ListReferrals::class)
            ->callTableBulkAction('delete', $referrals);

        foreach ($referrals as $referral) {
            $this->assertSoftDeleted('referrals', ['id' => $referral->id]);
        }
    }

    public function test_can_bulk_mark_as_completed(): void
    {
        $referrals = Referral::factory()->count(3)->pending()->create();

        Livewire::test(\App\Filament\Resources\ReferralResource\Pages\ListReferrals::class)
            ->callTableBulkAction('mark_completed', $referrals);

        foreach ($referrals as $referral) {
            $this->assertDatabaseHas('referrals', [
                'id' => $referral->id,
                'status' => 'completed',
            ]);
        }
    }

    public function test_can_bulk_mark_as_expired(): void
    {
        $referrals = Referral::factory()->count(3)->pending()->create();

        Livewire::test(\App\Filament\Resources\ReferralResource\Pages\ListReferrals::class)
            ->callTableBulkAction('mark_expired', $referrals);

        foreach ($referrals as $referral) {
            $this->assertDatabaseHas('referrals', [
                'id' => $referral->id,
                'status' => 'expired',
            ]);
        }
    }

    public function test_form_validation_requires_referrer(): void
    {
        $referred = User::factory()->create();

        Livewire::test(\App\Filament\Resources\ReferralResource\Pages\CreateReferral::class)
            ->fillForm([
                'referred_id' => $referred->id,
                'referral_code' => 'TEST123',
                'status' => 'pending',
            ])
            ->call('create')
            ->assertHasFormErrors(['referrer_id' => 'required']);
    }

    public function test_form_validation_requires_referred(): void
    {
        $referrer = User::factory()->create();

        Livewire::test(\App\Filament\Resources\ReferralResource\Pages\CreateReferral::class)
            ->fillForm([
                'referrer_id' => $referrer->id,
                'referral_code' => 'TEST123',
                'status' => 'pending',
            ])
            ->call('create')
            ->assertHasFormErrors(['referred_id' => 'required']);
    }

    public function test_form_validation_requires_referral_code(): void
    {
        $referrer = User::factory()->create();
        $referred = User::factory()->create();

        Livewire::test(\App\Filament\Resources\ReferralResource\Pages\CreateReferral::class)
            ->fillForm([
                'referrer_id' => $referrer->id,
                'referred_id' => $referred->id,
                'status' => 'pending',
            ])
            ->call('create')
            ->assertHasFormErrors(['referral_code' => 'required']);
    }

    public function test_form_validation_requires_unique_referral_code(): void
    {
        $existingReferral = Referral::factory()->create(['referral_code' => 'UNIQUE123']);
        $referrer = User::factory()->create();
        $referred = User::factory()->create();

        Livewire::test(\App\Filament\Resources\ReferralResource\Pages\CreateReferral::class)
            ->fillForm([
                'referrer_id' => $referrer->id,
                'referred_id' => $referred->id,
                'referral_code' => 'UNIQUE123',
                'status' => 'pending',
            ])
            ->call('create')
            ->assertHasFormErrors(['referral_code' => 'unique']);
    }

    public function test_can_handle_translation_fields(): void
    {
        $referrer = User::factory()->create();
        $referred = User::factory()->create();

        Livewire::test(\App\Filament\Resources\ReferralResource\Pages\CreateReferral::class)
            ->fillForm([
                'referrer_id' => $referrer->id,
                'referred_id' => $referred->id,
                'referral_code' => 'TEST123',
                'status' => 'pending',
                'title' => [
                    'en' => 'English Title',
                    'lt' => 'Lietuvių Pavadinimas',
                ],
                'description' => [
                    'en' => 'English Description',
                    'lt' => 'Lietuvių Aprašymas',
                ],
                'seo_keywords' => [
                    'en' => ['referral', 'bonus', 'reward'],
                    'lt' => ['rekomendacija', 'bonusas', 'atlygis'],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $referral = Referral::where('referral_code', 'TEST123')->first();
        $this->assertEquals('English Title', $referral->getTranslation('title', 'en'));
        $this->assertEquals('Lietuvių Pavadinimas', $referral->getTranslation('title', 'lt'));
        $this->assertEquals(['referral', 'bonus', 'reward'], $referral->getTranslation('seo_keywords', 'en'));
    }

    public function test_can_view_referral_with_relations(): void
    {
        $referral = Referral::factory()
            ->withRewards()
            ->withOrders()
            ->withAnalytics()
            ->create();

        Livewire::test(\App\Filament\Resources\ReferralResource\Pages\ViewReferral::class, [
            'record' => $referral->getRouteKey(),
        ])
            ->assertOk()
            ->assertCanSeeTableRecords($referral->rewards)
            ->assertCanSeeTableRecords($referral->referredOrders)
            ->assertCanSeeTableRecords($referral->analyticsEvents);
    }
}


