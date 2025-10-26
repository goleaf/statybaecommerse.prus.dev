<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * CountryStatisticsResource
 *
 * Resource wrapper providing a consistent structure for aggregated country statistics.
 */
final class CountryStatisticsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        // Return the statistics payload while casting values for predictable serialization.
        return [
            'total_countries'    => (int) ($this->resource['total_countries'] ?? 0),
            'active_countries'   => (int) ($this->resource['active_countries'] ?? 0),
            'eu_members'         => (int) ($this->resource['eu_members'] ?? 0),
            'countries_with_vat' => (int) ($this->resource['countries_with_vat'] ?? 0),
            'average_vat_rate'   => $this->resource['average_vat_rate'] !== null
                ? (float) $this->resource['average_vat_rate']
                : null,
            'by_region'   => $this->resource['by_region'] ?? [],
            'by_currency' => $this->resource['by_currency'] ?? [],
        ];
    }
}
