<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use App\Models\Scopes\UserOwnedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UserBehavior
 *
 * Eloquent model representing the UserBehavior entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 * @property mixed $casts
 * @property mixed $timestamps
 *
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehavior newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehavior newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehavior query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([UserOwnedScope::class])]
final class UserBehavior extends Model
{
    use HasFactory;
    use OrdersByName;

    /**
     * Configure the OrdersByName trait to rely on the event column for ordering analytics records.
     */
    protected string $nameColumn = 'event';

    /**
     * Allow mass assignment of the core tracking columns required for capturing behaviour snapshots.
     */
    protected $fillable = ['user_id', 'event', 'payload', 'occurred_at'];

    /**
     * Ensure JSON payloads become arrays automatically and that occurred_at is treated as a Carbon instance.
     */
    protected $casts = ['payload' => 'array', 'occurred_at' => 'datetime'];

    /**
     * Disable automatic timestamps so the occurred_at field remains the single source of truth.
     */
    public $timestamps = false;

    /**
     * Handle user functionality with proper error handling.
     */
    public function user(): BelongsTo
    {
        // Link each behaviour entry back to the originating user for downstream analytics queries.
        return $this->belongsTo(User::class);
    }
}
