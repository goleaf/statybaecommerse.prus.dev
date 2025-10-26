<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\DiscountCondition;
use App\Models\User;
use App\Support\Authorization\AuthorizationMatrix;

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
        // Lean on the central authorization matrix to keep permissions consistent.
        return AuthorizationMatrix::check('discount_conditions', 'viewAny', $user);
    }

    /**
     * Determine whether the user can view an individual condition.
     */
    public function view(AdminUser|User $user, DiscountCondition $discountCondition): bool
    {
        // Reuse the read permission for single-resource lookups and API helpers.
        return AuthorizationMatrix::check('discount_conditions', 'view', $user);
    }
}
