<?php

declare (strict_types=1);
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\User;
use App\Rules\UrlRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
/**
 * UserController
 * 
 * HTTP controller handling UserController related web requests, responses, and business logic with proper validation and error handling.
 * 
 */
final class UserController extends Controller
{
    /**
     * Handle profile functionality with proper error handling.
     * @return View
     */
    public function profile(): View
    {
        $user = Auth::user();
        return view('users.profile', compact('user'));
    }
    /**
     * Handle dashboard functionality with proper error handling.
     * @return View
     */
    public function dashboard(): View
    {
        $user = Auth::user();
        // Get user statistics
        $stats = ['orders_count' => $user->orders()->count(), 'total_spent' => $user->total_spent, 'reviews_count' => $user->reviews()->count(), 'wishlist_count' => $user->wishlist()->count(), 'addresses_count' => $user->addresses()->count()];
        // Get recent orders
        $recentOrders = $user->orders()->with(['items.product'])->latest()->limit(5)->get()
            ->skipWhile(function ($order) {
                // Skip orders that are not properly configured for display
                return empty($order->number) || 
                       empty($order->status) ||
                       $order->total_amount <= 0 ||
                       empty($order->items);
            });
        // Get recent reviews
        $recentReviews = $user->reviews()->with('product')->latest()->limit(3)->get()
            ->skipWhile(function ($review) {
                // Skip reviews that are not properly configured for display
                return empty($review->title) || 
                       empty($review->comment) ||
                       $review->rating <= 0 ||
                       !$review->is_approved;
            });
        return view('users.dashboard', compact('user', 'stats', 'recentOrders', 'recentReviews'));
    }
    /**
     * Handle updateProfile functionality with proper error handling.
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $request->validate(['name' => 'required|string|max:255', 'first_name' => 'nullable|string|max:255', 'last_name' => 'nullable|string|max:255', 'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)], 'phone_number' => 'nullable|string|max:255', 'gender' => 'nullable|in:male,female,other', 'birth_date' => 'nullable|date', 'bio' => 'nullable|string|max:1000', 'company' => 'nullable|string|max:255', 'position' => 'nullable|string|max:255', 'website' => ['nullable', new UrlRule(), 'max:255'], 'preferred_locale' => 'required|in:en,lt', 'timezone' => 'nullable|string|max:255']);
        $user->update($request->only(['name', 'first_name', 'last_name', 'email', 'phone_number', 'gender', 'birth_date', 'bio', 'company', 'position', 'website', 'preferred_locale', 'timezone']));
        return redirect()->route('users.profile')->with('success', __('users.profile_updated_successfully'));
    }
    /**
     * Handle updatePassword functionality with proper error handling.
     * @param Request $request
     * @return RedirectResponse
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate(['current_password' => 'required|current_password', 'password' => 'required|string|min:8|confirmed']);
        Auth::user()->update(['password' => Hash::make($request->password)]);
        return redirect()->route('users.profile')->with('success', __('users.password_updated_successfully'));
    }
    /**
     * Handle updateAvatar functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function updateAvatar(Request $request): JsonResponse
    {
        $request->validate(['avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048']);
        $user = Auth::user();
        // Delete old avatar if exists
        if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
            Storage::disk('public')->delete($user->avatar_url);
        }
        // Store new avatar
        $avatarPath = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar_url' => $avatarPath]);
        return response()->json(['success' => true, 'avatar_url' => Storage::disk('public')->url($avatarPath), 'message' => __('users.avatar_updated_successfully')]);
    }
    /**
     * Handle updateSocialLinks functionality with proper error handling.
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateSocialLinks(Request $request): RedirectResponse
    {
        $request->validate(['social_links' => 'nullable|array', 'social_links.*.platform' => 'required|string|in:facebook,twitter,instagram,linkedin,youtube,tiktok,github,website', 'social_links.*.url' => ['required', new UrlRule()]]);
        Auth::user()->update(['social_links' => $request->social_links ?? []]);
        return redirect()->route('users.profile')->with('success', __('users.social_links_updated_successfully'));
    }
    /**
     * Handle updateNotificationPreferences functionality with proper error handling.
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateNotificationPreferences(Request $request): RedirectResponse
    {
        $request->validate(['notification_preferences' => 'nullable|array', 'notification_preferences.*' => 'boolean']);
        Auth::user()->update(['notification_preferences' => $request->notification_preferences ?? []]);
        return redirect()->route('users.profile')->with('success', __('users.notification_preferences_updated_successfully'));
    }
    /**
     * Handle updatePrivacySettings functionality with proper error handling.
     * @param Request $request
     * @return RedirectResponse
     */
    public function updatePrivacySettings(Request $request): RedirectResponse
    {
        $request->validate(['privacy_settings' => 'nullable|array', 'privacy_settings.*' => 'boolean']);
        Auth::user()->update(['privacy_settings' => $request->privacy_settings ?? []]);
        return redirect()->route('users.profile')->with('success', __('users.privacy_settings_updated_successfully'));
    }
    /**
     * Handle orders functionality with proper error handling.
     * @return View
     */
    public function orders(): View
    {
        $user = Auth::user();
        $orders = $user->orders()->with(['items.product', 'items.productVariant'])->latest()->paginate(10);
        return view('users.orders', compact('orders'));
    }
    /**
     * Handle addresses functionality with proper error handling.
     * @return View
     */
    public function addresses(): View
    {
        $user = Auth::user();
        $addresses = $user->addresses()->latest()->get();
        return view('users.addresses', compact('addresses'));
    }
    /**
     * Handle reviews functionality with proper error handling.
     * @return View
     */
    public function reviews(): View
    {
        $user = Auth::user();
        $reviews = $user->reviews()->with('product')->latest()->paginate(10);
        return view('users.reviews', compact('reviews'));
    }
    /**
     * Handle wishlist functionality with proper error handling.
     * @return View
     */
    public function wishlist(): View
    {
        $user = Auth::user();
        $wishlist = $user->wishlist()->with(['images', 'brand', 'category'])->latest()->paginate(12);
        return view('users.wishlist', compact('wishlist'));
    }
    /**
     * Handle documents functionality with proper error handling.
     * @return View
     */
    public function documents(): View
    {
        $user = Auth::user();
        $documents = $user->documents()->with('template')->latest()->paginate(10);
        return view('users.documents', compact('documents'));
    }
    /**
     * Handle downloadDocument functionality with proper error handling.
     * @param Document $document
     * @return Symfony\Component\HttpFoundation\BinaryFileResponse
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
        return Storage::disk('public')->download($document->file_path, $document->filename ?? 'document.pdf');
    }
    /**
     * Handle statistics functionality with proper error handling.
     * @return JsonResponse
     */
    public function statistics(): JsonResponse
    {
        $user = Auth::user();
        $statistics = ['orders' => ['total' => $user->orders()->count(), 'completed' => $user->orders()->where('status', 'completed')->count(), 'pending' => $user->orders()->where('status', 'pending')->count(), 'total_spent' => $user->total_spent, 'average_order_value' => $user->average_order_value], 'reviews' => ['total' => $user->reviews()->count(), 'average_rating' => $user->average_rating], 'wishlist' => ['total' => $user->wishlist()->count()], 'addresses' => ['total' => $user->addresses()->count()], 'documents' => ['total' => $user->documents()->count()]];
        return response()->json(['success' => true, 'data' => $statistics]);
    }
    /**
     * Handle deactivateAccount functionality with proper error handling.
     * @param Request $request
     * @return RedirectResponse
     */
    public function deactivateAccount(Request $request): RedirectResponse
    {
        $request->validate(['password' => 'required|current_password', 'reason' => 'nullable|string|max:500']);
        $user = Auth::user();
        // Log deactivation reason if provided
        if ($request->reason) {
            activity()->performedOn($user)->withProperties(['reason' => $request->reason])->log('Account deactivated by user');
        }
        $user->update(['is_active' => false, 'deactivated_at' => now()]);
        Auth::logout();
        return redirect()->route('home')->with('success', __('users.account_deactivated_successfully'));
    }
    /**
     * Handle deleteAccount functionality with proper error handling.
     * @param Request $request
     * @return RedirectResponse
     */
    public function deleteAccount(Request $request): RedirectResponse
    {
        $request->validate(['password' => 'required|current_password', 'confirmation' => 'required|accepted']);
        $user = Auth::user();
        // Log account deletion
        activity()->performedOn($user)->log('Account deleted by user');
        // Soft delete the user
        $user->delete();
        Auth::logout();
        return redirect()->route('home')->with('success', __('users.account_deleted_successfully'));
    }
}