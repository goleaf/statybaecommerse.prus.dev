<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiscountRedemption;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * RedemptionController
 *
 * Admin focused controller responsible for exposing discount redemption data in a
 * predictable, well validated JSON structure for dashboards and asynchronous
 * tables. Every public entry point performs explicit input validation and keeps
 * database access eager loaded to avoid common performance pitfalls.
 */
final class RedemptionController extends Controller
{
    /**
     * Enumerate the statuses supported within administrative tooling.
     */
    private const SUPPORTED_STATUSES = [
        'pending',
        'redeemed',
        'expired',
        'cancelled',
        'refunded',
    ];

    /**
     * Provide a paginated listing of discount redemptions with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        // Validate the inbound filter payload to keep query construction safe.
        $validator = Validator::make($request->query(), [
            'status'      => ['nullable', 'string', Rule::in(self::SUPPORTED_STATUSES)],
            'discount_id' => ['nullable', 'integer', 'min:1'],
            'code_id'     => ['nullable', 'integer', 'min:1'],
            'user_id'     => ['nullable', 'integer', 'min:1'],
            'order_id'    => ['nullable', 'integer', 'min:1'],
            'currency'    => ['nullable', 'string', 'size:3'],
            'date_from'   => ['nullable', 'date'],
            'date_to'     => ['nullable', 'date', 'after_or_equal:date_from'],
            'search'      => ['nullable', 'string', 'max:255'],
            'per_page'    => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);

        if ($validator->fails()) {
            // Early return with a helpful payload whenever validation fails.
            return response()->json([
                'message' => 'The provided filters are invalid.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        /** @var array<string, mixed> $filters */
        $filters = $validator->validated();

        // Build the base query with all the eager-loads required by the frontend.
        $baseQuery = $this->buildFilteredQuery($filters);

        // Resolve pagination settings, clamping to reasonable boundaries.
        $perPage = (int) Arr::get($filters, 'per_page', 25);
        $perPage = max(1, min($perPage, 200));

        // Execute pagination on a cloned instance so statistics can re-use the base query.
        $paginated = (clone $baseQuery)
            ->paginate($perPage)
            ->through(fn (DiscountRedemption $redemption): array => $this->transformRedemption($redemption));

        // Gather lightweight aggregate statistics for quick dashboard summaries.
        $stats = $this->buildStats($baseQuery);

        return response()->json([
            'data' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
            'stats' => $stats,
        ]);
    }

    /**
     * Display a single redemption record with rich relationship context.
     */
    public function show(int $redemption): JsonResponse
    {
        // Load the redemption without global scopes so that soft deleted records remain accessible to admins.
        $record = DiscountRedemption::query()
            ->withoutGlobalScopes()
            ->with(['discount', 'code', 'user', 'order'])
            ->findOrFail($redemption);

        return response()->json([
            'data' => $this->transformRedemption($record),
        ]);
    }

    /**
     * Centralised helper applying the supported filters to a base query instance.
     */
    private function buildFilteredQuery(array $filters): Builder
    {
        // Start with a scope-free query and eager-load the relationships required by the admin UI.
        $query = DiscountRedemption::query()
            ->withoutGlobalScopes()
            ->with(['discount:id,name', 'code:id,code', 'user:id,name,email', 'order:id']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['discount_id'])) {
            $query->where('discount_id', $filters['discount_id']);
        }

        if (! empty($filters['code_id'])) {
            $query->where('code_id', $filters['code_id']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['order_id'])) {
            $query->where('order_id', $filters['order_id']);
        }

        if (! empty($filters['currency'])) {
            $query->where('currency_code', strtoupper((string) $filters['currency']));
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('redeemed_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('redeemed_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['search'])) {
            $term = trim((string) $filters['search']);

            $query->where(function (Builder $builder) use ($term): void {
                $builder->where('notes', 'like', "%{$term}%")
                    ->orWhere('ip_address', 'like', "%{$term}%")
                    ->orWhere('user_agent', 'like', "%{$term}%")
                    ->orWhereHas('code', function (Builder $codeQuery) use ($term): void {
                        $codeQuery->where('code', 'like', "%{$term}%");
                    })
                    ->orWhereHas('discount', function (Builder $discountQuery) use ($term): void {
                        $discountQuery->where('name', 'like', "%{$term}%");
                    })
                    ->orWhereHas('user', function (Builder $userQuery) use ($term): void {
                        $userQuery
                            ->where('name', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%");
                    });
            });
        }

        // Keep the ordering predictable by surfacing the most recent redemptions first.
        return $query
            ->orderByDesc('redeemed_at')
            ->orderByDesc('created_at');
    }

    /**
     * Transform a redemption model into an array suited for API responses.
     */
    private function transformRedemption(DiscountRedemption $redemption): array
    {
        return [
            'id'       => $redemption->id,
            'status'   => $redemption->status,
            'discount' => [
                'id'   => $redemption->discount?->id,
                'name' => $redemption->discount?->name,
            ],
            'code' => [
                'id'   => $redemption->code?->id,
                'code' => $redemption->code?->code,
            ],
            'user' => [
                'id'    => $redemption->user?->id,
                'name'  => $redemption->user?->name,
                'email' => $redemption->user?->email,
            ],
            'order' => [
                'id' => $redemption->order?->id,
            ],
            'amount_saved'  => (float) $redemption->amount_saved,
            'currency_code' => $redemption->currency_code,
            'redeemed_at'   => optional($redemption->redeemed_at)->toIso8601String(),
            'created_at'    => optional($redemption->created_at)->toIso8601String(),
            'updated_at'    => optional($redemption->updated_at)->toIso8601String(),
            'deleted_at'    => optional($redemption->deleted_at)->toIso8601String(),
            'meta'          => $redemption->meta,
        ];
    }

    /**
     * Produce aggregate statistics for the supplied query instance.
     */
    private function buildStats(Builder $query): array
    {
        // Clone the builder to ensure aggregate queries remain isolated.
        $statsBase = clone $query;

        $total = (clone $statsBase)->count();
        $totalSaved = (float) (clone $statsBase)->sum('amount_saved');
        $averageSaved = $total > 0
            ? (float) (clone $statsBase)->avg('amount_saved')
            : 0.0;

        $statusBreakdown = [];
        foreach (self::SUPPORTED_STATUSES as $status) {
            $statusBreakdown[$status] = (clone $statsBase)->where('status', $status)->count();
        }

        return [
            'total_redemptions' => $total,
            'total_saved'       => $totalSaved,
            'average_saved'     => $averageSaved,
            'by_status'         => $statusBreakdown,
        ];
    }
}
