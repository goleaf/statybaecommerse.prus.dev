<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Review extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

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

    protected $fillable = [
        'product_id',
        'user_id',
        'reviewer_name',
        'reviewer_email',
        'rating',
        'title',
        'comment',
        'is_approved',
        'is_featured',
        'locale',
        'approved_at',
        'rejected_at',
        'metadata',
    ];

    protected string $translationModel = \App\Models\Translations\ReviewTranslation::class;

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_approved' => 'boolean',
            'is_featured' => 'boolean',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Alias for clarity: a review's author is a customer (User)
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeForLocale($query, string $locale)
    {
        return $query->where('locale', $locale);
    }

    public function scopeByRating($query, int $rating)
    {
        return $query->where('rating', $rating);
    }

    public function scopePending($query)
    {
        return $query->where('is_approved', false)->whereNull('rejected_at');
    }

    public function approve(): bool
    {
        $this->is_approved = true;
        $this->approved_at = now();
        $this->rejected_at = null;

        return $this->save();
    }

    public function reject(): bool
    {
        $this->is_approved = false;
        $this->rejected_at = now();
        $this->approved_at = null;

        return $this->save();
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeHighRated($query, int $minRating = 4)
    {
        return $query->where('rating', '>=', $minRating);
    }

    public function scopeLowRated($query, int $maxRating = 2)
    {
        return $query->where('rating', '<=', $maxRating);
    }

    public function getAverageRatingForProduct(int $productId): float
    {
        return self::where('product_id', $productId)
            ->where('is_approved', true)
            ->avg('rating') ?? 0;
    }

    public function getReviewCountForProduct(int $productId): int
    {
        return self::where('product_id', $productId)
            ->where('is_approved', true)
            ->count();
    }

    public function getRatingDistributionForProduct(int $productId): array
    {
        return self::where('product_id', $productId)
            ->where('is_approved', true)
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating')
            ->pluck('count', 'rating')
            ->toArray();
    }
}
