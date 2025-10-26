<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Country;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * CountryPolicy
 *
 * Defines the access rules governing country catalogue interactions.
 */
final class CountryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?Authenticatable $user): bool
    {
        // Allow guests and authenticated users alike to view countries.
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?Authenticatable $user, Country $country): bool
    {
        // Allow the same visibility rules as the listing endpoint for individual models.
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can view aggregated statistics.
     */
    public function viewStatistics(?Authenticatable $user): bool
    {
        // Statistics are considered public information for the storefront.
        return true;
    }

    /**
     * Determine whether the user can view the list of EU members.
     */
    public function viewEuMembers(?Authenticatable $user): bool
    {
        // EU membership flags are also public storefront data.
        return true;
    }

    /**
     * Determine whether the user can view the list of countries with VAT obligations.
     */
    public function viewVatCountries(?Authenticatable $user): bool
    {
        // VAT requirements should remain accessible to customers and guests alike.
        return true;
    }
}
