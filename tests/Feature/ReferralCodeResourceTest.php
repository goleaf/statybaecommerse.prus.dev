<?php

declare(strict_types=1);

use App\Filament\Resources\ReferralCodeResource;
use App\Filament\Resources\ReferralCodeResource\Pages\ListReferralCodes;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('keeps compatibility referral code pages registered', function (): void {
    expect(ListReferralCodes::class)->toBeString();
    expect(ReferralCodeResource::getPages())->toHaveKeys(['index', 'create', 'edit']);
});
