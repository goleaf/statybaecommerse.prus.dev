<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\AuditLog;
use App\Models\User;
use App\Support\Authorization\AuthorizationMatrix;

/**
 * Policy securing read access to the audit trail API so only privileged
 * operators can enumerate sensitive mutation history entries.
 */
final class AuditLogPolicy
{
    /**
     * Determine whether the authenticated actor can view any audit log records.
     */
    public function viewAny(AdminUser|User $user): bool
    {
        // Delegate to the central authorization matrix to respect both explicit
        // permission grants and the predefined role permission bundles.
        return AuthorizationMatrix::check('audit_logs', 'viewAny', $user);
    }

    /**
     * Determine whether the actor can inspect a specific audit log entry.
     */
    public function view(AdminUser|User $user, AuditLog $auditLog): bool
    {
        // Reuse the collection-level grant so detailed reads always require the
        // same capability while allowing further tightening later if needed.
        return $this->viewAny($user);
    }
}
