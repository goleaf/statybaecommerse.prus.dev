<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\BrochureFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Brochure extends Model
{
    /** @use HasFactory<BrochureFactory> */
    use HasFactory;

    protected $table = 'brochures';

    protected $fillable = [
        'title',
        'description',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        self::deleting(static function (Brochure $brochure): void {
            $brochure->files()->get()->each(static function (BrochureFile $file): void {
                $file->delete();
            });
        });
    }

    /**
     * @return HasMany<BrochureFile, $this>
     */
    public function files(): HasMany
    {
        return $this->hasMany(BrochureFile::class, 'brochure_id');
    }

    /**
     * @param  Builder<static> $query
     * @return Builder<static>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    protected static function newFactory(): BrochureFactory
    {
        return BrochureFactory::new();
    }
}
