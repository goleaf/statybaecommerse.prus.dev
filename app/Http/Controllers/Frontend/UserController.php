<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\DeactivateAccountRequest;
use App\Http\Requests\Frontend\DeleteAccountRequest;
use App\Http\Requests\Frontend\UpdateUserAvatarRequest;
use App\Http\Requests\Frontend\UpdateUserNotificationPreferencesRequest;
use App\Http\Requests\Frontend\UpdateUserPasswordRequest;
use App\Http\Requests\Frontend\UpdateUserPrivacySettingsRequest;
use App\Http\Requests\Frontend\UpdateUserProfileRequest;
use App\Http\Requests\Frontend\UpdateUserSocialLinksRequest;
use App\Models\Document;
use App\Models\User;
use App\Support\Audit\AdminActivityLogger;
use App\Support\Storage\SecureStorage;
use App\Support\Uploads\SecureUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * UserController
 *
 * HTTP controller handling UserController related web requests, responses, and business logic with proper validation and error handling.
 */
final class UserController extends Controller
{
    /**
     * Centralise audit logging so preference updates surface in admin reports.
     */
    public function __construct(private readonly AdminActivityLogger $activityLogger) {}

    /**
     * Handle profile functionality with proper error handling.
     */
    public function profile(): View
    {
        $user = Auth::user();

        return view('users.profile', compact('user'));
    }

    /**
     * Handle dashboard functionality with proper error handling.
     */
    public function dashboard(): View
    {
        $user = Auth::user();
        // Get user statistics
        $stats = ['orders_count' => $user->orders()->count(), 'total_spent' => $user->total_spent, 'reviews_count' => $user->reviews()->count(), 'wishlist_count' => $user->wishlist()->count(), 'addresses_count' => $user->addresses()->count()];
        // Get recent orders
        $recentOrders = $user->orders()->with(['items.product'])->latest()->limit(5)->get()->skipWhile(function ($order) {
            // Skip orders that are not properly configured for display
            return empty($order->number) || empty($order->status) || $order->total_amount <= 0 || empty($order->items);
        });
        // Get recent reviews
        $recentReviews = $user->reviews()->with('product')->latest()->limit(3)->get()->skipWhile(function ($review) {
            // Skip reviews that are not properly configured for display
            return empty($review->title) || empty($review->comment) || $review->rating <= 0 || ! $review->is_approved;
        });

        return view('users.dashboard', compact('user', 'stats', 'recentOrders', 'recentReviews'));
    }

    /**
     * Handle updateProfile functionality with proper error handling.
     */
    public function updateProfile(UpdateUserProfileRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $user->update($request->validated());

        return redirect()->route('users.profile')->with('success', __('users.profile_updated_successfully'));
    }

    /**
     * Handle updatePassword functionality with proper error handling.
     */
    public function updatePassword(UpdateUserPasswordRequest $request): RedirectResponse
    {
        Auth::user()->update(['password' => Hash::make($request->password)]);

        return redirect()->route('users.profile')->with('success', __('users.password_updated_successfully'));
    }

    /**
     * Handle updateAvatar functionality with proper error handling.
     */
    public function updateAvatar(UpdateUserAvatarRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $disk = SecureStorage::disk();

        // Resolve the uploaded avatar and fail loudly if the transport is missing the expected file instance.
        $uploadedAvatar = $request->file('avatar');
        if (! $uploadedAvatar instanceof UploadedFile) {
            abort(422, __('validation.uploaded', ['attribute' => 'avatar']));
        }

        // Clean up the previous avatar so we do not leak orphaned files when users rotate their image frequently.
        if ($user->avatar_url && Storage::disk($disk)->exists($user->avatar_url)) {
            Storage::disk($disk)->delete($user->avatar_url);
        }

        // Store the new avatar using the hardened uploader so filenames are safe and metadata stripped.
        $avatarPath = SecureUpload::storeUploadedFile(
            $uploadedAvatar,
            'avatars',
            $disk,
            UpdateUserAvatarRequest::allowedMimeTypes(),
            UpdateUserAvatarRequest::allowedExtensions(),
            UpdateUserAvatarRequest::maxFileSizeKilobytes(),
        );

        $user->update(['avatar_url' => $avatarPath]);

        return response()->json([
            'success'    => true,
            'avatar_url' => SecureStorage::temporarySignedUrl($avatarPath),
            'message'    => __('users.avatar_updated_successfully'),
        ]);
    }

    /**
     * Handle updateSocialLinks functionality with proper error handling.
     */
    public function updateSocialLinks(UpdateUserSocialLinksRequest $request): RedirectResponse
    {
        Auth::user()->update(['social_links' => $request->validated('social_links', [])]);

        return redirect()->route('users.profile')->with('success', __('users.social_links_updated_successfully'));
    }

    /**
     * Handle updateNotificationPreferences functionality with proper error handling.
     */
    public function updateNotificationPreferences(UpdateUserNotificationPreferencesRequest $request): RedirectResponse
    {
        Auth::user()->update(['notification_preferences' => $request->validated('notification_preferences', [])]);

        return redirect()->route('users.profile')->with('success', __('users.notification_preferences_updated_successfully'));
    }

    /**
     * Handle updatePrivacySettings functionality with proper error handling.
     */
    public function updatePrivacySettings(UpdateUserPrivacySettingsRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        // Capture the prior settings before persisting the update to construct
        // a meaningful audit diff for compliance reviews.
        $previousSettings = (array) $user->getAttribute('privacy_settings');
        $newSettings = $request->validated('privacy_settings', []);

        $user->update(['privacy_settings' => $newSettings]);

        $this->activityLogger->log(
            $user,
            'privacy_settings_updated',
            $user,
            ['privacy_settings' => $previousSettings],
            ['privacy_settings' => $newSettings],
            ['channel'          => 'frontend']
        );

        return redirect()->route('users.profile')->with('success', __('users.privacy_settings_updated_successfully'));
    }

    /**
     * Handle orders functionality with proper error handling.
     */
    public function orders(): View
    {
        $user = Auth::user();
        $orders = $user->orders()->with(['items.product', 'items.productVariant'])->latest()->paginate(10);

        return view('users.orders', compact('orders'));
    }

    /**
     * Handle addresses functionality with proper error handling.
     */
    public function addresses(): View
    {
        $user = Auth::user();
        $addresses = $user->addresses()->latest()->get()->skipWhile(function ($address) {
            // Skip addresses that are not properly configured for display
            return empty($address->street) || empty($address->city) || empty($address->postal_code) || empty($address->country);
        });

        return view('users.addresses', compact('addresses'));
    }

    /**
     * Handle reviews functionality with proper error handling.
     */
    public function reviews(): View
    {
        $user = Auth::user();
        $reviews = $user->reviews()->with('product')->latest()->paginate(10);

        return view('users.reviews', compact('reviews'));
    }

    /**
     * Handle wishlist functionality with proper error handling.
     */
    public function wishlist(): View
    {
        $user = Auth::user();
        $wishlist = $user->wishlist()->with(['images', 'brand', 'category'])->latest()->paginate(12);

        return view('users.wishlist', compact('wishlist'));
    }

    /**
     * Handle documents functionality with proper error handling.
     */
    public function documents(): View
    {
        $user = Auth::user();
        $documents = $user->documents()->with('template')->latest()->paginate(10);

        return view('users.documents', compact('documents'));
    }

    /**
     * Handle downloadDocument functionality with proper error handling.
     *
     * @return Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadDocument(Document $document): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $user = Auth::user();
        if ($document->documentable_id !== $user->id || $document->documentable_type !== User::class) {
            abort(403, __('users.unauthorized_document_access'));
        }
        $disk = SecureStorage::disk();
        if (! Storage::disk($disk)->exists($document->file_path)) {
            abort(404, __('users.document_not_found'));
        }

        $filename = $document->filename ?? SecureStorage::filename($document->file_path);

        return Storage::disk($disk)->download($document->file_path, $filename);
    }

    /**
     * Handle statistics functionality with proper error handling.
     */
    public function statistics(): JsonResponse
    {
        $user = Auth::user();
        $statistics = ['orders' => ['total' => $user->orders()->count(), 'completed' => $user->orders()->where('status', 'completed')->count(), 'pending' => $user->orders()->where('status', 'pending')->count(), 'total_spent' => $user->total_spent, 'average_order_value' => $user->average_order_value], 'reviews' => ['total' => $user->reviews()->count(), 'average_rating' => $user->average_rating], 'wishlist' => ['total' => $user->wishlist()->count()], 'addresses' => ['total' => $user->addresses()->count()], 'documents' => ['total' => $user->documents()->count()]];

        return response()->json(['success' => true, 'data' => $statistics]);
    }

    /**
     * Handle deactivateAccount functionality with proper error handling.
     */
    public function deactivateAccount(DeactivateAccountRequest $request): RedirectResponse
    {
        $user = Auth::user();
        // Log deactivation reason if provided
        $reason = $request->validated('reason');

        if ($reason) {
            activity()->performedOn($user)->withProperties(['reason' => $reason])->log('Account deactivated by user');
        }
        $user->update(['is_active' => false, 'deactivated_at' => now()]);
        Auth::logout();

        return redirect()->route('home')->with('success', __('users.account_deactivated_successfully'));
    }

    /**
     * Handle deleteAccount functionality with proper error handling.
     */
    public function deleteAccount(DeleteAccountRequest $request): RedirectResponse
    {
        $user = Auth::user();
        // Log account deletion
        activity()->performedOn($user)->log('Account deleted by user');
        // Soft delete the user
        $user->delete();
        Auth::logout();

        return redirect()->route('home')->with('success', __('users.account_deleted_successfully'));
    }
}
