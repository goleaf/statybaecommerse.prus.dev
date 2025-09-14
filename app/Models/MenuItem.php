<?php

declare (strict_types=1);
namespace App\Models;

use App\Models\Scopes\VisibleScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
/**
 * MenuItem
 * 
 * Eloquent model representing the MenuItem entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $fillable
 * @method static \Illuminate\Database\Eloquent\Builder|MenuItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MenuItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MenuItem query()
 * @mixin \Eloquent
 */
#[ScopedBy([VisibleScope::class])]
final class MenuItem extends Model
{
    use HasFactory;
    protected $fillable = ['menu_id', 'parent_id', 'label', 'url', 'route_name', 'route_params', 'icon', 'sort_order', 'is_visible'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['route_params' => 'array', 'sort_order' => 'integer', 'is_visible' => 'boolean'];
    }
    /**
     * Handle menu functionality with proper error handling.
     * @return BelongsTo
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
    /**
     * Handle parent functionality with proper error handling.
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }
    /**
     * Handle children functionality with proper error handling.
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort_order');
    }
}