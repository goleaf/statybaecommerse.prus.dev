<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\UsersCompanyTabSeeder;
use Database\Seeders\UsersCustomerGroupsTabSeeder;
use Database\Seeders\UsersPartnersTabSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('registers users tab seeders in the standard seeder profile', function (): void {
    $standardSeeders = config('seeds.standard_seeders', []);

    expect($standardSeeders)->not->toContain(UsersCompanyTabSeeder::class);
    expect($standardSeeders)->toContain(UsersCustomerGroupsTabSeeder::class);
    expect($standardSeeders)->toContain(UsersPartnersTabSeeder::class);
});

it('seeds company tab users with all related interface records and stays idempotent', function (): void {
    $this->seed(UsersCompanyTabSeeder::class);
    $this->seed(UsersCompanyTabSeeder::class);

    $companyUsers = User::query()
        ->withoutGlobalScopes()
        ->where('email', 'like', 'info@egisstatyba.lt')
        ->orderBy('email')
        ->get();

    $referredUsersCount = User::query()
        ->withoutGlobalScopes()
        ->where('email', 'like', 'info@egisstatyba.lt')
        ->count();

    expect($companyUsers)->toHaveCount(12);
    expect($referredUsersCount)->toBe(12);

    foreach ($companyUsers as $user) {
        expect($user->company_id)->not->toBeNull();
        expect($user->account_type)->toBe('company');

        expect(DB::table('addresses')->where('user_id', $user->id)->count())->toBeGreaterThanOrEqual(2);
        expect(DB::table('cart_items')->where('user_id', $user->id)->count())->toBeGreaterThanOrEqual(1);
        expect(DB::table('customer_group_user')->where('user_id', $user->id)->count())->toBeGreaterThanOrEqual(1);
        expect(DB::table('partner_users')->where('user_id', $user->id)->count())->toBeGreaterThanOrEqual(1);
        expect(DB::table('orders')->where('user_id', $user->id)->count())->toBeGreaterThanOrEqual(1);
        expect(DB::table('coupon_usages')->where('user_id', $user->id)->count())->toBeGreaterThanOrEqual(1);
        expect(DB::table('discount_redemptions')->where('user_id', $user->id)->count())->toBeGreaterThanOrEqual(1);
        expect(DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->count())->toBeGreaterThanOrEqual(1);
        expect(DB::table('referral_codes')->where('user_id', $user->id)->count())->toBeGreaterThanOrEqual(1);
        expect(DB::table('referrals')->where('referrer_id', $user->id)->count())->toBeGreaterThanOrEqual(1);
        expect(DB::table('referral_rewards')->where('user_id', $user->id)->count())->toBeGreaterThanOrEqual(1);
        expect(DB::table('subscribers')->where('user_id', $user->id)->count())->toBe(1);
    }
});

it('seeds customer groups tab users with two group assignments and stays idempotent', function (): void {
    $this->seed(UsersCustomerGroupsTabSeeder::class);
    $this->seed(UsersCustomerGroupsTabSeeder::class);

    $users = User::query()
        ->withoutGlobalScopes()
        ->where('email', 'like', 'info@egisstatyba.lt')
        ->orderBy('email')
        ->get();

    expect($users)->toHaveCount(12);

    foreach ($users as $user) {
        $groupRows = DB::table('customer_group_user')
            ->where('user_id', $user->id)
            ->get();

        expect($user->company_id)->not->toBeNull();
        expect($user->account_type)->toBe('company');
        expect($groupRows)->toHaveCount(2);
        expect($groupRows->whereNotNull('assigned_at'))->toHaveCount(2);
    }
});

it('seeds partners tab users with two partner assignments and stays idempotent', function (): void {
    $this->seed(UsersPartnersTabSeeder::class);
    $this->seed(UsersPartnersTabSeeder::class);

    $users = User::query()
        ->withoutGlobalScopes()
        ->where('email', 'like', 'info@egisstatyba.lt')
        ->orderBy('email')
        ->get();

    expect($users)->toHaveCount(12);

    foreach ($users as $user) {
        expect($user->company_id)->not->toBeNull();
        expect($user->account_type)->toBe('company');
        expect(DB::table('partner_users')->where('user_id', $user->id)->count())->toBe(2);
    }
});
