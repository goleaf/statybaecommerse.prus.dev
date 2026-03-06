<?php

declare(strict_types=1);

it('does not build redirect query parameters from raw livewire request urls in relation managers', function (): void {
    $relationManagerPaths = [
        'app/Filament/Resources/ProductResource/RelationManagers/VariantsRelationManager.php',
        'app/Filament/Resources/Users/RelationManagers/AddressesRelationManager.php',
        'app/Filament/Resources/Users/RelationManagers/CouponUsagesRelationManager.php',
        'app/Filament/Resources/Users/RelationManagers/CustomerGroupsRelationManager.php',
        'app/Filament/Resources/Users/RelationManagers/DiscountRedemptionsRelationManager.php',
        'app/Filament/Resources/Users/RelationManagers/NotificationsRelationManager.php',
        'app/Filament/Resources/Users/RelationManagers/OrdersRelationManager.php',
        'app/Filament/Resources/Users/RelationManagers/PartnersRelationManager.php',
        'app/Filament/Resources/Users/RelationManagers/ReferralCodesRelationManager.php',
        'app/Filament/Resources/Users/RelationManagers/ReferralRewardsRelationManager.php',
        'app/Filament/Resources/Users/RelationManagers/ReferralsRelationManager.php',
        'app/Filament/Resources/Users/RelationManagers/SubscriberRelationManager.php',
    ];

    foreach ($relationManagerPaths as $path) {
        $source = file_get_contents(base_path($path));

        expect($source)->not->toBeFalse();

        if (! is_string($source)) {
            continue;
        }

        $unsafeRedirectPattern = "/'redirect'\\s*=>\\s*request\\(\\)->fullUrl\\(\\)/";

        expect((int) preg_match($unsafeRedirectPattern, $source))
            ->toBe(0, sprintf('%s still uses request()->fullUrl() as redirect target.', $path));

        expect($source)->toContain('resolveOwnerPageRedirectUrl');
    }
});
