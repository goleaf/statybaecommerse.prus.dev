<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Support\Contracts\Entities\UserContract;
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
        $contract = UserContract::fromModel($this->resource);
        $meta = $contract['meta'];

        $meta = array_merge($meta, [
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
        ]);

        if ($this->resource->relationLoaded('addresses')) {
            $meta['addresses'] = $this->resource->addresses->map(function ($address) {
                return $address->except(['user_id', 'created_at', 'updated_at'])->toArray();
            })->toArray();
        }

        if ($this->resource->relationLoaded('orders')) {
            $meta['orders'] = $this->resource->orders->map(function ($order) {
                return $order->except(['user_id', 'created_at', 'updated_at'])->toArray();
            })->toArray();
        }

        if ($this->resource->relationLoaded('wishlist')) {
            $meta['wishlist'] = $this->resource->wishlist->map(function ($product) {
                return $product->except(['created_at', 'updated_at'])->toArray();
            })->toArray();
        }

        $contract['meta'] = $meta;

        return $contract;
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
