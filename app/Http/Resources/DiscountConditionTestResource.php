<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * DiscountConditionTestResource
 *
 * Resource responsible for exposing the evaluation result in a consistent
 * JSON representation.
 */
final class DiscountConditionTestResource extends JsonResource
{
    /**
     * Disable the default "data" wrapper to keep the payload flat.
     *
     * @var string|null
     */
    public static $wrap;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $payload = is_array($this->resource)
            ? $this->resource
            : (array) $this->resource;

        $matches = $payload['matches'] ?? false;
        if (! is_bool($matches)) {
            $matches = (bool) $matches;
        }

        $isValid = $payload['is_valid'] ?? false;
        if (! is_bool($isValid)) {
            $isValid = (bool) $isValid;
        }

        $description = $payload['condition_description'] ?? '';
        if (! is_string($description)) {
            $description = is_scalar($description) ? strval($description) : '';
        }

        $message = $payload['message'] ?? '';
        if (! is_string($message)) {
            $message = is_scalar($message) ? strval($message) : '';
        }

        // Directly return the evaluation details, mirroring the legacy payload.
        return [
            'matches'               => $matches,
            'is_valid'              => $isValid,
            'condition_description' => $description,
            'message'               => $message,
        ];
    }
}
