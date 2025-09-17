<?php

declare (strict_types=1);
namespace App\Actions;

use App\Models\Country;
use App\Models\Zone;
use Illuminate\Support\Collection;
/**
 * GetCountriesByZone
 * 
 * Action class for GetCountriesByZone single-purpose operations with validation, error handling, and result reporting.
 * 
 */
class GetCountriesByZone
{
    /**
     * Handle the job, event, or request processing.
     * @return Collection
     */
    public function handle(): Collection
    {
        $zones = Zone::with(['currency', 'countries'])->enabled()->get();
        $countriesByZone = $zones->map(function (Zone $zone) {
            return $zone->countries->map(function (Country $country) use ($zone) {
                return CountryByZoneData::fromArray(['zone_id' => $zone->id, 'zone_name' => $zone->name, 'zone_code' => $zone->code, 'country_id' => $country->id, 'country_name' => $country->name, 'country_code' => $country->cca2, 'country_flag' => $country->svg_flag, 'currency_code' => $zone->currency?->code ?? 'EUR']);
            });
        });
        return $countriesByZone->flatten(1);
    }
}