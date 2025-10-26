<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;
use App\Support\ListQuery\ListQueryDefinition;
use App\Support\ListQuery\ListQueryValidator;
use App\Support\ListQuery\ListResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * AuditLogController
 *
 * Responsible for exposing a paginated, filterable list of audit log entries
 * for administrative interfaces, while ensuring the request only honours
 * allow-listed query parameters.
 */
final class AuditLogController extends Controller
{
    /**
     * Display a paginated collection of audit logs with filtering and sorting.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', AuditLog::class);

        $listQuery = ListQueryValidator::fromRequest($request, $this->auditLogListDefinition());

        $query = AuditLog::query()
            ->with('user');

        // Apply the sanitized filters and sort directives surfaced from the list
        // query helper to ensure only allow-listed query parameters take effect.
        $listQuery->applyFilters($query);
        $listQuery->applySorts($query);

        if (! $listQuery->hasSort('created_at')) {
            // Enforce a recency based tiebreaker whenever a different sort has
            // been requested so that pagination remains deterministic.
            $query->orderByDesc('audit_logs.created_at');
        }

        // Always add the identifier sort as the final tiebreaker to avoid
        // duplicate ordering when multiple rows share the same timestamp.
        $query->orderByDesc('audit_logs.id');

        $paginator = $query->paginate($listQuery->perPage(), ['*'], 'page', $listQuery->page());

        return AuditLogResource::collection($paginator)
            ->additional([
                'meta'  => ListResponse::meta($listQuery, $paginator),
                'links' => ListResponse::links($paginator),
            ]);
    }

    /**
     * Build the list query definition describing allowed filters and sorts.
     */
    private function auditLogListDefinition(): ListQueryDefinition
    {
        // Centralise the list configuration so any future changes stay in sync
        // with the validator and the documentation.
        return new ListQueryDefinition(
            filters: [
                'entity_type' => [
                    'type'     => 'string',
                    'nullable' => true,
                    'column'   => 'audit_logs.entity_type',
                ],
                'entity_id' => [
                    'type'     => 'string',
                    'nullable' => true,
                    'column'   => 'audit_logs.entity_id',
                ],
                'action' => [
                    'type'     => 'string',
                    'nullable' => true,
                    'column'   => 'audit_logs.action',
                ],
            ],
            sortable: [
                'created_at' => [
                    'column'            => 'audit_logs.created_at',
                    'default_direction' => 'desc',
                ],
                'id' => [
                    'column' => 'audit_logs.id',
                ],
            ],
            defaultSort: 'created_at',
            defaultDirection: 'desc',
            defaultPerPage: 25,
            maxPerPage: 100,
            minPerPage: 1,
        );
    }
}
