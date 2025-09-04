<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Review extends Model
{
    use HasFactory, SoftDeletes;

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($review) {
            if ($review->rating < 1 || $review->rating > 5) {
                throw new \InvalidArgumentException('Rating must be between 1 and 5');
            }
        });
        
        static::updating(function ($review) {
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
        'content',
        'is_approved',
        'locale',
        'approved_at',
        'rejected_at',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_approved' => 'boolean',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
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
}


