<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Data\DiscountCodeValidationData;
use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\DiscountCode;
use App\Models\DiscountRedemption;
use App\Models\DocumentTemplate;
use App\Services\DocumentService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Carbon\CarbonInterface;

/**
 * DiscountCodeController
 *
 * HTTP controller handling DiscountCodeController related web requests, responses, and business logic with proper validation and error handling.
 */
final class DiscountCodeController extends Controller
{
    /**
     * Initialize the class instance with required dependencies.
     */
    public function __construct(private readonly DocumentService $documentService) {}

    /**
     * Validate the input data against defined rules.
     */
    public function validate(DiscountCodeValidationData $data): JsonResponse
    {
        $code = DiscountCode::query()
            ->with('discount')
            ->withCode($data->code)
            ->first();
        if (! $code || ! $code->discount instanceof Discount) {
            return response()->json(['valid' => false, 'message' => __('discount_code_invalid')], 422);
        }
        if (! $code->isValid()) {
            $expiresAt = $code->expires_at instanceof CarbonInterface ? $code->expires_at : null;
            $message = match (true) {
                $code->hasReachedLimit() => __('discount_code_limit_reached'),
                $expiresAt instanceof CarbonInterface && $expiresAt->lt(now()) => __('discount_code_expired_message'),
                ! $code->is_active => __('discount_code_inactive'),
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
     */
    public function apply(\App\Http\Requests\Frontend\DiscountCodeApplyRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $code = DiscountCode::where('code', $validated['code'])->first();
        if (! $code || ! $code->isValid()) {
            return response()->json(['success' => false, 'message' => __('discount_code_invalid')], 422);
        }
        try {
            DB::beginTransaction();
            // Create redemption record
            $redemption = DiscountRedemption::create([
                'discount_id' => $code->discount_id,
                'code_id' => $code->id,
                'order_id' => $validated['order_id'] ?? null,
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
     */
    public function remove(\App\Http\Requests\Frontend\DiscountCodeRemoveRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $code = DiscountCode::where('code', $validated['code'])->first();
        if (! $code) {
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
     */
    public function available(Request $request): JsonResponse
    {
        /** @var int|null $userId */
        $userId = Auth::id();

        // Build the base query so we can apply eager loading and guard against
        // incomplete configurations directly in SQL instead of filtering later.
        $availableCodesQuery = DiscountCode::query()
            ->active()
            ->with('discount')
            ->whereHas('discount')
            ->whereNotNull('code')
            ->where('code', '<>', '');

        if ($userId !== null) {
            $availableCodesQuery->withCount([
                'redemptions as user_redemptions_count' => static function (Builder $builder) use ($userId): void {
                    $builder->where('user_id', $userId);
                },
            ]);
        }

        /** @var \Illuminate\Support\Collection<int, DiscountCode> $availableCodes */
        $availableCodes = $availableCodesQuery->get()->filter(static function (DiscountCode $code) use ($userId): bool {
            // Discard codes that lost their discount relationship due to
            // synchronisation or data clean-up tasks.
            if (! $code->discount instanceof Discount) {
                return false;
            }

            if ($userId !== null && $code->usage_limit_per_user) {
                $usageCount = (int) ($code->user_redemptions_count ?? 0);

                return $usageCount < $code->usage_limit_per_user;
            }

            return true;
        });

        /** @var \Illuminate\Support\Collection<int, array<string, mixed>> $codes */
        $codes = $availableCodes->map(static function (DiscountCode $code): array {
            /** @var Discount $discount */
            $discount = $code->discount;
            $expiresAt = $code->expires_at instanceof CarbonInterface ? $code->expires_at : null;

            return [
                'id' => $code->id,
                'code' => $code->code,
                'description' => $code->description,
                'discount' => [
                    'name' => $discount->name,
                    'type' => $discount->type,
                    'value' => $discount->value,
                ],
                'expires_at' => $expiresAt?->format('d/m/Y'),
                'remaining_uses' => $code->remaining_uses,
            ];
        })->values();

        return response()->json(['codes' => $codes->all()]);
    }

    /**
     * Handle generateDocument functionality with proper error handling.
     */
    public function generateDocument(\App\Http\Requests\Frontend\DiscountCodeGenerateDocumentRequest $request, DiscountCode $discountCode): JsonResponse|Response|RedirectResponse
    {
        /** @var array{template_id:int, format:string} $validated */
        $validated = $request->validated();
        try {
            $template = DocumentTemplate::query()->findOrFail((int) $validated['template_id']);
            $discountCode->loadMissing('discount');

            if (! $discountCode->discount instanceof Discount) {
                return response()->json([
                    'error' => 'Discount data unavailable',
                    'message' => __('discount_code_invalid'),
                ], 422);
            }

            $discount = $discountCode->discount;
            $startsAt = $discountCode->starts_at instanceof CarbonInterface ? $discountCode->starts_at : null;
            $expiresAt = $discountCode->expires_at instanceof CarbonInterface ? $discountCode->expires_at : null;

            // Prepare the variable map that will be injected into the template.
            $variables = [
                'DISCOUNT_CODE' => $discountCode->code,
                'DISCOUNT_NAME' => $discount->name,
                'DISCOUNT_DESCRIPTION' => $discountCode->description,
                'DISCOUNT_VALUE' => $discount->value,
                'DISCOUNT_TYPE' => $discount->type,
                'USAGE_LIMIT' => $discountCode->usage_limit ?? 'Unlimited',
                'USAGE_COUNT' => $discountCode->usage_count,
                'REMAINING_USES' => $discountCode->remaining_uses ?? 'Unlimited',
                'STARTS_AT' => $startsAt?->format('d/m/Y H:i') ?? 'Immediately',
                'EXPIRES_AT' => $expiresAt?->format('d/m/Y H:i') ?? 'Never',
                'STATUS' => $discountCode->status,
                'IS_ACTIVE' => $discountCode->is_active ? 'Yes' : 'No',
            ];

            $document = $this->documentService->generateDocument($template, $discountCode, $variables);

            if ($validated['format'] === 'pdf') {
                // Generate a signed download URL for the generated PDF payload and
                // return an appropriate response based on the caller expectations.
                $downloadUrl = $this->documentService->generatePdf($document);

                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'document_id' => $document->id,
                        'download_url' => $downloadUrl,
                    ]);
                }

                return redirect()->away($downloadUrl);
            }

            return response($document->content, 200, ['Content-Type' => 'text/html']);
        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'error' => 'Template not found',
                'message' => $exception->getMessage(),
            ], 404);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Failed to generate document', 'message' => $e->getMessage()], 500);
        }
    }
}
