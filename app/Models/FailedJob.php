<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class FailedJob extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $table = 'failed_jobs';

    protected $guarded = [];

    protected $casts = [
        'failed_at' => 'datetime',
    ];

    protected $appends = ['job_name'];

    public function getJobNameAttribute(): string
    {
        $payload = safe_json_decode_array($this->payload ?? '');

        return (string) ($payload['displayName'] ?? 'unknown');
    }
}
