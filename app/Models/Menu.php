<?php declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Observers\MenuObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Menu
 *
 * Eloquent model representing the Menu entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Menu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu query()
 *
 * @mixin \Eloquent
 */
#[ObservedBy([MenuObserver::class])]
#[ScopedBy([ActiveScope::class])]
final class Menu extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'name', 'location', 'description', 'is_active'];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /**
     * Handle items functionality with proper error handling.
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->whereNull('parent_id')->orderBy('sort_order');
    }

    /**
     * Handle allItems functionality with proper error handling.
     */
    public function allItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForKey(Builder $query, string $key): Builder
    {
        return $query->where('key', $key);
    }

    public function scopeForLocation(Builder $query, string $location): Builder
    {
        return $query->where('location', $location);
    }

    public function scopeWithVisibleItems(Builder $query): Builder
    {
        return $query->with([
            'allItems' => static fn($itemQuery) => $itemQuery->visible()->ordered(),
        ]);
    }

    /**
     * Scope the query to order menus alphabetically by their display name.
     *
     * Including this helper keeps ordering behaviour consistent across the
     * application whenever menus need to be rendered in a predictable list.
     */
    public function scopeOrderedByName(Builder $query): Builder
    {
        // Always order by the "name" column to keep menu listings readable.
        return $query->orderBy('name');
    }
}
