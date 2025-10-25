<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $subject
 * @property string|null $phone
 * @property string|null $order_number
 * @property string $message
 * @property string|null $ip_address
 * @property string|null $user_agent
 */
final class ContactMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'subject',
        'phone',
        'order_number',
        'message',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope a query to order contact messages alphabetically by the contact name.
     *
     * @param Builder<ContactMessage> $query The current query builder instance.
     *
     * @return Builder<ContactMessage> The modified builder ordered by the name column.
     */
    public function scopeOrderedByName(Builder $query): Builder
    {
        // Sorting by name ensures consistent presentation in administrative listings.
        return $query->orderBy('name');
    }
}
