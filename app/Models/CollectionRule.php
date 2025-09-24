<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CollectionRule
 *
 * Eloquent model representing the CollectionRule entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property mixed $fillable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionRule query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
final class CollectionRule extends Model
{
    use HasFactory;

    protected $table = 'collection_rules';

    protected $fillable = ['collection_id', 'field', 'operator', 'value', 'position'];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return ['position' => 'integer'];
    }

    /**
     * Handle collection functionality with proper error handling.
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }
}
