<?php declare(strict_types=1);

namespace App\Actions;

class CountryByZoneData
{
    public function __construct(
        public int $zoneId,
        public string $zoneCode,
        public string $zoneName,
        public int $countryId,
        public string $countryName,
        public string $countryCode,
        public string $countryFlag,
        public string $currencyCode,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            zoneId: (int) ($data['zone_id'] ?? 0),
            zoneCode: (string) ($data['zone_code'] ?? ''),
            zoneName: (string) ($data['zone_name'] ?? ''),
            countryId: (int) ($data['country_id'] ?? 0),
            countryName: (string) ($data['country_name'] ?? ''),
            countryCode: (string) ($data['country_code'] ?? ''),
            countryFlag: (string) ($data['country_flag'] ?? ''),
            currencyCode: (string) ($data['currency_code'] ?? ''),
        );
    }
}
