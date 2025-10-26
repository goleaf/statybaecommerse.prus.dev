<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AdminActivityLog persists high-level audit records for administrator driven actions
 * and user initiated privacy operations so we can surface a consolidated trail.
 *
 * @property int         $id
 * @property int         $user_id
 * @property string      $action
 * @property string      $resource_type
 * @property int|null    $resource_id
 * @property array|null  $old_values
 * @property array|null  $new_values
 * @property string|null $ip_address
 * @property string|null $user_agent
 */
final class AdminActivityLog extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'action',
        'resource_type',
        'resource_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Relate the log entry back to the actor that triggered the event so we
     * can hydrate audit tables and Filament widgets with the responsible user.
     *
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
