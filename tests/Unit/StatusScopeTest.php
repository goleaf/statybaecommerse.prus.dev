<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\ReferralReward;
use App\Models\Scopes\StatusScope;
use ReflectionMethod;
use Tests\TestCase;

final class StatusScopeTest extends TestCase
{
    public function test_referral_reward_allowed_statuses_match_reward_lifecycle(): void
    {
        $scope = new StatusScope;

        $method = new ReflectionMethod(StatusScope::class, 'getAllowedStatuses');
        $method->setAccessible(true);

        /** @var array<int, string> $statuses */
        $statuses = $method->invoke($scope, new ReferralReward);

        $this->assertSame(['pending', 'applied', 'expired'], $statuses);
    }
}
