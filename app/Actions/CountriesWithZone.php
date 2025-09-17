<?php

declare (strict_types=1);
namespace App\Actions;

use Illuminate\Support\Collection;
/**
 * CountriesWithZone
 * 
 * Action class for CountriesWithZone single-purpose operations with validation, error handling, and result reporting.
 * 
 */
class CountriesWithZone
{
    /**
     * Handle the job, event, or request processing.
     * @return Collection
     */
    public function handle(): Collection
    {
        return once(function () {
            return resolve(GetCountriesByZone::class)->handle();
        });
    }
}