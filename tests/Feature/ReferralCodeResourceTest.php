<?php

declare(strict_types=1);

use App\Filament\Resources\ReferralCodeResource\Pages\ListReferralCodes;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Laravel\get;

it('mounts referral code index page', function () {
    $user = User::factory()->create();
    actingAs($user);

    Filament::setCurrentPanel('admin');

    $response = get(ListReferralCodes::getUrl());

    $response->assertOk();
});
