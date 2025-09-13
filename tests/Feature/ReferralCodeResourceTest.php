<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ReferralCode;
use App\Models\User;
use App\Models\ReferralCampaign;
use App\Filament\Resources\ReferralCodeResource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ReferralCodeResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_can_list_referral_codes(): void
    {
        $referralCodes = ReferralCode::factory()->count(3)->create();

        Livewire::test(ReferralCodeResource\Pages\ListReferralCodes::class)
            ->assertCanSeeTableRecords($referralCodes);
    }

    public function test_can_create_referral_code(): void
    {
        $user = User::factory()->create();
        $campaign = ReferralCampaign::factory()->create();

        Livewire::test(ReferralCodeResource\Pages\CreateReferralCode::class)
            ->fillForm([
                'user_id' => $user->id,
                'code' => 'TEST123',
                'is_active' => true,
                'title' => ['lt' => 'Test kodas', 'en' => 'Test code'],
                'description' => ['lt' => 'Test aprašymas', 'en' => 'Test description'],
                'usage_limit' => 100,
                'reward_amount' => 10.50,
                'reward_type' => 'fixed',
                'source' => 'admin',
                'campaign_id' => $campaign->id,
                'tags' => ['test', 'promo'],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('referral_codes', [
            'user_id' => $user->id,
            'code' => 'TEST123',
            'is_active' => true,
            'usage_limit' => 100,
            'reward_amount' => 10.50,
            'reward_type' => 'fixed',
            'source' => 'admin',
            'campaign_id' => $campaign->id,
        ]);
    }

    public function test_can_edit_referral_code(): void
    {
        $referralCode = ReferralCode::factory()->create();
        $newUser = User::factory()->create();

        Livewire::test(ReferralCodeResource\Pages\EditReferralCode::class, [
            'record' => $referralCode->getRouteKey(),
        ])
            ->fillForm([
                'user_id' => $newUser->id,
                'code' => 'UPDATED123',
                'is_active' => false,
                'title' => ['lt' => 'Atnaujintas kodas', 'en' => 'Updated code'],
                'usage_limit' => 200,
                'reward_amount' => 25.00,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('referral_codes', [
            'id' => $referralCode->id,
            'user_id' => $newUser->id,
            'code' => 'UPDATED123',
            'is_active' => false,
            'usage_limit' => 200,
            'reward_amount' => 25.00,
        ]);
    }

    public function test_can_view_referral_code(): void
    {
        $referralCode = ReferralCode::factory()->create();

        Livewire::test(ReferralCodeResource\Pages\ViewReferralCode::class, [
            'record' => $referralCode->getRouteKey(),
        ])
            ->assertFormSet([
                'user_id' => $referralCode->user_id,
                'code' => $referralCode->code,
                'is_active' => $referralCode->is_active,
            ]);
    }

    public function test_can_delete_referral_code(): void
    {
        $referralCode = ReferralCode::factory()->create();

        Livewire::test(ReferralCodeResource\Pages\ListReferralCodes::class)
            ->callTableAction(DeleteAction::class, $referralCode);

        $this->assertDatabaseMissing('referral_codes', [
            'id' => $referralCode->id,
        ]);
    }

    public function test_can_filter_active_referral_codes(): void
    {
        $activeCode = ReferralCode::factory()->create(['is_active' => true]);
        $inactiveCode = ReferralCode::factory()->create(['is_active' => false]);

        Livewire::test(ReferralCodeResource\Pages\ListReferralCodes::class)
            ->filterTable('active')
            ->assertCanSeeTableRecords([$activeCode])
            ->assertCanNotSeeTableRecords([$inactiveCode]);
    }

    public function test_can_filter_expired_referral_codes(): void
    {
        $expiredCode = ReferralCode::factory()->create([
            'is_active' => false,
            'expires_at' => now()->subDay(),
        ]);
        $activeCode = ReferralCode::factory()->create([
            'is_active' => true,
            'expires_at' => now()->addDay(),
        ]);

        Livewire::test(ReferralCodeResource\Pages\ListReferralCodes::class)
            ->filterTable('expired')
            ->assertCanSeeTableRecords([$expiredCode])
            ->assertCanNotSeeTableRecords([$activeCode]);
    }

    public function test_can_filter_by_source(): void
    {
        $adminCode = ReferralCode::factory()->create(['source' => 'admin']);
        $userCode = ReferralCode::factory()->create(['source' => 'user']);

        Livewire::test(ReferralCodeResource\Pages\ListReferralCodes::class)
            ->filterTable('by_source', ['source' => 'admin'])
            ->assertCanSeeTableRecords([$adminCode])
            ->assertCanNotSeeTableRecords([$userCode]);
    }

    public function test_can_filter_by_reward_type(): void
    {
        $fixedCode = ReferralCode::factory()->create(['reward_type' => 'fixed']);
        $percentageCode = ReferralCode::factory()->create(['reward_type' => 'percentage']);

        Livewire::test(ReferralCodeResource\Pages\ListReferralCodes::class)
            ->filterTable('by_reward_type', ['reward_type' => 'fixed'])
            ->assertCanSeeTableRecords([$fixedCode])
            ->assertCanNotSeeTableRecords([$percentageCode]);
    }

    public function test_can_deactivate_referral_code(): void
    {
        $referralCode = ReferralCode::factory()->create(['is_active' => true]);

        Livewire::test(ReferralCodeResource\Pages\ListReferralCodes::class)
            ->callTableAction('deactivate', $referralCode);

        $this->assertFalse($referralCode->fresh()->is_active);
    }

    public function test_can_copy_referral_url(): void
    {
        $referralCode = ReferralCode::factory()->create(['code' => 'TEST123']);

        Livewire::test(ReferralCodeResource\Pages\ListReferralCodes::class)
            ->callTableAction('copy_url', $referralCode)
            ->assertNotified(__('referral_codes.notifications.url_copied'));
    }

    public function test_can_bulk_deactivate_referral_codes(): void
    {
        $referralCodes = ReferralCode::factory()->count(3)->create(['is_active' => true]);

        Livewire::test(ReferralCodeResource\Pages\ListReferralCodes::class)
            ->callTableBulkAction('deactivate', $referralCodes);

        foreach ($referralCodes as $referralCode) {
            $this->assertFalse($referralCode->fresh()->is_active);
        }
    }

    public function test_can_bulk_activate_referral_codes(): void
    {
        $referralCodes = ReferralCode::factory()->count(3)->create(['is_active' => false]);

        Livewire::test(ReferralCodeResource\Pages\ListReferralCodes::class)
            ->callTableBulkAction('activate', $referralCodes);

        foreach ($referralCodes as $referralCode) {
            $this->assertTrue($referralCode->fresh()->is_active);
        }
    }

    public function test_can_search_referral_codes(): void
    {
        $referralCode1 = ReferralCode::factory()->create(['code' => 'SEARCH123']);
        $referralCode2 = ReferralCode::factory()->create(['code' => 'DIFFERENT456']);

        Livewire::test(ReferralCodeResource\Pages\ListReferralCodes::class)
            ->searchTable('SEARCH123')
            ->assertCanSeeTableRecords([$referralCode1])
            ->assertCanNotSeeTableRecords([$referralCode2]);
    }

    public function test_can_sort_referral_codes(): void
    {
        $referralCode1 = ReferralCode::factory()->create(['created_at' => now()->subDay()]);
        $referralCode2 = ReferralCode::factory()->create(['created_at' => now()]);

        Livewire::test(ReferralCodeResource\Pages\ListReferralCodes::class)
            ->sortTable('created_at', 'desc')
            ->assertCanSeeTableRecords([$referralCode2, $referralCode1]);
    }

    public function test_form_validation_works(): void
    {
        Livewire::test(ReferralCodeResource\Pages\CreateReferralCode::class)
            ->fillForm([
                'user_id' => null,
                'code' => '',
                'is_active' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['user_id', 'code', 'is_active']);
    }

    public function test_code_must_be_unique(): void
    {
        $existingCode = ReferralCode::factory()->create(['code' => 'UNIQUE123']);
        $user = User::factory()->create();

        Livewire::test(ReferralCodeResource\Pages\CreateReferralCode::class)
            ->fillForm([
                'user_id' => $user->id,
                'code' => 'UNIQUE123',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['code']);
    }

    public function test_can_toggle_table_columns(): void
    {
        $referralCode = ReferralCode::factory()->create();

        Livewire::test(ReferralCodeResource\Pages\ListReferralCodes::class)
            ->assertTableColumnExists('title')
            ->assertTableColumnExists('usage_count')
            ->assertTableColumnExists('reward_amount');
    }

    public function test_widgets_are_displayed(): void
    {
        Livewire::test(ReferralCodeResource\Pages\ListReferralCodes::class)
            ->assertCanSeeWidget(ReferralCodeResource\Widgets\ReferralCodeStatsWidget::class)
            ->assertCanSeeWidget(ReferralCodeResource\Widgets\ReferralCodeUsageChartWidget::class)
            ->assertCanSeeWidget(ReferralCodeResource\Widgets\TopReferralCodesWidget::class);
    }

    public function test_navigation_works(): void
    {
        $this->get(ReferralCodeResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_create_page_works(): void
    {
        $this->get(ReferralCodeResource::getUrl('create'))
            ->assertSuccessful();
    }

    public function test_edit_page_works(): void
    {
        $referralCode = ReferralCode::factory()->create();

        $this->get(ReferralCodeResource::getUrl('edit', ['record' => $referralCode]))
            ->assertSuccessful();
    }

    public function test_view_page_works(): void
    {
        $referralCode = ReferralCode::factory()->create();

        $this->get(ReferralCodeResource::getUrl('view', ['record' => $referralCode]))
            ->assertSuccessful();
    }
}
