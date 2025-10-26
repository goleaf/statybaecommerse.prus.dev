<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Support\Contracts\Entities\UserContract;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Disable the outer "data" wrapper to keep the contract payload untouched.
     *
     * @var string|null
     */
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Delegate the heavy lifting to the shared contract helper so both the
        // API and internal systems reuse the exact same shape.
        $payload = UserContract::forUser($this->resource);

        return $payload;
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        // No supplemental metadata is required for the public user contract.
        return [];
    }
}
