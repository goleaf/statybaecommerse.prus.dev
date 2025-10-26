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

final class AuditLogController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', AuditLog::class);

        $definition = new ListQueryDefinition(
            filters: [
                'entity_type' => [
                    'type' => 'string',
                    'nullable' => true,
                    'column' => 'audit_logs.entity_type',
                ],
                'entity_id' => [
                    'type' => 'string',
                    'nullable' => true,
                    'column' => 'audit_logs.entity_id',
                ],
                'action' => [
                    'type' => 'string',
                    'nullable' => true,
                    'column' => 'audit_logs.action',
                ],
            ],
            sortable: [
                'created_at' => [
                    'column' => 'audit_logs.created_at',
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

        $listQuery = ListQueryValidator::fromRequest($request, $definition);

        $query = AuditLog::query()
            ->with('user');

        // Apply the sanitized filters and sort directives surfaced from the list
        // query helper to ensure only allow-listed query parameters take effect.
        $listQuery->applyFilters($query);
        $listQuery->applySorts($query);

        if (! $listQuery->hasSort('created_at')) {
            $query->orderByDesc('audit_logs.created_at');
        }

        $query->orderByDesc('audit_logs.id');

        $paginator = $query->paginate($listQuery->perPage(), ['*'], 'page', $listQuery->page());

        return AuditLogResource::collection($paginator)
            ->additional([
                'meta' => ListResponse::meta($listQuery, $paginator),
                'links' => ListResponse::links($paginator),
            ]);
    }
}
