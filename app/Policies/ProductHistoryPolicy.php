<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\Product;
use App\Models\ProductHistory;
use App\Models\User;
use App\Support\Authorization\AuthorizationMatrix;

/**
 * Policy governing access to product history records so only authorized
 * operators can inspect, export, or mutate the audit timeline for a product.
 */
final class ProductHistoryPolicy
{
    /**
     * Check whether the actor may browse the history collection for the product.
     */
    public function viewAny(AdminUser|User $user, ?Product $product = null): bool
    {
        // Require both the dedicated history permission and general product
        // visibility so read-only staff cannot bypass product restrictions.
        // The product context is optional because Filament's navigation audit
        // checks only provide the authenticated user when booting menus.
        return AuthorizationMatrix::check('product_histories', 'viewAny', $user)
            && AuthorizationMatrix::check('products', 'view', $user);
    }

    /**
     * Determine whether the actor can inspect a specific history entry.
     */
    public function view(AdminUser|User $user, ProductHistory $history, Product $product): bool
    {
        if ($history->product_id !== $product->getKey()) {
            // Refuse access when the requested record does not belong to the
            // resolved product to avoid leaking cross-product history data.
            return false;
        }

        return AuthorizationMatrix::check('product_histories', 'view', $user)
            && AuthorizationMatrix::check('products', 'view', $user);
    }

    /**
     * Authorize the statistics endpoint which summarises history activity.
     */
    public function statistics(AdminUser|User $user, Product $product): bool
    {
        // Statistics reveal aggregated insights so we reuse the collection grant
        // to avoid widening the audience beyond the list endpoint.
        return $this->viewAny($user, $product);
    }

    /**
     * Determine whether the actor can queue export jobs for the product history.
     */
    public function export(AdminUser|User $user, Product $product): bool
    {
        return AuthorizationMatrix::check('product_histories', 'export', $user)
            && AuthorizationMatrix::check('products', 'view', $user);
    }

    /**
     * Determine whether the actor can append manual entries to the history log.
     */
    public function create(AdminUser|User $user, Product $product): bool
    {
        return AuthorizationMatrix::check('product_histories', 'create', $user)
            && AuthorizationMatrix::check('products', 'update', $user);
    }
}
