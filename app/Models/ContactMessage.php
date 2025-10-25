<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use Database\Factories\ContactMessageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int         $id
 * @property string      $name
 * @property string      $email
 * @property string      $subject
 * @property string|null $phone
 * @property string|null $order_number
 * @property string      $message
 * @property string|null $ip_address
 * @property string|null $user_agent
 *
 * @method static Builder<self> orderedByName(string $direction = 'asc')
 */
final class ContactMessage extends Model
{
    /** @use HasFactory<ContactMessageFactory> */
    use HasFactory;

    use OrdersByName;

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
     * Point the shared OrdersByName scope at the subject column for predictable sorting.
     */
    protected function getNameColumn(): string
    {
        return 'subject';
    }

    /**
     * Provide the factory resolution for Laravel's model factory helpers.
     */
    protected static function newFactory(): ContactMessageFactory
    {
        return ContactMessageFactory::new();
    }
}
