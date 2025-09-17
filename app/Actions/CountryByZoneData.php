<?php

declare (strict_types=1);
namespace App\Actions;

/**
 * CountryByZoneData
 * 
 * Action class for CountryByZoneData single-purpose operations with validation, error handling, and result reporting.
 * 
 */
class CountryByZoneData
{
    /**
     * Initialize the class instance with required dependencies.
     * @param int $zoneId
     * @param string $zoneCode
     * @param string $zoneName
     * @param int $countryId
     * @param string $countryName
     * @param string $countryCode
     * @param string $countryFlag
     * @param string $currencyCode
     */
    public function __construct(public int $zoneId, public string $zoneCode, public string $zoneName, public int $countryId, public string $countryName, public string $countryCode, public string $countryFlag, public string $currencyCode)
    {
    }
    /**
     * Handle fromArray functionality with proper error handling.
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(zoneId: (int) ($data['zone_id'] ?? 0), zoneCode: (string) ($data['zone_code'] ?? ''), zoneName: (string) ($data['zone_name'] ?? ''), countryId: (int) ($data['country_id'] ?? 0), countryName: (string) ($data['country_name'] ?? ''), countryCode: (string) ($data['country_code'] ?? ''), countryFlag: (string) ($data['country_flag'] ?? ''), currencyCode: (string) ($data['currency_code'] ?? ''));
    }
}