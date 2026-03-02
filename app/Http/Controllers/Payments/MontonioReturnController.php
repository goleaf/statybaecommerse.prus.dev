<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payments;

use App\Enums\OrderPaymentState;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Payments\MontonioService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class MontonioReturnController extends Controller
{
    public function __construct(private readonly MontonioService $montonioService) {}

    /**
     * Handle the frontend redirect back from Montonio after the user completes or cancels the payment.
     */
    public function handleReturn(Request $request): RedirectResponse
    {
        $token = $request->query('order-token');

        if (! is_string($token) || $token === '') {
            session()->flash('error', __('messages.invalid_payment_return_token'));

            return redirect()->route('frontend.checkout.cancel');
        }

        try {
            $payload = $this->montonioService->validateToken($token);

            $orderNumber = $payload['merchantReference'] ?? null;
            $status = $payload['paymentStatus'] ?? null;

            if (! $orderNumber) {
                throw new Exception(__('messages.montonio_missing_merchant_reference'));
            }

            $order = Order::query()->where('number', $orderNumber)->firstOrFail();

            if ($status === 'PAID') {
                $order->payment_status = PaymentStatus::PAID;
                $order->payment_state = OrderPaymentState::PAID;
                if ($order->isDirty()) {
                    $order->save();
                }

                session()->flash('order_number', $order->number);

                return redirect()->route('frontend.checkout.success');
            }

            // If it's not paid (e.g., PENDING, FAILED, ABANDONED), we'll direct them to the order confirmation page
            // which can handle unpaid statuses safely based on standard logic.
            session()->flash('error', __('messages.payment_was_not_successful', ['status' => $status]));

            return redirect()->route('frontend.checkout.cancel');

        } catch (Exception $e) {
            session()->flash('error', __('messages.payment_verification_failed', ['error' => $e->getMessage()]));

            return redirect()->route('frontend.checkout.cancel');
        }
    }
}
