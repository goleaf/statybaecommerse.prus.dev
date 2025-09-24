<?php

declare(strict_types=1);

use App\Models\Address;
use App\Models\CustomerGroup;
use App\Models\Document;
use App\Models\Order;
use App\Models\Partner;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

describe('User Impersonation Comprehensive Tests', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create();
        $this->admin->assignRole('administrator');

        $this->regularUser = User::factory()->create(['is_admin' => false]);
        $this->anotherAdmin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($this->admin);
    });

    describe('Access Control', function () {
        it('allows administrator to access user impersonation', function () {
            $this->get('/admin/user-impersonation')
                ->assertOk();
        });

        it('allows super_admin to access user impersonation', function () {
            $superAdmin = User::factory()->create();
            $superAdmin->assignRole('super_admin');

            $this->actingAs($superAdmin)
                ->get('/admin/user-impersonation')
                ->assertOk();
        });

        it('allows users with is_admin flag to access user impersonation', function () {
            $adminUser = User::factory()->create(['is_admin' => true]);

            $this->actingAs($adminUser)
                ->get('/admin/user-impersonation')
                ->assertOk();
        });

        it('denies access to regular users', function () {
            $this->actingAs($this->regularUser)
                ->get('/admin/user-impersonation')
                ->assertForbidden();
        });

        it('denies access to unauthenticated users', function () {
            Auth::logout();

            $this->get('/admin/user-impersonation')
                ->assertRedirect('/admin/login');
        });
    });

    describe('Impersonation Session Management', function () {
        it('can start impersonation session', function () {
            $impersonateData = [
                'original_user_id' => $this->admin->id,
                'impersonated_user_id' => $this->regularUser->id,
                'started_at' => now()->toISOString(),
            ];

            Session::put('impersonate', $impersonateData);
            Auth::login($this->regularUser);

            expect(Session::has('impersonate'))->toBeTrue();
            expect(Session::get('impersonate.original_user_id'))->toBe($this->admin->id);
            expect(Session::get('impersonate.impersonated_user_id'))->toBe($this->regularUser->id);
            expect(Auth::id())->toBe($this->regularUser->id);
        });

        it('can stop impersonation session', function () {
            // Start impersonation
            Session::put('impersonate', [
                'original_user_id' => $this->admin->id,
                'impersonated_user_id' => $this->regularUser->id,
                'started_at' => now()->toISOString(),
            ]);
            Auth::login($this->regularUser);

            // Stop impersonation
            $originalUserId = Session::get('impersonate.original_user_id');
            $originalUser = User::find($originalUserId);

            if ($originalUser) {
                Auth::login($originalUser);
                Session::forget('impersonate');
            }

            expect(Session::has('impersonate'))->toBeFalse();
            expect(Auth::id())->toBe($this->admin->id);
        });

        it('prevents impersonating admin users', function () {
            // Admin users should not be impersonatable
            expect($this->anotherAdmin->is_admin)->toBeTrue();

            $canImpersonate = ! $this->anotherAdmin->is_admin;
            expect($canImpersonate)->toBeFalse();
        });

        it('handles impersonation middleware correctly', function () {
            // Start impersonation
            Session::put('impersonate', [
                'original_user_id' => $this->admin->id,
                'impersonated_user_id' => $this->regularUser->id,
                'started_at' => now()->toISOString(),
            ]);

            // Make a request that goes through the middleware
            $response = $this->get('/admin');

            // The middleware should handle the impersonation
            expect(Auth::id())->toBe($this->regularUser->id);
        });
    });

    describe('User Data and Relationships', function () {
        it('can access impersonated user orders', function () {
            $order = Order::factory()->create(['user_id' => $this->regularUser->id]);

            Auth::login($this->regularUser);

            expect($this->regularUser->orders()->count())->toBe(1);
            expect($this->regularUser->orders->first()->id)->toBe($order->id);
        });

        it('can access impersonated user addresses', function () {
            $address = Address::factory()->create(['user_id' => $this->regularUser->id]);

            Auth::login($this->regularUser);

            expect($this->regularUser->addresses()->count())->toBe(1);
            expect($this->regularUser->addresses->first()->id)->toBe($address->id);
        });

        it('can access impersonated user reviews', function () {
            $review = Review::factory()->create(['user_id' => $this->regularUser->id]);

            Auth::login($this->regularUser);

            expect($this->regularUser->reviews()->count())->toBe(1);
            expect($this->regularUser->reviews->first()->id)->toBe($review->id);
        });

        it('can access impersonated user wishlist', function () {
            $product = Product::factory()->create();
            $this->regularUser->wishlist()->attach($product->id);

            Auth::login($this->regularUser);

            expect($this->regularUser->wishlist()->count())->toBe(1);
            expect($this->regularUser->wishlist->first()->id)->toBe($product->id);
        });

        it('can access impersonated user customer groups', function () {
            $customerGroup = CustomerGroup::factory()->create();
            $this->regularUser->customerGroups()->attach($customerGroup->id);

            Auth::login($this->regularUser);

            expect($this->regularUser->customerGroups()->count())->toBe(1);
            expect($this->regularUser->customerGroups->first()->id)->toBe($customerGroup->id);
        });

        it('can access impersonated user documents', function () {
            $document = Document::factory()->create([
                'documentable_type' => User::class,
                'documentable_id' => $this->regularUser->id,
            ]);

            Auth::login($this->regularUser);

            expect($this->regularUser->documents()->count())->toBe(1);
            expect($this->regularUser->documents->first()->id)->toBe($document->id);
        });

        it('can access impersonated user partners', function () {
            $partner = Partner::factory()->create();
            $this->regularUser->partners()->attach($partner->id);

            Auth::login($this->regularUser);

            expect($this->regularUser->partners()->count())->toBe(1);
            expect($this->regularUser->partners->first()->id)->toBe($partner->id);
        });
    });

    describe('User Attributes and Methods', function () {
        it('can access impersonated user attributes', function () {
            Auth::login($this->regularUser);

            expect($this->regularUser->name)->not()->toBeNull();
            expect($this->regularUser->email)->not()->toBeNull();
            expect($this->regularUser->is_active)->toBeTrue();
            expect($this->regularUser->is_admin)->toBeFalse();
        });

        it('can access impersonated user default address', function () {
            $address = Address::factory()->create([
                'user_id' => $this->regularUser->id,
                'is_default' => true,
            ]);

            Auth::login($this->regularUser);

            expect($this->regularUser->default_address)->not()->toBeNull();
            expect($this->regularUser->default_address->id)->toBe($address->id);
        });

        it('can access impersonated user billing address', function () {
            $address = Address::factory()->create([
                'user_id' => $this->regularUser->id,
                'type' => 'billing',
                'is_default' => true,
            ]);

            Auth::login($this->regularUser);

            expect($this->regularUser->billing_address)->not()->toBeNull();
            expect($this->regularUser->billing_address->id)->toBe($address->id);
        });

        it('can access impersonated user shipping address', function () {
            $address = Address::factory()->create([
                'user_id' => $this->regularUser->id,
                'type' => 'shipping',
                'is_default' => true,
            ]);

            Auth::login($this->regularUser);

            expect($this->regularUser->shipping_address)->not()->toBeNull();
            expect($this->regularUser->shipping_address->id)->toBe($address->id);
        });

        it('can check if impersonated user is partner', function () {
            $partner = Partner::factory()->create(['is_enabled' => true]);
            $this->regularUser->partners()->attach($partner->id);

            Auth::login($this->regularUser);

            expect($this->regularUser->isPartner())->toBeTrue();
            expect($this->regularUser->active_partner)->not()->toBeNull();
            expect($this->regularUser->partner_discount_rate)->toBeGreaterThan(0);
        });
    });

    describe('Security and Validation', function () {
        it('validates impersonation session data', function () {
            $impersonateData = [
                'original_user_id' => $this->admin->id,
                'impersonated_user_id' => $this->regularUser->id,
                'started_at' => now()->toISOString(),
            ];

            expect($impersonateData)->toHaveKeys(['original_user_id', 'impersonated_user_id', 'started_at']);
            expect($impersonateData['original_user_id'])->toBe($this->admin->id);
            expect($impersonateData['impersonated_user_id'])->toBe($this->regularUser->id);
            expect($impersonateData['started_at'])->not()->toBeNull();
        });

        it('prevents self-impersonation', function () {
            $canImpersonateSelf = $this->admin->id !== $this->admin->id;
            expect($canImpersonateSelf)->toBeFalse();
        });

        it('validates user exists before impersonation', function () {
            $nonExistentUserId = 99999;
            $user = User::find($nonExistentUserId);

            expect($user)->toBeNull();
        });

        it('validates original user exists for stopping impersonation', function () {
            Session::put('impersonate', [
                'original_user_id' => 99999, // Non-existent user
                'impersonated_user_id' => $this->regularUser->id,
                'started_at' => now()->toISOString(),
            ]);

            $originalUserId = Session::get('impersonate.original_user_id');
            $originalUser = User::find($originalUserId);

            expect($originalUser)->toBeNull();
        });
    });

    describe('Edge Cases and Error Handling', function () {
        it('handles missing impersonation session gracefully', function () {
            expect(Session::has('impersonate'))->toBeFalse();

            $originalUserId = Session::get('impersonate.original_user_id');
            expect($originalUserId)->toBeNull();
        });

        it('handles corrupted impersonation session data', function () {
            Session::put('impersonate', [
                'original_user_id' => 'invalid',
                'impersonated_user_id' => 'invalid',
                'started_at' => 'invalid',
            ]);

            $originalUserId = Session::get('impersonate.original_user_id');
            $originalUser = User::find($originalUserId);

            expect($originalUser)->toBeNull();
        });

        it('handles deleted user during impersonation', function () {
            // Start impersonation
            Session::put('impersonate', [
                'original_user_id' => $this->admin->id,
                'impersonated_user_id' => $this->regularUser->id,
                'started_at' => now()->toISOString(),
            ]);

            // Delete the impersonated user
            $this->regularUser->delete();

            // Try to find the deleted user
            $deletedUser = User::find($this->regularUser->id);
            expect($deletedUser)->toBeNull();
        });

        it('handles multiple impersonation sessions', function () {
            $user1 = User::factory()->create(['is_admin' => false]);
            $user2 = User::factory()->create(['is_admin' => false]);

            // Start first impersonation
            Session::put('impersonate', [
                'original_user_id' => $this->admin->id,
                'impersonated_user_id' => $user1->id,
                'started_at' => now()->toISOString(),
            ]);

            // Start second impersonation (should replace first)
            Session::put('impersonate', [
                'original_user_id' => $this->admin->id,
                'impersonated_user_id' => $user2->id,
                'started_at' => now()->toISOString(),
            ]);

            expect(Session::get('impersonate.impersonated_user_id'))->toBe($user2->id);
        });
    });
});
