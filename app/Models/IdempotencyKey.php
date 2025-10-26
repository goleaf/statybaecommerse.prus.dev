<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * IdempotencyKey
 *
 * Eloquent model that stores processed request fingerprints so repeated
 * submissions can be replayed safely without duplicating side effects.
 */
final class IdempotencyKey extends Model
{
    use HasFactory;

    /**
     * Disable mass-assignment protection because we will always fill via trusted arrays.
     */
    protected $guarded = [];

    /**
     * Handle attribute casting to keep JSON payloads and dates strongly typed.
     */
    protected function casts(): array
    {
        return [
            'response_body' => 'array',
            'locked_at'     => 'datetime',
        ];
    }
}
