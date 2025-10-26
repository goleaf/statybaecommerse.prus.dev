<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminUserResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Support\ListQuery\ListQueryDefinition;
use App\Support\ListQuery\ListQueryValidator;
use App\Support\ListQuery\ListResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * UserController
 *
 * HTTP controller handling UserController related web requests, responses, and business logic with proper validation and error handling.
 */
class UserController extends Controller
{
    /**
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);
        $definition = new ListQueryDefinition(
            filters: [
                'search' => [
                    'type' => 'string',
                    'callback' => static function (Builder $builder, string $term): void {
                        $builder->where(function (Builder $query) use ($term): void {
                            $query->where('name', 'like', "%{$term}%")
                                ->orWhere('email', 'like', "%{$term}%")
                                ->orWhere('first_name', 'like', "%{$term}%")
                                ->orWhere('last_name', 'like', "%{$term}%");
                        });
                    },
                ],
                'status' => [
                    'type' => 'bool',
                    'nullable' => true,
                    'callback' => static function (Builder $builder, bool $status): void {
                        $builder->where('is_active', $status);
                    },
                ],
                'role' => [
                    'type' => 'string',
                    'callback' => static function (Builder $builder, string $role): void {
                        $builder->whereHas('roles', static function (Builder $query) use ($role): void {
                            $query->where('name', $role);
                        });
                    },
                ],
            ],
            sortable: [
                'name' => ['column' => 'users.name'],
                'email' => ['column' => 'users.email'],
                'created_at' => ['column' => 'users.created_at', 'default_direction' => 'desc'],
                'last_login_at' => ['column' => 'users.last_login_at', 'default_direction' => 'desc'],
            ],
            defaultSort: 'created_at',
            defaultDirection: 'desc',
            defaultPerPage: 15,
            maxPerPage: 100,
        );

        $listQuery = ListQueryValidator::fromRequest($request, $definition);

        $query = User::with(['addresses', 'orders', 'wishlist', 'reviews', 'partners', 'referrals']);
        $listQuery->applyFilters($query);
        $listQuery->applySorts($query);

        $users = $query->paginate($listQuery->perPage(), ['*'], 'page', $listQuery->page());

        $items = AdminUserResource::collection($users->items())->resolve();

        return response()->json([
            'success' => true,
            'data' => $items,
            'meta' => array_merge([
                'timestamp' => now()->toISOString(),
                'version' => '1.0',
                'admin_view' => true,
            ], ListResponse::meta($listQuery, $users)),
            'links' => ListResponse::links($users),
        ]);
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(Request $request, User $user): AdminUserResource
    {
        $this->authorize('view', $user);
        $user->load(['addresses', 'orders', 'wishlist', 'reviews', 'partners', 'referrals']);

        return new AdminUserResource($user);
    }

    /**
     * Handle profile functionality with proper error handling.
     */
    public function profile(Request $request): UserResource
    {
        $user = $request->user();
        $user->load(['addresses', 'orders', 'wishlist']);

        return new UserResource($user);
    }

    /**
     * Handle updateProfile functionality with proper error handling.
     */
    public function updateProfile(Request $request): UserResource
    {
        $user = $request->user();
        $validated = $request->validate(['first_name' => 'sometimes|string|max:255', 'last_name' => 'sometimes|string|max:255', 'phone_number' => 'sometimes|nullable|string|max:20', 'preferred_locale' => 'sometimes|string|in:lt,en,ru,de', 'timezone' => 'sometimes|nullable|string|max:50', 'company' => 'sometimes|nullable|string|max:255', 'position' => 'sometimes|nullable|string|max:255', 'website' => 'sometimes|nullable|url|max:255', 'bio' => 'sometimes|nullable|string|max:1000', 'social_links' => 'sometimes|nullable|array', 'notification_preferences' => 'sometimes|nullable|array', 'privacy_settings' => 'sometimes|nullable|array', 'marketing_preferences' => 'sometimes|nullable|array']);
        $user->update($validated);
        $user->load(['addresses', 'orders', 'wishlist']);

        return new UserResource($user);
    }

    /**
     * Handle statistics functionality with proper error handling.
     */
    public function statistics(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);
        $thirtyDaysAgo = now()->subDays(30);
        // Aggregate key metrics in a single query so the statistics endpoint scales under high load.
        $aggregate = User::query()
            ->selectRaw('COUNT(*) as total_users')
            ->selectRaw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_users')
            ->selectRaw('SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_users')
            ->selectRaw('SUM(CASE WHEN email_verified_at IS NOT NULL THEN 1 ELSE 0 END) as verified_users')
            ->selectRaw('SUM(CASE WHEN is_admin = 1 THEN 1 ELSE 0 END) as admin_users')
            ->selectRaw('SUM(CASE WHEN EXISTS (SELECT 1 FROM orders WHERE orders.user_id = users.id) THEN 1 ELSE 0 END) as users_with_orders')
            ->selectRaw('SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as recent_users', [$thirtyDaysAgo])
            ->first();

        $usersWithOrders = (int) ($aggregate?->users_with_orders ?? 0);
        $totalUsers = (int) ($aggregate?->total_users ?? 0);

        $stats = [
            'total_users' => $totalUsers,
            'active_users' => (int) ($aggregate?->active_users ?? 0),
            'inactive_users' => (int) ($aggregate?->inactive_users ?? 0),
            'verified_users' => (int) ($aggregate?->verified_users ?? 0),
            'admin_users' => (int) ($aggregate?->admin_users ?? 0),
            'users_with_orders' => $usersWithOrders,
            'users_without_orders' => max($totalUsers - $usersWithOrders, 0),
            'recent_users' => (int) ($aggregate?->recent_users ?? 0),
            // Locale and gender breakdowns remain separate grouped queries for clarity and caching friendliness.
            'users_by_locale' => User::selectRaw('preferred_locale, count(*) as count')->groupBy('preferred_locale')->pluck('count', 'preferred_locale'),
            'users_by_gender' => User::selectRaw('gender, count(*) as count')->whereNotNull('gender')->groupBy('gender')->pluck('count', 'gender'),
        ];

        return response()->json(['success' => true, 'data' => $stats, 'timestamp' => now()->toISOString()]);
    }

    /**
     * Handle activity functionality with proper error handling.
     */
    public function activity(Request $request, User $user): JsonResponse
    {
        $this->authorize('view', $user);
        // Warm the relation counters so activity payload generation hits the database just once.
        $user->loadCount(['orders', 'reviews', 'wishlist', 'addresses']);

        $activity = [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'last_login_at' => $user->last_login_at?->toISOString(),
            'last_activity_at' => $user->last_activity_at?->toISOString(),
            'login_count' => $user->login_count,
            'orders_count' => $user->orders_count,
            'reviews_count' => $user->reviews_count,
            'wishlist_count' => $user->wishlist_count,
            'addresses_count' => $user->addresses_count,
            'total_spent' => $user->total_spent,
            'average_order_value' => $user->average_order_value,
            'last_order_date' => $user->last_order_date,
            'is_on_trial' => $user->isOnTrial(),
            'has_active_subscription' => $user->hasActiveSubscription(),
            'subscription_status' => $user->subscription_status,
            'referral_stats' => $user->referral_stats,
        ];

        return response()->json(['success' => true, 'data' => $activity, 'timestamp' => now()->toISOString()]);
}

    private function userListDefinition(): ListQueryDefinition
    {
        return ListQueryDefinition::make()
            ->defaultPerPage(15)
            ->maxPerPage(100)
            ->defaultSort('created_at', 'desc')
            ->allowedSorts([
                'created_at' => ['column' => 'created_at'],
                'name' => ['column' => ['name', 'id']],
                'email' => ['column' => ['email', 'id']],
                'last_login_at' => ['column' => 'last_login_at'],
            ])
            ->filters([
                'search' => [
                    'type' => 'string',
                    'nullable' => true,
                    'callback' => static function (Builder $builder, string $search): void {
                        $builder->where(static function (Builder $query) use ($search): void {
                            $query->where('name', 'like', '%'.$search.'%')
                                ->orWhere('email', 'like', '%'.$search.'%')
                                ->orWhere('first_name', 'like', '%'.$search.'%')
                                ->orWhere('last_name', 'like', '%'.$search.'%');
                        });
                    },
                ],
                'status' => [
                    'type' => 'bool',
                    'nullable' => true,
                    'callback' => static function (Builder $builder, bool $status): void {
                        $builder->where('is_active', $status);
                    },
                ],
                'role' => [
                    'type' => 'string',
                    'nullable' => true,
                    'callback' => static function (Builder $builder, string $role): void {
                        $builder->whereHas('roles', static function (Builder $query) use ($role): void {
                            $query->where('name', $role);
                        });
                    },
                ],
            ]);
    }
}
