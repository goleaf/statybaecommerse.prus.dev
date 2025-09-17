<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Use the except() method to exclude sensitive fields
        $safeAttributes = $this->resource->toApiSafeArray();
        
        return array_merge($safeAttributes, [
            // Add computed fields that are safe for API
            'full_name' => $this->resource->full_name,
            'initials' => $this->resource->initials,
            'avatar_url' => $this->resource->avatar_url,
            'is_email_verified' => $this->resource->isEmailVerified(),
            'is_phone_verified' => $this->resource->isPhoneVerified(),
            'has_two_factor' => $this->resource->hasTwoFactor(),
            'is_on_trial' => $this->resource->isOnTrial(),
            'has_active_subscription' => $this->resource->hasActiveSubscription(),
            'subscription_status_color' => $this->resource->subscription_status_color,
            'status_color' => $this->resource->status_color,
            'status_text' => $this->resource->status_text,
            'age' => $this->resource->age,
            'gender_text' => $this->resource->gender_text,
            'locale_text' => $this->resource->locale_text,
            'roles_label' => $this->resource->roles_label,
            
            // Add relationships if loaded
            'addresses' => $this->whenLoaded('addresses', function () {
                return $this->resource->addresses->map(function ($address) {
                    return $address->except(['user_id', 'created_at', 'updated_at'])->toArray();
                });
            }),
            
            'orders' => $this->whenLoaded('orders', function () {
                return $this->resource->orders->map(function ($order) {
                    return $order->except(['user_id', 'created_at', 'updated_at'])->toArray();
                });
            }),
            
            'wishlist' => $this->whenLoaded('wishlist', function () {
                return $this->resource->wishlist->map(function ($product) {
                    return $product->except(['created_at', 'updated_at'])->toArray();
                });
            }),
        ]);
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'timestamp' => now()->toISOString(),
                'version' => '1.0',
            ],
        ];
    }
}
