<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * DiscountConditionCollection
 *
 * Wraps the condition resource to preserve the legacy "conditions" response
 * key while using Laravel's resource pipeline.
 */
final class DiscountConditionCollection extends ResourceCollection
{
    /** @var string|null */
    public static $wrap = 'conditions';

    /** @var string */
    public $collects = DiscountConditionResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Delegate the heavy lifting to the individual resource transformer.
        return DiscountConditionResource::collection($this->collection)->resolve();
    }
}
