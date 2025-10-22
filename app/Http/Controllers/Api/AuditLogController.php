<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class AuditLogController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'entity_type' => ['nullable', 'string'],
            'entity_id'   => ['nullable', 'string'],
            'action'      => ['nullable', 'string'],
            'per_page'    => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = AuditLog::query()
            ->with('user')
            ->latest('created_at')
            ->orderByDesc('id'); // Stabilize ordering when timestamps match to keep the most recent mutation at the top.

        // Allow callers to scope audit logs to a concrete model type.
        if (! empty($validated['entity_type'])) {
            $query->where('entity_type', $validated['entity_type']);
        }

        // Filter by specific model identifier when needed.
        if (! empty($validated['entity_id'])) {
            $query->where('entity_id', $validated['entity_id']);
        }

        // Support action-specific drill downs (created/updated/etc.).
        if (! empty($validated['action'])) {
            $query->where('action', $validated['action']);
        }

        $perPage = $validated['per_page'] ?? 25;

        return AuditLogResource::collection($query->paginate($perPage));
    }
}
