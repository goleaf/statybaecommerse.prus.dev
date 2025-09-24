<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\EnabledScope;
use App\Models\Scopes\PublishedScope;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Legal
 *
 * Eloquent model representing the Legal entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property mixed $fillable
 * @property mixed $casts
 * @property string $translationModel
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Legal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Legal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Legal query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([EnabledScope::class, PublishedScope::class])]
final class Legal extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'legals';

    protected $fillable = ['key', 'type', 'is_enabled', 'is_required', 'sort_order', 'meta_data', 'published_at'];

    protected $casts = ['is_enabled' => 'boolean', 'is_required' => 'boolean', 'sort_order' => 'integer', 'meta_data' => 'array', 'published_at' => 'datetime'];

    protected string $translationModel = \App\Models\Translations\LegalTranslation::class;

    // Scopes
    /**
     * Handle scopeEnabled functionality with proper error handling.
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Handle scopeRequired functionality with proper error handling.
     */
    public function scopeRequired(Builder $query): Builder
    {
        return $query->where('is_required', true);
    }

    /**
     * Handle scopeByType functionality with proper error handling.
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Handle scopePublished functionality with proper error handling.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published_at', '<=', now());
    }

    /**
     * Handle scopeOrdered functionality with proper error handling.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('created_at');
    }

    /**
     * Handle scopeByKey functionality with proper error handling.
     */
    public function scopeByKey(Builder $query, string $key): Builder
    {
        return $query->where('key', $key);
    }

    // Accessors
    /**
     * Handle isPublished functionality with proper error handling.
     */
    protected function isPublished(): Attribute
    {
        return Attribute::make(get: fn (): bool => $this->published_at && $this->published_at->isPast());
    }

    /**
     * Handle status functionality with proper error handling.
     */
    protected function status(): Attribute
    {
        return Attribute::make(get: fn (): string => match (true) {
            ! $this->is_enabled => 'disabled',
            ! $this->is_published => 'draft',
            default => 'published',
        });
    }

    // Helper methods
    /**
     * Handle getTranslatedTitle functionality with proper error handling.
     */
    public function getTranslatedTitle(?string $locale = null): ?string
    {
        return $this->trans('title', $locale);
    }

    /**
     * Handle getTranslatedContent functionality with proper error handling.
     */
    public function getTranslatedContent(?string $locale = null): ?string
    {
        return $this->trans('content', $locale);
    }

    /**
     * Handle getTranslatedSlug functionality with proper error handling.
     */
    public function getTranslatedSlug(?string $locale = null): ?string
    {
        return $this->trans('slug', $locale);
    }

    /**
     * Handle getTranslatedSeoTitle functionality with proper error handling.
     */
    public function getTranslatedSeoTitle(?string $locale = null): ?string
    {
        return $this->trans('seo_title', $locale);
    }

    /**
     * Handle getTranslatedSeoDescription functionality with proper error handling.
     */
    public function getTranslatedSeoDescription(?string $locale = null): ?string
    {
        return $this->trans('seo_description', $locale);
    }

    /**
     * Handle getAvailableLocales functionality with proper error handling.
     */
    public function getAvailableLocales(): array
    {
        return $this->translations()->pluck('locale')->toArray();
    }

    /**
     * Handle hasTranslationFor functionality with proper error handling.
     */
    public function hasTranslationFor(string $locale): bool
    {
        return $this->translations()->where('locale', $locale)->exists();
    }

    /**
     * Handle getOrCreateTranslation functionality with proper error handling.
     *
     * @return App\Models\Translations\LegalTranslation
     */
    public function getOrCreateTranslation(string $locale): \App\Models\Translations\LegalTranslation
    {
        return $this->translations()->firstOrCreate(['locale' => $locale], ['title' => $this->key, 'slug' => \Illuminate\Support\Str::slug($this->key).'-'.$locale, 'content' => '', 'seo_title' => $this->key, 'seo_description' => '']);
    }

    /**
     * Handle updateTranslation functionality with proper error handling.
     */
    public function updateTranslation(string $locale, array $data): bool
    {
        $translation = $this->translations()->where('locale', $locale)->first();
        if (! $translation) {
            $translation = $this->getOrCreateTranslation($locale);
        }

        return $translation->update($data);
    }

    /**
     * Handle publish functionality with proper error handling.
     */
    public function publish(): bool
    {
        return $this->update(['published_at' => now()]);
    }

    /**
     * Handle unpublish functionality with proper error handling.
     */
    public function unpublish(): bool
    {
        return $this->update(['published_at' => null]);
    }

    /**
     * Handle enable functionality with proper error handling.
     */
    public function enable(): bool
    {
        return $this->update(['is_enabled' => true]);
    }

    /**
     * Handle disable functionality with proper error handling.
     */
    public function disable(): bool
    {
        return $this->update(['is_enabled' => false]);
    }

    /**
     * Handle makeRequired functionality with proper error handling.
     */
    public function makeRequired(): bool
    {
        return $this->update(['is_required' => true]);
    }

    /**
     * Handle makeOptional functionality with proper error handling.
     */
    public function makeOptional(): bool
    {
        return $this->update(['is_required' => false]);
    }

    // Static methods
    /**
     * Handle getTypes functionality with proper error handling.
     */
    public static function getTypes(): array
    {
        return ['privacy_policy' => 'Privatumo politika', 'terms_of_use' => 'Naudojimosi sąlygos', 'refund_policy' => 'Grąžinimo politika', 'shipping_policy' => 'Pristatymo politika', 'cookie_policy' => 'Slapukų politika', 'gdpr_policy' => 'GDPR politika', 'legal_notice' => 'Teisinė informacija', 'imprint' => 'Imprint', 'legal_document' => 'Teisinis dokumentas'];
    }

    /**
     * Handle getRequiredTypes functionality with proper error handling.
     */
    public static function getRequiredTypes(): array
    {
        return ['privacy_policy', 'terms_of_use'];
    }

    /**
     * Handle getByKey functionality with proper error handling.
     */
    public static function getByKey(string $key): ?self
    {
        return self::byKey($key)->enabled()->published()->first();
    }

    /**
     * Handle getRequiredDocuments functionality with proper error handling.
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public static function getRequiredDocuments(): \Illuminate\Database\Eloquent\Collection
    {
        return self::required()->enabled()->published()->ordered()->get();
    }

    /**
     * Handle getByType functionality with proper error handling.
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public static function getByType(string $type): \Illuminate\Database\Eloquent\Collection
    {
        return self::byType($type)->enabled()->published()->ordered()->get();
    }
}
