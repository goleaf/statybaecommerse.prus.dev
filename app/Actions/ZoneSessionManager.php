<?php

declare (strict_types=1);
namespace App\Actions;

/**
 * ZoneSessionManager
 * 
 * Action class for ZoneSessionManager single-purpose operations with validation, error handling, and result reporting.
 * 
 */
class ZoneSessionManager
{
    /**
     * Handle checkSession functionality with proper error handling.
     * @return bool
     */
    public static function checkSession(): bool
    {
        return session()->exists('zone');
    }
    /**
     * Handle setSession functionality with proper error handling.
     * @param CountryByZoneData $zone
     * @return void
     */
    public static function setSession(CountryByZoneData $zone): void
    {
        if (self::checkSession()) {
            session()->forget('zone');
        }
        session()->put('zone', $zone);
    }
    /**
     * Handle getSession functionality with proper error handling.
     * @return CountryByZoneData|null
     */
    public static function getSession(): ?CountryByZoneData
    {
        return session()->get('zone');
    }
}