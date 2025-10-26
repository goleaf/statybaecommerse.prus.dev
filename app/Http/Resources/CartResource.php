<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @extends JsonResource<array<string, mixed>>
 */
final class CartResource extends JsonResource
{
    /**
     * Transform the underlying cart summary into a structured API payload.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array{items?: array<int, mixed>, count?: int, subtotal?: float, tax?: float, shipping?: float, discount?: float, total?: float} $summary */
        $summary = $this->resource;

        return [
            'item_count' => (int) ($summary['count'] ?? 0),
            'items'      => $summary['items'] ?? [],
            'totals'     => [
                'subtotal' => round((float) ($summary['subtotal'] ?? 0.0), 2),
                'tax'      => round((float) ($summary['tax'] ?? 0.0), 2),
                'shipping' => round((float) ($summary['shipping'] ?? 0.0), 2),
                'discount' => round((float) ($summary['discount'] ?? 0.0), 2),
                'total'    => round((float) ($summary['total'] ?? 0.0), 2),
            ],
        ];
    }
}
