<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class ApiKey extends Model
{
    use HasFactory;

    protected $table = 'api_keys';

    protected $guarded = ['id'];

    protected $fillable = [
        'key',
        'name',
        'scopes',
        'rate_limit',
        'active',
        'last_used_at',
    ];

    protected $casts = [
        'scopes' => 'array',
        'rate_limit' => 'integer',
        'active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    /**
     * Determine if the API key has the given scope.
     */
    public function hasScope(string $scope): bool
    {
        $scopes = $this->scopes ?? [];

        if ($scope === '*') {
            return true;
        }

        return \in_array('*', $scopes, true) || \in_array($scope, $scopes, true);
    }

    /**
     * Determine if the API key has any of the provided scopes.
     *
     * @param array<int, string> $scopes
     */
    public function hasAnyScope(array $scopes): bool
    {
        if ($scopes === []) {
            return true;
        }

        $assignedScopes = $this->scopes ?? [];

        if (\in_array('*', $assignedScopes, true)) {
            return true;
        }

        return [] !== \array_intersect($assignedScopes, $scopes);
    }

    /**
     * Determine if the API key meets its configured rate limit for the given request count.
     */
    public function withinRateLimit(int $requestedCalls = 1): bool
    {
        $limit = $this->rate_limit;

        if ($limit === null || $limit <= 0) {
            return true;
        }

        return $requestedCalls <= $limit;
    }

    /**
     * Determine if the API key has a finite rate limit configured.
     */
    public function hasRateLimit(): bool
    {
        $limit = $this->rate_limit;

        return $limit !== null && $limit > 0;
    }
}
