<?php

declare (strict_types=1);
namespace App\Http\Controllers\Frontend;

use App\Data\DiscountCodeValidationData;
use App\Http\Controllers\Controller;
use App\Models\DiscountCode;
use App\Models\DiscountRedemption;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
/**
 * DiscountCodeController
 * 
 * HTTP controller handling DiscountCodeController related web requests, responses, and business logic with proper validation and error handling.
 * 
 */
final class DiscountCodeController extends Controller
{
    /**
     * Initialize the class instance with required dependencies.
     * @param DocumentService $documentService
     */
    public function __construct(private readonly DocumentService $documentService)
    {
    }
    /**
     * Validate the input data against defined rules.
     * @param DiscountCodeValidationData $data
     * @return JsonResponse
     */
    public function validate(DiscountCodeValidationData $data): JsonResponse
    {
        $code = DiscountCode::where('code', $data->code)->first();
        if (!$code) {
            return response()->json(['valid' => false, 'message' => __('discount_code_invalid')], 422);
        }
        if (!$code->isValid()) {
            $message = match (true) {
                $code->hasReachedLimit() => __('discount_code_limit_reached'),
                $code->expires_at && $code->expires_at->lt(now()) => __('discount_code_expired_message'),
                !$code->is_active => __('discount_code_inactive'),
                default => __('discount_code_invalid'),
            };
            return response()->json(['valid' => false, 'message' => $message], 422);
        }
        // Check if user has already used this code (if limit per user is set)
        if ($code->usage_limit_per_user && Auth::check()) {
            $userUsage = DiscountRedemption::where('code_id', $code->id)->where('user_id', Auth::id())->count();
            if ($userUsage >= $code->usage_limit_per_user) {
                return response()->json(['valid' => false, 'message' => __('discount_code_already_used')], 422);
            }
        }
        return response()->json(['valid' => true, 'message' => __('discount_code_success'), 'discount' => ['id' => $code->id, 'code' => $code->code, 'name' => $code->discount->name, 'type' => $code->discount->type, 'value' => $code->discount->value, 'description' => $code->description]]);
    }
    /**
     * Handle apply functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function apply(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string|max:50', 'order_id' => 'nullable|exists:orders,id']);
        $code = DiscountCode::where('code', $request->code)->first();
        if (!$code || !$code->isValid()) {
            return response()->json(['success' => false, 'message' => __('discount_code_invalid')], 422);
        }
        try {
            DB::beginTransaction();
            // Create redemption record
            $redemption = DiscountRedemption::create([
                'discount_id' => $code->discount_id,
                'code_id' => $code->id,
                'order_id' => $request->order_id,
                'user_id' => Auth::id(),
                'amount_saved' => 0,
                // Will be calculated based on order
                'currency_code' => 'EUR',
                'redeemed_at' => now(),
            ]);
            // Increment usage count
            $code->incrementUsage();
            DB::commit();
            return response()->json(['success' => true, 'message' => __('discount_code_success'), 'redemption' => $redemption]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => __('Something went wrong. Please try again.')], 500);
        }
    }
    /**
     * Handle remove functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function remove(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string|max:50']);
        $code = DiscountCode::where('code', $request->code)->first();
        if (!$code) {
            return response()->json(['success' => false, 'message' => __('discount_code_invalid')], 422);
        }
        // Find and remove redemption
        $redemption = DiscountRedemption::where('code_id', $code->id)->where('user_id', Auth::id())->latest()->first();
        if ($redemption) {
            $redemption->delete();
            // Decrement usage count
            $code->decrement('usage_count');
        }
        return response()->json(['success' => true, 'message' => __('discount_code_removed')]);
    }
    /**
     * Handle available functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function available(Request $request): JsonResponse
    {
        $codes = DiscountCode::active()->with('discount')->where(function ($query) {
            $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
        })->where(function ($query) {
            $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
        })->get()->skipWhile(function ($code) {
            // Skip codes that are not properly configured for display
            return empty($code->code) || empty($code->discount) || !$code->is_active || empty($code->discount_id);
        })->filter(function ($code) {
            // Filter out codes that user has already used up their limit
            if ($code->usage_limit_per_user && Auth::check()) {
                $userUsage = DiscountRedemption::where('code_id', $code->id)->where('user_id', Auth::id())->count();
                return $userUsage < $code->usage_limit_per_user;
            }
            return true;
        })->map(function ($code) {
            return ['id' => $code->id, 'code' => $code->code, 'description' => $code->description, 'discount' => ['name' => $code->discount->name, 'type' => $code->discount->type, 'value' => $code->discount->value], 'expires_at' => $code->expires_at?->format('d/m/Y'), 'remaining_uses' => $code->remaining_uses];
        });
        return response()->json(['codes' => $codes]);
    }
    /**
     * Handle generateDocument functionality with proper error handling.
     * @param Request $request
     * @param DiscountCode $discountCode
     * @return Response
     */
    public function generateDocument(Request $request, DiscountCode $discountCode): Response
    {
        $request->validate(['template_id' => 'required|exists:document_templates,id', 'format' => 'required|in:html,pdf']);
        try {
            $document = $this->documentService->generateDocument(templateId: $request->template_id, documentable: $discountCode, variables: ['DISCOUNT_CODE' => $discountCode->code, 'DISCOUNT_NAME' => $discountCode->discount->name, 'DISCOUNT_DESCRIPTION' => $discountCode->description, 'DISCOUNT_VALUE' => $discountCode->discount->value, 'DISCOUNT_TYPE' => $discountCode->discount->type, 'USAGE_LIMIT' => $discountCode->usage_limit ?? 'Unlimited', 'USAGE_COUNT' => $discountCode->usage_count, 'REMAINING_USES' => $discountCode->remaining_uses ?? 'Unlimited', 'STARTS_AT' => $discountCode->starts_at?->format('d/m/Y H:i') ?? 'Immediately', 'EXPIRES_AT' => $discountCode->expires_at?->format('d/m/Y H:i') ?? 'Never', 'STATUS' => $discountCode->status, 'IS_ACTIVE' => $discountCode->is_active ? 'Yes' : 'No'], format: $request->format);
            if ($request->format === 'pdf') {
                return response()->download($document->file_path, $document->title . '.pdf');
            }
            return response($document->content, 200, ['Content-Type' => 'text/html']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate document', 'message' => $e->getMessage()], 500);
        }
    }
}
