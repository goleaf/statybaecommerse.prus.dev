<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Support\Contracts\Entities\UserContract;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public static $wrap = null;

    private ?array $contractPayload = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->contractPayload = UserContract::forUser($this->resource);

        return $this->contractPayload;
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [];
    }
}
