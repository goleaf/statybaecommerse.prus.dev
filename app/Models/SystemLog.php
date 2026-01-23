<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class SystemLog extends Model
{
    protected $table = 'system_logs';

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'context'   => 'array',
        'logged_at' => 'datetime',
    ];

    /**
     * @var array<int, string>
     */
    protected $guarded = [];
}
