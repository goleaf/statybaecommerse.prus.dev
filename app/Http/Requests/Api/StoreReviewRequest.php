<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Enums\OrderStatus;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * StoreReviewRequest
 *
 * API form request responsible for validating incoming review submissions
 * while ensuring the authenticated shopper actually purchased the product
 * they are attempting to review.
 */
final class StoreReviewRequest extends ApiRequest
{
    /**
     * Track whether the purchase verification query succeeded so we can
     * expose the flag to the controller without running duplicate lookups.
     */
    private bool $verifiedPurchase = false;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Resolve the maximum review length from configuration with a sane fallback.
        $maxLength = (int) config('shared.validation.max_review_length', 2000);

        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string', 'min:10', 'max:' . $maxLength],
            'order_id' => ['required', 'integer', Rule::exists('orders', 'id')],
        ];
    }

    /**
     * Hook into the validator to assert the product belongs to the supplied order
     * and that the order itself belongs to the authenticated customer.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $user = $this->user();
            $product = $this->route('product');
            $orderId = (int) $this->input('order_id');

            // Bail early when critical context is missing to avoid noisy errors.
            if ($user === null || ! $product instanceof Product || $orderId <= 0) {
                return;
            }

            // Check that the customer actually purchased the product through the referenced order.
            $hasPurchase = OrderItem::query()
                ->where('product_id', $product->getKey())
                ->where('order_id', $orderId)
                ->whereHas('order', function ($query) use ($user): void {
                    $query->where('user_id', $user->getKey())
                        ->whereIn('status', [
                            OrderStatus::DELIVERED->value,
                            OrderStatus::COMPLETED->value,
                        ]);
                })
                ->exists();

            if (! $hasPurchase) {
                $validator->errors()->add('order_id', __('You must purchase this product before leaving a review.'));

                return;
            }

            // Mark the request as verified so downstream logic can persist the flag.
            $this->verifiedPurchase = true;
        });
    }

    /**
     * Surface whether the associated order was confirmed as a verified purchase.
     */
    public function verifiedPurchase(): bool
    {
        return $this->verifiedPurchase;
    }
}
