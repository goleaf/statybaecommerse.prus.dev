<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Simple transformer that exposes the structured audit payload to API clients
 * without leaking internal implementation details.
 *
 * @property AuditLog $resource
 */
final class AuditLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $auditLog = $this->resource;
        $user = $auditLog->user;

        return [
            'id'          => $auditLog->getKey(),
            'entity_type' => $auditLog->entity_type,
            'entity_id'   => $auditLog->entity_id,
            'action'      => $auditLog->action,
            'diff'        => $auditLog->diff,
            'user'        => $this->whenLoaded('user', static function () use ($user) {
                // Keep the payload lean while still exposing basic actor info.
                if ($user === null) {
                    return null;
                }

                return [
                    'id'    => $user->getKey(),
                    'name'  => $user->getAttribute('name'),
                    'email' => $user->getAttribute('email'),
                ];
            }),
            'created_at' => $auditLog->created_at?->toAtomString(),
            'updated_at' => $auditLog->updated_at?->toAtomString(),
        ];
    }
}
