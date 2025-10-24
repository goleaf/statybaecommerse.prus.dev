<?php declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\EnabledScope;
use App\Models\Scopes\PublishedScope;
use App\Services\Security\HtmlContentSanitizer;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Legal
 *
 * Eloquent model representing the Legal entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property int                             $id
 * @property string                          $key
 * @property string                          $type
 * @property bool                            $is_enabled
 * @property bool                            $is_required
 * @property int                             $sort_order
 * @property array<string, mixed>|null       $meta_data
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read bool                                                                          $is_published
 * @property-read string                                                                        $status
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Translations\LegalTranslation> $translations
 *
 * @method static \Illuminate\Database\Eloquent\Builder<Legal> newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<Legal> newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<Legal> query()
 * @method static \Illuminate\Database\Eloquent\Builder<Legal> enabled()
 * @method static \Illuminate\Database\Eloquent\Builder<Legal> required()
 * @method static \Illuminate\Database\Eloquent\Builder<Legal> byType(string $type)
 * @method static \Illuminate\Database\Eloquent\Builder<Legal> published()
 * @method static \Illuminate\Database\Eloquent\Builder<Legal> ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<Legal> byKey(string $key)
 *
 * @mixin \Eloquent
 */
#[ScopedBy([EnabledScope::class, PublishedScope::class])]
final class Legal extends Model
{
    /** @use HasFactory<\Database\Factories\LegalFactory> */
    use HasFactory;
    use HasTranslations;

    protected $table = 'legals';

    protected $fillable = ['key', 'type', 'is_enabled', 'is_required', 'sort_order', 'meta_data', 'published_at'];

    protected $casts = ['is_enabled' => 'boolean', 'is_required' => 'boolean', 'sort_order' => 'integer', 'meta_data' => 'array', 'published_at' => 'datetime'];

    protected string $translationModel = \App\Models\Translations\LegalTranslation::class;

    // Scopes

    /**
     * Scope a query to only include enabled legal documents.
     *
     * @param  Builder<Legal> $query
     * @return Builder<Legal>
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope a query to only include required legal documents.
     *
     * @param  Builder<Legal> $query
     * @return Builder<Legal>
     */
    public function scopeRequired(Builder $query): Builder
    {
        return $query->where('is_required', true);
    }

    /**
     * Scope a query to filter by document type.
     *
     * @param  Builder<Legal> $query
     * @return Builder<Legal>
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include published documents.
     *
     * @param  Builder<Legal> $query
     * @return Builder<Legal>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published_at', '<=', now());
    }

    /**
     * Scope a query to order documents by sort order and creation date.
     *
     * @param  Builder<Legal> $query
     * @return Builder<Legal>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('created_at');
    }

    /**
     * Scope a query to find a document by its unique key.
     *
     * @param  Builder<Legal> $query
     * @return Builder<Legal>
     */
    public function scopeByKey(Builder $query, string $key): Builder
    {
        return $query->where('key', $key);
    }

    // Accessors

    /**
     * Determine if the legal document is published.
     *
     * @return Attribute<bool, never>
     */
    protected function isPublished(): Attribute
    {
        return Attribute::make(
            get: fn(): bool => $this->published_at && $this->published_at->isPast()
        );
    }

    /**
     * Get the document's current status.
     *
     * @return Attribute<string, never>
     */
    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn(): string => match (true) {
                !$this->is_enabled => 'disabled',
                !$this->is_published => 'draft',
                default => 'published',
            }
        );
    }

    // Helper methods

    /**
     * Get the translated title for a given locale.
     */
    public function getTranslatedTitle(?string $locale = null): ?string
    {
        $title = $this->trans('title', $locale);

        return is_string($title) ? $title : null;
    }

    /**
     * Get the sanitized translated content for a given locale.
     */
    public function getTranslatedContent(?string $locale = null): ?string
    {
        $content = $this->trans('content', $locale);
        if ($content === null || !is_string($content)) {
            return null;
        }

        return app(HtmlContentSanitizer::class)->sanitize($content);
    }

    /**
     * Get the translated slug for a given locale.
     */
    public function getTranslatedSlug(?string $locale = null): ?string
    {
        $slug = $this->trans('slug', $locale);

        return is_string($slug) ? $slug : null;
    }

    /**
     * Get the translated SEO title for a given locale.
     */
    public function getTranslatedSeoTitle(?string $locale = null): ?string
    {
        $seoTitle = $this->trans('seo_title', $locale);

        return is_string($seoTitle) ? $seoTitle : null;
    }

    /**
     * Get the translated SEO description for a given locale.
     */
    public function getTranslatedSeoDescription(?string $locale = null): ?string
    {
        $seoDescription = $this->trans('seo_description', $locale);

        return is_string($seoDescription) ? $seoDescription : null;
    }

    /**
     * Get all available locales for this document.
     *
     * @return array<int, string>
     */
    public function getAvailableLocales(): array
    {
        /** @var array<int, string> $locales */
        $locales = $this->translations()->pluck('locale')->toArray();

        return $locales;
    }

    /**
     * Check if a translation exists for the given locale.
     */
    public function hasTranslationFor(string $locale): bool
    {
        return $this->translations()->where('locale', $locale)->exists();
    }

    /**
     * Get or create a translation for the given locale.
     */
    public function getOrCreateTranslation(string $locale): \App\Models\Translations\LegalTranslation
    {
        /** @var \App\Models\Translations\LegalTranslation $translation */
        $translation = $this->translations()->firstOrCreate(
            ['locale' => $locale],
            [
                'title' => $this->key,
                'slug' => \Illuminate\Support\Str::slug($this->key) . '-' . $locale,
                'content' => '',
                'seo_title' => $this->key,
                'seo_description' => '',
            ]
        );

        return $translation;
    }

    /**
     * Update a translation for the given locale.
     *
     * @param array<string, mixed> $data
     */
    public function updateTranslation(string $locale, array $data): bool
    {
        $translation = $this->translations()->where('locale', $locale)->first();
        if (!$translation) {
            $translation = $this->getOrCreateTranslation($locale);
        }

        return $translation->update($data);
    }

    /**
     * Publish the legal document.
     */
    public function publish(): bool
    {
        return $this->update(['published_at' => now()]);
    }

    /**
     * Unpublish the legal document.
     */
    public function unpublish(): bool
    {
        return $this->update(['published_at' => null]);
    }

    /**
     * Enable the legal document.
     */
    public function enable(): bool
    {
        return $this->update(['is_enabled' => true]);
    }

    /**
     * Disable the legal document.
     */
    public function disable(): bool
    {
        return $this->update(['is_enabled' => false]);
    }

    /**
     * Mark the document as required.
     */
    public function makeRequired(): bool
    {
        return $this->update(['is_required' => true]);
    }

    /**
     * Mark the document as optional.
     */
    public function makeOptional(): bool
    {
        return $this->update(['is_required' => false]);
    }

    // Static methods

    /**
     * Get all available legal document types.
     *
     * @return array<string, string>
     */
    public static function getTypes(): array
    {
        return [
            'privacy_policy' => 'Privatumo politika',
            'terms_of_use' => 'Naudojimosi sąlygos',
            'refund_policy' => 'Grąžinimo politika',
            'shipping_policy' => 'Pristatymo politika',
            'cookie_policy' => 'Slapukų politika',
            'gdpr_policy' => 'GDPR politika',
            'legal_notice' => 'Teisinė informacija',
            'imprint' => 'Imprint',
            'legal_document' => 'Teisinis dokumentas',
        ];
    }

    /**
     * Get the types that are required by default.
     *
     * @return array<int, string>
     */
    public static function getRequiredTypes(): array
    {
        return ['privacy_policy', 'terms_of_use'];
    }

    /**
     * Get a legal document by its unique key.
     */
    public static function getByKey(string $key): ?self
    {
        return self::byKey($key)->enabled()->published()->first();
    }

    /**
     * Get all required legal documents.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Legal>
     */
    public static function getRequiredDocuments(): \Illuminate\Database\Eloquent\Collection
    {
        return self::required()->enabled()->published()->ordered()->get();
    }

    /**
     * Get all legal documents of a specific type.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Legal>
     */
    public static function getByType(string $type): \Illuminate\Database\Eloquent\Collection
    {
        return self::byType($type)->enabled()->published()->ordered()->get();
    }
}
