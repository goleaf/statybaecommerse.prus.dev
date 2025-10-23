<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Zone extends Model
{
    /**
     * @use HasFactory<\Database\Factories\ZoneFactory>
     */
    /** @use HasFactory<\Database\Factories\ZoneFactory> */
    use HasFactory;

    protected $table = 'zones';

    protected $fillable = [
        'name',
        'code',
        'is_enabled',
    ];
}
