<?php declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Address;
use App\Models\Review;
use App\Models\CartItem;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class UserController extends Controller
{
    /**
     * Display user profile page
     */
    public function profile(): View
    {
        $user = Auth::user();
        
        return view('frontend.users.profile', compact('user'));
    }

    /**
     * Display user dashboard
     */
    public function dashboard(): View
    {
        $user = Auth::user();
        
        // Get user statistics
        $stats = [
            'orders_count' => $user->orders()->count(),
            'total_spent' => $user->total_spent,
            'reviews_count' => $user->reviews()->count(),
            'wishlist_count' => $user->wishlist()->count(),
            'addresses_count' => $user->addresses()->count(),
        ];

        // Get recent orders
        $recentOrders = $user->orders()
            ->with(['items.product'])
            ->latest()
            ->limit(5)
            ->get();

        // Get recent reviews
        $recentReviews = $user->reviews()
            ->with('product')
            ->latest()
            ->limit(3)
            ->get();

        return view('frontend.users.dashboard', compact('user', 'stats', 'recentOrders', 'recentReviews'));
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone_number' => 'nullable|string|max:255',
            'gender' => 'nullable|in:male,female,other',
            'birth_date' => 'nullable|date',
            'bio' => 'nullable|string|max:1000',
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'preferred_locale' => 'required|in:en,lt',
            'timezone' => 'nullable|string|max:255',
        ]);

        $user->update($request->only([
            'name',
            'first_name',
            'last_name',
            'email',
            'phone_number',
            'gender',
            'birth_date',
            'bio',
            'company',
            'position',
            'website',
            'preferred_locale',
            'timezone',
        ]));

        return redirect()->route('users.profile')
            ->with('success', __('users.profile_updated_successfully'));
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);

        Auth::user()->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('users.profile')
            ->with('success', __('users.password_updated_successfully'));
    }

    /**
     * Update user avatar
     */
    public function updateAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();

        // Delete old avatar if exists
        if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
            Storage::disk('public')->delete($user->avatar_url);
        }

        // Store new avatar
        $avatarPath = $request->file('avatar')->store('avatars', 'public');
        
        $user->update(['avatar_url' => $avatarPath]);

        return response()->json([
            'success' => true,
            'avatar_url' => Storage::disk('public')->url($avatarPath),
            'message' => __('users.avatar_updated_successfully'),
        ]);
    }

    /**
     * Update social links
     */
    public function updateSocialLinks(Request $request): RedirectResponse
    {
        $request->validate([
            'social_links' => 'nullable|array',
            'social_links.*.platform' => 'required|string|in:facebook,twitter,instagram,linkedin,youtube,tiktok,github,website',
            'social_links.*.url' => 'required|url',
        ]);

        Auth::user()->update([
            'social_links' => $request->social_links ?? [],
        ]);

        return redirect()->route('users.profile')
            ->with('success', __('users.social_links_updated_successfully'));
    }

    /**
     * Update notification preferences
     */
    public function updateNotificationPreferences(Request $request): RedirectResponse
    {
        $request->validate([
            'notification_preferences' => 'nullable|array',
            'notification_preferences.*' => 'boolean',
        ]);

        Auth::user()->update([
            'notification_preferences' => $request->notification_preferences ?? [],
        ]);

        return redirect()->route('users.profile')
            ->with('success', __('users.notification_preferences_updated_successfully'));
    }

    /**
     * Update privacy settings
     */
    public function updatePrivacySettings(Request $request): RedirectResponse
    {
        $request->validate([
            'privacy_settings' => 'nullable|array',
            'privacy_settings.*' => 'boolean',
        ]);

        Auth::user()->update([
            'privacy_settings' => $request->privacy_settings ?? [],
        ]);

        return redirect()->route('users.profile')
            ->with('success', __('users.privacy_settings_updated_successfully'));
    }

    /**
     * Get user orders
     */
    public function orders(): View
    {
        $user = Auth::user();
        $orders = $user->orders()
            ->with(['items.product', 'items.productVariant'])
            ->latest()
            ->paginate(10);

        return view('frontend.users.orders', compact('orders'));
    }

    /**
     * Get user addresses
     */
    public function addresses(): View
    {
        $user = Auth::user();
        $addresses = $user->addresses()->latest()->get();

        return view('frontend.users.addresses', compact('addresses'));
    }

    /**
     * Get user reviews
     */
    public function reviews(): View
    {
        $user = Auth::user();
        $reviews = $user->reviews()
            ->with('product')
            ->latest()
            ->paginate(10);

        return view('frontend.users.reviews', compact('reviews'));
    }

    /**
     * Get user wishlist
     */
    public function wishlist(): View
    {
        $user = Auth::user();
        $wishlist = $user->wishlist()
            ->with(['images', 'brand', 'category'])
            ->latest()
            ->paginate(12);

        return view('frontend.users.wishlist', compact('wishlist'));
    }

    /**
     * Get user documents
     */
    public function documents(): View
    {
        $user = Auth::user();
        $documents = $user->documents()
            ->with('template')
            ->latest()
            ->paginate(10);

        return view('frontend.users.documents', compact('documents'));
    }

    /**
     * Download user document
     */
    public function downloadDocument(Document $document): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $user = Auth::user();

        if ($document->documentable_id !== $user->id || $document->documentable_type !== User::class) {
            abort(403, __('users.unauthorized_document_access'));
        }

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, __('users.document_not_found'));
        }

        return Storage::disk('public')->download(
            $document->file_path,
            $document->filename ?? 'document.pdf'
        );
    }

    /**
     * Get user statistics API
     */
    public function statistics(): JsonResponse
    {
        $user = Auth::user();

        $statistics = [
            'orders' => [
                'total' => $user->orders()->count(),
                'completed' => $user->orders()->where('status', 'completed')->count(),
                'pending' => $user->orders()->where('status', 'pending')->count(),
                'total_spent' => $user->total_spent,
                'average_order_value' => $user->average_order_value,
            ],
            'reviews' => [
                'total' => $user->reviews()->count(),
                'average_rating' => $user->average_rating,
            ],
            'wishlist' => [
                'total' => $user->wishlist()->count(),
            ],
            'addresses' => [
                'total' => $user->addresses()->count(),
            ],
            'documents' => [
                'total' => $user->documents()->count(),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }

    /**
     * Deactivate user account
     */
    public function deactivateAccount(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|current_password',
            'reason' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        
        // Log deactivation reason if provided
        if ($request->reason) {
            activity()
                ->performedOn($user)
                ->withProperties(['reason' => $request->reason])
                ->log('Account deactivated by user');
        }

        $user->update([
            'is_active' => false,
            'deactivated_at' => now(),
        ]);

        Auth::logout();

        return redirect()->route('home')
            ->with('success', __('users.account_deactivated_successfully'));
    }

    /**
     * Delete user account
     */
    public function deleteAccount(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|current_password',
            'confirmation' => 'required|accepted',
        ]);

        $user = Auth::user();

        // Log account deletion
        activity()
            ->performedOn($user)
            ->log('Account deleted by user');

        // Soft delete the user
        $user->delete();

        Auth::logout();

        return redirect()->route('home')
            ->with('success', __('users.account_deleted_successfully'));
    }
}
