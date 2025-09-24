<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\ApprovedScope;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Review
 *
 * Eloquent model representing the Review entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property mixed $fillable
 * @property string $translationModel
 * @property mixed $appends
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Review newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Review newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Review query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, ApprovedScope::class])]
final class Review extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    /**
     * Boot the service provider or trait functionality.
     */
    protected static function boot()
    {
        parent::boot();
        self::creating(function ($review) {
            if ($review->rating < 1 || $review->rating > 5) {
                throw new \InvalidArgumentException('Rating must be between 1 and 5');
            }
            // Ensure required reviewer fields are populated
            if (empty($review->reviewer_name) && ! empty($review->user_id)) {
                $user = User::find($review->user_id);
                if ($user) {
                    $review->reviewer_name = $user->name ?? 'Guest';
                    $review->reviewer_email = $user->email ?? 'guest@example.com';
                }
            }
            if (empty($review->reviewer_name)) {
                $review->reviewer_name = $review->reviewer_name ?? 'Guest';
            }
            if (empty($review->reviewer_email)) {
                $review->reviewer_email = $review->reviewer_email ?? 'guest@example.com';
            }
        });
        self::updating(function ($review) {
            if ($review->rating < 1 || $review->rating > 5) {
                throw new \InvalidArgumentException('Rating must be between 1 and 5');
            }
        });
    }

    protected $table = 'reviews';

    protected $fillable = ['product_id', 'user_id', 'reviewer_name', 'reviewer_email', 'rating', 'title', 'content', 'is_approved', 'is_featured', 'locale', 'approved_at', 'rejected_at', 'metadata'];

    protected string $translationModel = \App\Models\Translations\ReviewTranslation::class;

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return ['rating' => 'integer', 'is_approved' => 'boolean', 'is_featured' => 'boolean', 'approved_at' => 'datetime', 'rejected_at' => 'datetime', 'metadata' => 'array'];
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ['status', 'status_color', 'status_label', 'rating_stars', 'rating_label', 'rating_color', 'is_high_rated', 'is_low_rated', 'is_recent', 'days_old', 'reviewer_type'];

    /**
     * Handle product functionality with proper error handling.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Handle user functionality with proper error handling.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Alias for clarity: a review's author is a customer (User)
    /**
     * Handle author functionality with proper error handling.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Handle scopeApproved functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Handle scopeForLocale functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeForLocale($query, string $locale)
    {
        return $query->where('locale', $locale);
    }

    /**
     * Handle scopeByRating functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByRating($query, int $rating)
    {
        return $query->where('rating', $rating);
    }

    /**
     * Handle scopePending functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopePending($query)
    {
        return $query->withoutGlobalScope(ApprovedScope::class)->where('is_approved', false)->whereNull('rejected_at');
    }

    /**
     * Handle approve functionality with proper error handling.
     */
    public function approve(): bool
    {
        $this->is_approved = true;
        $this->approved_at = now();
        $this->rejected_at = null;

        return $this->save();
    }

    /**
     * Handle reject functionality with proper error handling.
     */
    public function reject(): bool
    {
        $this->is_approved = false;
        $this->rejected_at = now();
        $this->approved_at = null;

        return $this->save();
    }

    /**
     * Handle scopeFeatured functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Handle scopeRecent functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Handle scopeHighRated functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeHighRated($query, int $minRating = 4)
    {
        return $query->where('rating', '>=', $minRating);
    }

    /**
     * Handle scopeLowRated functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeLowRated($query, int $maxRating = 2)
    {
        return $query->where('rating', '<=', $maxRating);
    }

    /**
     * Handle getAverageRatingForProduct functionality with proper error handling.
     */
    public static function getAverageRatingForProduct(int $productId): float
    {
        return self::where('product_id', $productId)->where('is_approved', true)->avg('rating') ?? 0;
    }

    /**
     * Handle getReviewCountForProduct functionality with proper error handling.
     */
    public static function getReviewCountForProduct(int $productId): int
    {
        return self::where('product_id', $productId)->where('is_approved', true)->count();
    }

    /**
     * Handle getRatingDistributionForProduct functionality with proper error handling.
     */
    public static function getRatingDistributionForProduct(int $productId): array
    {
        return self::where('product_id', $productId)->where('is_approved', true)->selectRaw('rating, COUNT(*) as count')->groupBy('rating')->orderBy('rating')->pluck('count', 'rating')->toArray();
    }

    // Advanced Translation Methods
    /**
     * Handle getTranslatedTitle functionality with proper error handling.
     */
    public function getTranslatedTitle(?string $locale = null): ?string
    {
        return $this->trans('title', $locale) ?: $this->title;
    }

    /**
     * Handle getTranslatedComment functionality with proper error handling.
     */
    public function getTranslatedComment(?string $locale = null): ?string
    {
        return $this->trans('comment', $locale) ?: $this->content;
    }

    // Scope for translated reviews
    /**
     * Handle scopeWithTranslations functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeWithTranslations($query, ?string $locale = null)
    {
        $locale = $locale ?: app()->getLocale();

        return $query->with(['translations' => function ($q) use ($locale) {
            $q->where('locale', $locale);
        }]);
    }

    // Translation Management Methods
    /**
     * Handle getAvailableLocales functionality with proper error handling.
     */
    public function getAvailableLocales(): array
    {
        return $this->translations()->pluck('locale')->unique()->values()->toArray();
    }

    /**
     * Handle hasTranslationFor functionality with proper error handling.
     */
    public function hasTranslationFor(string $locale): bool
    {
        return $this->translations()->where('locale', $locale)->exists();
    }

    /**
     * Handle getOrCreateTranslation functionality with proper error handling.
     *
     * @return App\Models\Translations\ReviewTranslation
     */
    public function getOrCreateTranslation(string $locale): \App\Models\Translations\ReviewTranslation
    {
        return $this->translations()->firstOrCreate(['locale' => $locale], ['title' => $this->title, 'comment' => $this->comment]);
    }

    /**
     * Handle updateTranslation functionality with proper error handling.
     */
    public function updateTranslation(string $locale, array $data): bool
    {
        $translation = $this->getOrCreateTranslation($locale);

        return $translation->update($data);
    }

    /**
     * Handle updateTranslations functionality with proper error handling.
     */
    public function updateTranslations(array $translations): bool
    {
        foreach ($translations as $locale => $data) {
            $this->updateTranslation($locale, $data);
        }

        return true;
    }

    // Helper Methods
    /**
     * Handle getFullDisplayName functionality with proper error handling.
     */
    public function getFullDisplayName(?string $locale = null): string
    {
        $title = $this->getTranslatedTitle($locale);
        $rating = str_repeat('⭐', $this->rating);

        return "{$title} ({$rating})";
    }

    /**
     * Handle getReviewInfo functionality with proper error handling.
     */
    public function getReviewInfo(): array
    {
        return ['id' => $this->id, 'product_id' => $this->product_id, 'user_id' => $this->user_id, 'reviewer_name' => $this->reviewer_name, 'reviewer_email' => $this->reviewer_email, 'rating' => $this->rating, 'title' => $this->title, 'comment' => $this->comment, 'is_approved' => $this->is_approved, 'is_featured' => $this->is_featured, 'locale' => $this->locale, 'approved_at' => $this->approved_at?->toISOString(), 'rejected_at' => $this->rejected_at?->toISOString()];
    }

    /**
     * Handle getStatusInfo functionality with proper error handling.
     */
    public function getStatusInfo(): array
    {
        return ['is_approved' => $this->is_approved, 'is_featured' => $this->is_featured, 'status' => $this->getStatus(), 'status_color' => $this->getStatusColor(), 'status_label' => $this->getStatusLabel(), 'approved_at' => $this->approved_at?->toISOString(), 'rejected_at' => $this->rejected_at?->toISOString()];
    }

    /**
     * Handle getRatingInfo functionality with proper error handling.
     */
    public function getRatingInfo(): array
    {
        return ['rating' => $this->rating, 'rating_stars' => str_repeat('⭐', $this->rating), 'rating_label' => $this->getRatingLabel(), 'rating_color' => $this->getRatingColor(), 'is_high_rated' => $this->rating >= 4, 'is_low_rated' => $this->rating <= 2];
    }

    /**
     * Handle getBusinessInfo functionality with proper error handling.
     */
    public function getBusinessInfo(): array
    {
        return ['is_approved' => $this->is_approved, 'is_featured' => $this->is_featured, 'is_recent' => $this->created_at->isAfter(now()->subDays(30)), 'days_old' => $this->created_at->diffInDays(now()), 'product_name' => $this->product?->name, 'reviewer_type' => $this->user_id ? 'registered' : 'guest'];
    }

    /**
     * Handle getCompleteInfo functionality with proper error handling.
     */
    public function getCompleteInfo(?string $locale = null): array
    {
        return array_merge($this->getReviewInfo(), $this->getStatusInfo(), $this->getRatingInfo(), $this->getBusinessInfo(), ['translations' => $this->getAvailableLocales(), 'has_translations' => count($this->getAvailableLocales()) > 0, 'created_at' => $this->created_at?->toISOString(), 'updated_at' => $this->updated_at?->toISOString()]);
    }

    // Additional helper methods
    /**
     * Handle getStatus functionality with proper error handling.
     */
    public function getStatus(): string
    {
        if ($this->is_approved) {
            return 'approved';
        }
        if ($this->rejected_at) {
            return 'rejected';
        }

        return 'pending';
    }

    /**
     * Handle getStatusColor functionality with proper error handling.
     */
    public function getStatusColor(): string
    {
        return match ($this->getStatus()) {
            'approved' => 'success',
            'rejected' => 'danger',
            'pending' => 'warning',
            default => 'gray',
        };
    }

    /**
     * Handle getStatusLabel functionality with proper error handling.
     */
    public function getStatusLabel(): string
    {
        return match ($this->getStatus()) {
            'approved' => __('admin.reviews.status.approved'),
            'rejected' => __('admin.reviews.status.rejected'),
            'pending' => __('admin.reviews.status.pending'),
            default => __('admin.reviews.status.unknown'),
        };
    }

    /**
     * Handle getRatingLabel functionality with proper error handling.
     */
    public function getRatingLabel(): string
    {
        return match ($this->rating) {
            1 => __('admin.reviews.ratings.poor'),
            2 => __('admin.reviews.ratings.fair'),
            3 => __('admin.reviews.ratings.good'),
            4 => __('admin.reviews.ratings.very_good'),
            5 => __('admin.reviews.ratings.excellent'),
            default => __('admin.reviews.ratings.unknown'),
        };
    }

    /**
     * Handle getRatingColor functionality with proper error handling.
     */
    public function getRatingColor(): string
    {
        return match ($this->rating) {
            1, 2 => 'danger',
            3 => 'warning',
            4, 5 => 'success',
            default => 'gray',
        };
    }

    /**
     * Handle isRecent functionality with proper error handling.
     */
    public function isRecent(int $days = 30): bool
    {
        return $this->created_at->isAfter(now()->subDays($days));
    }

    /**
     * Handle isHighRated functionality with proper error handling.
     */
    public function isHighRated(int $minRating = 4): bool
    {
        return $this->rating >= $minRating;
    }

    /**
     * Handle isLowRated functionality with proper error handling.
     */
    public function isLowRated(int $maxRating = 2): bool
    {
        return $this->rating <= $maxRating;
    }

    // Accessors for appended attributes
    public function getStatusAttribute(): string
    {
        return $this->getStatus();
    }

    public function getStatusColorAttribute(): string
    {
        return $this->getStatusColor();
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->getStatusLabel();
    }

    public function getRatingStarsAttribute(): string
    {
        return str_repeat('⭐', (int) $this->rating);
    }

    public function getRatingLabelAttribute(): string
    {
        return $this->getRatingLabel();
    }

    public function getRatingColorAttribute(): string
    {
        return $this->getRatingColor();
    }

    public function getIsHighRatedAttribute(): bool
    {
        return $this->isHighRated();
    }

    public function getIsLowRatedAttribute(): bool
    {
        return $this->isLowRated();
    }

    public function getIsRecentAttribute(): bool
    {
        return $this->isRecent();
    }

    public function getDaysOldAttribute(): int
    {
        return $this->created_at ? $this->created_at->diffInDays(now()) : 0;
    }

    public function getReviewerTypeAttribute(): string
    {
        return $this->user_id ? 'registered' : 'guest';
    }

    /**
     * Handle canBeApproved functionality with proper error handling.
     */
    public function canBeApproved(): bool
    {
        return ! $this->is_approved && ! $this->rejected_at;
    }

    /**
     * Handle canBeRejected functionality with proper error handling.
     */
    public function canBeRejected(): bool
    {
        return ! $this->rejected_at;
    }

    /**
     * Handle canBeFeatured functionality with proper error handling.
     */
    public function canBeFeatured(): bool
    {
        return $this->is_approved && ! $this->is_featured;
    }

    /**
     * Handle canBeUnfeatured functionality with proper error handling.
     */
    public function canBeUnfeatured(): bool
    {
        return $this->is_featured;
    }
}
