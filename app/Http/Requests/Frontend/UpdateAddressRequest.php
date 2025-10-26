<?php

declare(strict_types=1);

namespace App\Http\Requests\Frontend;

use App\Models\Address;
use Illuminate\Support\Facades\Auth;

/**
 * UpdateAddressRequest
 *
 * Validates address updates while ensuring that only the owner may make
 * modifications to the resource.
 */
final class UpdateAddressRequest extends AddressRequest
{
    /**
     * Determine if the user is authorised to make this request.
     */
    public function authorize(): bool
    {
        /** @var Address|null $address */
        $address = $this->route('address');

        // Only allow updates when the user owns the record.
        return Auth::check() && $address instanceof Address && $address->user_id === Auth::id();
    }
}
