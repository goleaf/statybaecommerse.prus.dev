<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use Database\Factories\ActivityLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * ActivityLog
 *
 * Eloquent model representing the ActivityLog entity for tracking user activities.
 *
 * @property mixed $fillable
 * @property mixed $casts
 * @property mixed $appends
 * @property mixed $table
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog query()
 * @method static \Database\Factories\ActivityLogFactory            factory($count = null, $state = [])
 *
 * @mixin \Eloquent
 *
 * @phpstan-use HasFactory<\Database\Factories\ActivityLogFactory>
 */
final class ActivityLog extends Model
{
    /** @phpstan-ignore-next-line missingType.generics */
    use HasFactory;

    use OrdersByName;

    /**
     * Column leveraged by the OrdersByName scope.
     */
    protected string $nameColumn = 'log_name';

    /**
     * Provide the explicitly typed factory for phpstan and IDEs.
     */
    protected static function newFactory(): ActivityLogFactory
    {
        return ActivityLogFactory::new();
    }

    protected $table = 'activity_log';

    protected $fillable = [
        'log_name',
        'description',
        'event',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties',
        'batch_uuid',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'os',
        'country',
        'is_important',
        'is_system',
        'severity',
        'category',
        'notes',
    ];

    protected $casts = [
        'properties'   => 'array',
        'is_important' => 'boolean',
        'is_system'    => 'boolean',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    /**
     * Get the user that performed the activity.
     *
     * @return BelongsTo<User, self>
     *
     * @phpstan-return BelongsTo<User, ActivityLog>
     */
    public function user(): BelongsTo
    {
        /** @var BelongsTo<User, ActivityLog> $relation */
        $relation = $this->belongsTo(User::class, 'causer_id');

        return $relation;
    }

    /**
     * Get the subject of the activity.
     *
     * @return MorphTo<Model, self>
     *
     * @phpstan-return MorphTo<Model, ActivityLog>
     */
    public function subject(): MorphTo
    {
        /** @var MorphTo<Model, ActivityLog> $relation */
        $relation = $this->morphTo();

        return $relation;
    }

    /**
     * Get the causer of the activity.
     *
     * @return MorphTo<Model, self>
     *
     * @phpstan-return MorphTo<Model, ActivityLog>
     */
    public function causer(): MorphTo
    {
        /** @var MorphTo<Model, ActivityLog> $relation */
        $relation = $this->morphTo();

        return $relation;
    }
}
