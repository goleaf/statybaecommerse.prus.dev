<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AnalyticsEvent extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'event_type',
        'session_id',
        'user_id',
        'properties',
        'url',
        'referrer',
        'user_agent',
        'ip_address',
        'country_code',
        'created_at',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('event_type', $type);
    }

    public function scopeForSession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public static function track(string $eventType, array $properties = [], ?string $url = null): self
    {
        return static::create([
            'event_type' => $eventType,
            'session_id' => session()->getId(),
            'user_id' => auth()->id(),
            'properties' => $properties,
            'url' => $url ?? request()->url(),
            'referrer' => request()->header('referer'),
            'user_agent' => request()->userAgent(),
            'ip_address' => request()->ip(),
            'country_code' => null, // Can be populated by GeoIP service
            'created_at' => now(),
        ]);
    }
}
