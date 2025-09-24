<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminUserResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);
        $users = User::with(['addresses', 'orders', 'wishlist', 'reviews', 'partners', 'referrals'])->when($request->has('search'), function ($query) use ($request) {
            $search = $request->get('search');

            return $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")->orWhere('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%");
            });
        })->when($request->has('status'), function ($query) use ($request) {
            return $query->where('is_active', $request->boolean('status'));
        })->when($request->has('role'), function ($query) use ($request) {
            return $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->get('role'));
            });
        })->paginate($request->get('per_page', 15));

        return AdminUserResource::collection($users);
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
        $stats = ['total_users' => User::count(), 'active_users' => User::where('is_active', true)->count(), 'inactive_users' => User::where('is_active', false)->count(), 'verified_users' => User::whereNotNull('email_verified_at')->count(), 'admin_users' => User::where('is_admin', true)->count(), 'users_with_orders' => User::has('orders')->count(), 'users_without_orders' => User::doesntHave('orders')->count(), 'recent_users' => User::where('created_at', '>=', now()->subDays(30))->count(), 'users_by_locale' => User::selectRaw('preferred_locale, count(*) as count')->groupBy('preferred_locale')->pluck('count', 'preferred_locale'), 'users_by_gender' => User::selectRaw('gender, count(*) as count')->whereNotNull('gender')->groupBy('gender')->pluck('count', 'gender')];

        return response()->json(['success' => true, 'data' => $stats, 'timestamp' => now()->toISOString()]);
    }

    /**
     * Handle activity functionality with proper error handling.
     */
    public function activity(Request $request, User $user): JsonResponse
    {
        $this->authorize('view', $user);
        $activity = ['user_id' => $user->id, 'user_name' => $user->name, 'last_login_at' => $user->last_login_at?->toISOString(), 'last_activity_at' => $user->last_activity_at?->toISOString(), 'login_count' => $user->login_count, 'orders_count' => $user->orders()->count(), 'reviews_count' => $user->reviews()->count(), 'wishlist_count' => $user->wishlist()->count(), 'addresses_count' => $user->addresses()->count(), 'total_spent' => $user->total_spent, 'average_order_value' => $user->average_order_value, 'last_order_date' => $user->last_order_date, 'is_on_trial' => $user->isOnTrial(), 'has_active_subscription' => $user->hasActiveSubscription(), 'subscription_status' => $user->subscription_status, 'referral_stats' => $user->referral_stats];

        return response()->json(['success' => true, 'data' => $activity, 'timestamp' => now()->toISOString()]);
    }
}
