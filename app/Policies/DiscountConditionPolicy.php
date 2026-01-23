<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\DiscountCondition;
use App\Models\User;

/**
 * DiscountConditionPolicy
 *
 * Governs access to discount condition resources so sensitive configuration
 * is only exposed to permitted operators.
 */
final class DiscountConditionPolicy
{
    /**
     * Determine whether the user can list discount conditions.
     */
    public function viewAny(AdminUser|User $user): bool
    {
        return $user instanceof AdminUser;
    }

    /**
     * Determine whether the user can view an individual condition.
     */
    public function view(AdminUser|User $user, DiscountCondition $discountCondition): bool
    {
        return $user instanceof AdminUser;
    }
}
