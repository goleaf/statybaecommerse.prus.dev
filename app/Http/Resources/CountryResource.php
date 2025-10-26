<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * CountryResource
 *
 * API resource transforming country models into a stable external representation.
 */
final class CountryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        // Expose only the country fields required by the public API surface.
        return [
            'id'            => $this->resource->id,
            'name'          => $this->resource->name,
            'name_official' => $this->resource->name_official,
            'cca2'          => $this->resource->cca2,
            'cca3'          => $this->resource->cca3,
            'flag'          => $this->resource->flag,
            'region'        => $this->resource->region,
            'currency_code' => $this->resource->currency_code,
            'vat_rate'      => $this->resource->vat_rate,
            'is_eu_member'  => (bool) $this->resource->is_eu_member,
            'requires_vat'  => (bool) $this->resource->requires_vat,
        ];
    }
}
