<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ScopedBy([ActiveScope::class])]
final /**
 * CollectionRule
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class CollectionRule extends Model
{
    use HasFactory;

    protected $table = 'collection_rules';

    protected $fillable = [
        'collection_id',
        'field',
        'operator',
        'value',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }
}
