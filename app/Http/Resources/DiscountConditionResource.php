<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * DiscountConditionResource
 *
 * JSON transformer exposing the public shape of discount conditions for API
 * consumers while keeping presentation logic out of the controller.
 */
final class DiscountConditionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var \App\Models\DiscountCondition $condition */
        $condition = $this->resource;

        // Flatten the condition into a predictable payload for the API.
        return [
            'id'             => $condition->id,
            'type'           => $condition->type,
            'type_label'     => $condition->getTypeLabel(),
            'operator'       => $condition->operator,
            'operator_label' => $condition->getOperatorLabel(),
            'value'          => $condition->value,
            'priority'       => $condition->priority,
            'position'       => $condition->position,
            'description'    => $condition->human_readable_condition,
            'name'           => $condition->translated_name,
        ];
    }
}
