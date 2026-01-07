<?php

declare(strict_types=1);

namespace App\Support\Audit;

use App\Models\AdminActivityLog;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * AdminActivityLogger handles audit persistence for admin actions.
 */
final class AdminActivityLogger
{
    /**
     * Persist an audit entry for admin actions.
     *
     * @param array<string, mixed> $oldValues
     * @param array<string, mixed> $newValues
     * @param array<string, mixed> $context
     */
    public function log(
        ?Authenticatable $actor,
        string $action,
        ?Model $resource = null,
        array $oldValues = [],
        array $newValues = [],
        array $context = []
    ): void {
        // Resolve the acting user so CLI invocations gracefully fallback to
        // the authenticated context when an explicit actor is not supplied.
        $user = $actor ?? Auth::user();
        if ($user === null) {
            return;
        }

        // Capture request metadata to support downstream anomaly detection.
        $request = $this->resolveRequest();

        AdminActivityLog::query()->create([
            'user_id'       => (int) $user->getAuthIdentifier(),
            'action'        => $action,
            'resource_type' => $resource?->getMorphClass() ?? $context['resource_type'] ?? 'system',
            'resource_id'   => $resource?->getKey(),
            'old_values'    => $oldValues === [] ? null : $oldValues,
            'new_values'    => $this->mergeContext($newValues, $context),
            'ip_address'    => $request?->ip(),
            'user_agent'    => $request?->userAgent(),
        ]);
    }

    private function resolveRequest(): ?Request
    {
        // request() may not be available during queued jobs or CLI contexts;
        // guard against that scenario so background processes do not crash.
        return app()->bound('request') ? request() : null;
    }

    /**
     * @param  array<string, mixed>      $newValues
     * @param  array<string, mixed>      $context
     * @return array<string, mixed>|null
     */
    private function mergeContext(array $newValues, array $context): ?array
    {
        // Coalesce additional context information into the new value payload so
        // downstream reporting can access ancillary metadata from one column.
        if ($context !== []) {
            $newValues['context'] = $context;
        }

        return $newValues === [] ? null : $newValues;
    }
}
