<?php

declare(strict_types=1);

use App\Filament\Resources\ReferralResource;
use App\Filament\Resources\ReferralResource\Pages\ListReferrals;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('keeps compatibility referral pages registered', function (): void {
    expect(ListReferrals::class)->toBeString();
    expect(ReferralResource::getPages())->toHaveKeys(['index', 'create', 'edit']);
});
