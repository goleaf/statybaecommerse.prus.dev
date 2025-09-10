<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'date_range',
        'start_date',
        'end_date',
        'filters',
        'description',
        'is_active',
    ];

    protected $casts = [
        'filters' => 'array',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];
}
