<?php

declare(strict_types=1);

namespace App\Http\Requests\Frontend;

use Illuminate\Support\Facades\Auth;

/**
 * StoreAddressRequest
 *
 * Request object responsible for validating the creation of address records
 * while enforcing strict ownership semantics.
 */
final class StoreAddressRequest extends AddressRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        // Only authenticated users may create address records for themselves.
        return Auth::check();
    }
}
