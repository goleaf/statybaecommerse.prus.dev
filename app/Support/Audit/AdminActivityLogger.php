<?php

declare(strict_types=1);

namespace App\Support\Audit;

use App\Models\AdminActivityLog;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;
use Throwable;

/**
 * AdminActivityLogger coordinates audit persistence between the bespoke admin
 * activity table and Spatie's activity log so compliance tooling receives a
 * consistent timeline of user actions.
 */
final class AdminActivityLogger
{
    /**
     * Persist an audit entry and mirror it to the Spatie log so both systems
     * stay in sync with minimal duplication of logging logic.
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

        // Mirror the event to Spatie's activity log so existing dashboards and
        // notifications continue to function without modification.
        $activityModel = null;

        if (function_exists('activity')) {
            try {
                $activity = activity('admin')
                    ->event($action)
                    ->causedBy($user)
                    ->withProperties([
                        'old'     => $oldValues,
                        'new'     => $newValues,
                        'context' => $context,
                        'ip'      => $request?->ip(),
                        'agent'   => $request?->userAgent(),
                    ]);

                if ($resource !== null) {
                    $activity->performedOn($resource);
                }

                $activityModel = $activity->log(sprintf('Admin activity "%s" recorded.', $action));
            } catch (Throwable $exception) {
                // Swallow logging exceptions so user facing flows never fail while
                // still allowing a manual record to be written below.
                report($exception);
            }
        }

        if (! $activityModel instanceof Activity) {
            Activity::query()->create([
                'log_name'     => 'admin',
                'description'  => sprintf('Admin activity "%s" recorded.', $action),
                'subject_type' => $resource?->getMorphClass(),
                'subject_id'   => $resource?->getKey(),
                'causer_type'  => $user::class,
                'causer_id'    => $user->getAuthIdentifier(),
                'properties'   => [
                    'old'     => $oldValues,
                    'new'     => $newValues,
                    'context' => $context,
                    'ip'      => $request?->ip(),
                    'agent'   => $request?->userAgent(),
                ],
                'event' => $action,
            ]);
        }
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
