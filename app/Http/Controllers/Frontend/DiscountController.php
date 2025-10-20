<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplyCouponRequest;
use App\Services\Discounts\CouponApplicationService;
use App\Services\Discounts\DiscountContextBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class DiscountController extends Controller
{
    public function __construct(
        private readonly CouponApplicationService $couponService,
        private readonly DiscountContextBuilder $contextBuilder,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $context = $this->contextBuilder->fromRequest($request);

        return response()->json([
            'coupons' => $this->couponService->getAvailableCoupons($context),
        ]);
    }

    public function coupons(Request $request): View|JsonResponse
    {
        $context = $this->contextBuilder->fromRequest($request);
        $coupons = $this->couponService->getAvailableCoupons($context);

        if ($request->wantsJson()) {
            return response()->json(['coupons' => $coupons]);
        }

        return view('frontend.discounts.coupons', [
            'coupons' => $coupons,
            'hasAppliedCoupon' => $request->session()->has('checkout.coupon.code'),
        ]);
    }

    public function applyCoupon(ApplyCouponRequest $request): JsonResponse
    {
        $context = $this->contextBuilder->fromRequest($request, $request->validated('code'));
        $result = $this->couponService->apply($request->validated('code'), $context);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function removeCoupon(Request $request): JsonResponse
    {
        $context = $this->contextBuilder->fromRequest($request);

        return response()->json($this->couponService->remove($context));
    }

    public function show(string $id): JsonResponse
    {
        return response()->json(['message' => 'Discount details not implemented yet', 'id' => $id]);
    }

    public function validate(Request $request): JsonResponse
    {
        return response()->json(['message' => 'Discount validation not implemented yet']);
    }
}
