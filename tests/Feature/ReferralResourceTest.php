<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Referral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\TestCase;

final class ReferralResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create([
            'email' => 'admin@example.com',
        ]);

        $this->actingAs($this->user);
    }

    public function test_can_list_referrals(): void
    {
        // Arrange
        $referrer = User::factory()->create();
        $referred = User::factory()->create();

        $referral = Referral::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
        ]);

        // Act & Assert
        $this->get('/admin/referrals')
            ->assertOk()
            ->assertSee($referral->referral_code);
    }

    public function test_can_create_referral(): void
    {
        // Arrange
        $referrer = User::factory()->create();
        $referred = User::factory()->create();

        // Act
        $this->post('/admin/referrals', [
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
            'referral_code' => 'TEST123',
            'status' => 'pending',
            'title' => 'Test Referral',
            'description' => 'Test description',
        ]);

        // Assert
        $this->assertDatabaseHas('referrals', [
            'referral_code' => 'TEST123',
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
        ]);
    }

    public function test_can_view_referral(): void
    {
        // Arrange
        $referrer = User::factory()->create();
        $referred = User::factory()->create();

        $referral = Referral::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
        ]);

        // Act & Assert
        $this->get("/admin/referrals/{$referral->id}")
            ->assertOk()
            ->assertSee($referral->referral_code);
    }

    public function test_can_edit_referral(): void
    {
        // Arrange
        $referrer = User::factory()->create();
        $referred = User::factory()->create();

        $referral = Referral::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
        ]);

        // Act
        $this->put("/admin/referrals/{$referral->id}", [
            'referrer_id' => $referral->referrer_id,
            'referred_id' => $referral->referred_id,
            'referral_code' => $referral->referral_code,
            'status' => 'completed',
            'title' => 'Updated Title',
            'description' => 'Updated description',
        ]);

        // Assert
        $this->assertDatabaseHas('referrals', [
            'id' => $referral->id,
            'status' => 'completed',
            'title' => 'Updated Title',
        ]);
    }

    public function test_can_delete_referral(): void
    {
        // Arrange
        $referrer = User::factory()->create();
        $referred = User::factory()->create();

        $referral = Referral::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
        ]);

        // Act
        $this->delete("/admin/referrals/{$referral->id}");

        // Assert
        $this->assertDatabaseMissing('referrals', [
            'id' => $referral->id,
        ]);
    }

    public function test_can_filter_referrals_by_status(): void
    {
        // Arrange
        $referrer = User::factory()->create();
        $referred1 = User::factory()->create();
        $referred2 = User::factory()->create();

        $referral1 = Referral::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referred1->id,
            'status' => 'pending',
        ]);
        $referral2 = Referral::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referred2->id,
            'status' => 'completed',
        ]);

        // Act & Assert
        $this->get('/admin/referrals?status=pending')
            ->assertOk()
            ->assertSee($referral1->referral_code)
            ->assertDontSee($referral2->referral_code);
    }

    public function test_can_filter_referrals_by_referrer(): void
    {
        // Arrange
        $referrer1 = User::factory()->create();
        $referrer2 = User::factory()->create();
        $referred = User::factory()->create();

        $referral1 = Referral::factory()->create([
            'referrer_id' => $referrer1->id,
            'referred_id' => $referred->id,
        ]);
        $referral2 = Referral::factory()->create([
            'referrer_id' => $referrer2->id,
            'referred_id' => $referred->id,
        ]);

        // Act & Assert
        $this->get('/admin/referrals?referrer_id='.$referrer1->id)
            ->assertOk()
            ->assertSee($referral1->referral_code)
            ->assertDontSee($referral2->referral_code);
    }

    public function test_can_search_referrals(): void
    {
        // Arrange
        $referrer = User::factory()->create();
        $referred = User::factory()->create();

        $referral1 = Referral::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
            'referral_code' => 'SEARCH123',
        ]);
        $referral2 = Referral::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
            'referral_code' => 'OTHER456',
        ]);

        // Act & Assert
        $this->get('/admin/referrals?search=SEARCH123')
            ->assertOk()
            ->assertSee($referral1->referral_code)
            ->assertDontSee($referral2->referral_code);
    }

    public function test_referral_validation(): void
    {
        // Act & Assert
        $this->post('/admin/referrals', [])
            ->assertSessionHasErrors(['referrer_id', 'referred_id', 'referral_code', 'status', 'title']);
    }

    public function test_referral_unique_validation(): void
    {
        // Arrange
        $referrer = User::factory()->create();
        $referred = User::factory()->create();

        $existingReferral = Referral::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
            'referral_code' => 'UNIQUE123',
        ]);

        // Act & Assert
        $this->post('/admin/referrals', [
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
            'referral_code' => 'UNIQUE123',
            'status' => 'pending',
            'title' => 'Test Title',
        ])->assertSessionHasErrors(['referral_code']);
    }
}
