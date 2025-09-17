<?php

declare (strict_types=1);
namespace App\Models\Translations;

use App\Models\Legal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
/**
 * LegalTranslation
 * 
 * Eloquent model representing the LegalTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $table
 * @property mixed $fillable
 * @property mixed $casts
 * @property mixed $timestamps
 * @method static \Illuminate\Database\Eloquent\Builder|LegalTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LegalTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LegalTranslation query()
 * @mixin \Eloquent
 */
final class LegalTranslation extends Model
{
    use HasFactory;
    /**
     * Handle newFactory functionality with proper error handling.
     */
    protected static function newFactory()
    {
        return \Database\Factories\LegalTranslationFactory::new();
    }
    protected $table = 'legal_translations';
    protected $fillable = ['legal_id', 'locale', 'title', 'slug', 'content', 'seo_title', 'seo_description', 'meta_data'];
    protected $casts = ['legal_id' => 'integer', 'meta_data' => 'array'];
    public $timestamps = true;
    /**
     * Handle legal functionality with proper error handling.
     * @return BelongsTo
     */
    public function legal(): BelongsTo
    {
        return $this->belongsTo(Legal::class);
    }
    // Scopes
    /**
     * Handle scopeByLocale functionality with proper error handling.
     * @param Builder $query
     * @param string $locale
     * @return Builder
     */
    public function scopeByLocale(Builder $query, string $locale): Builder
    {
        return $query->where('locale', $locale);
    }
    /**
     * Handle scopeByLegal functionality with proper error handling.
     * @param Builder $query
     * @param int $legalId
     * @return Builder
     */
    public function scopeByLegal(Builder $query, int $legalId): Builder
    {
        return $query->where('legal_id', $legalId);
    }
    /**
     * Handle scopeWithTitle functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithTitle(Builder $query): Builder
    {
        return $query->whereNotNull('title')->where('title', '!=', '');
    }
    /**
     * Handle scopeWithContent functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithContent(Builder $query): Builder
    {
        return $query->whereNotNull('content')->where('content', '!=', '');
    }
    /**
     * Handle scopeWithSlug functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithSlug(Builder $query): Builder
    {
        return $query->whereNotNull('slug')->where('slug', '!=', '');
    }
    // Accessors
    /**
     * Handle getFormattedTitleAttribute functionality with proper error handling.
     * @return string
     */
    public function getFormattedTitleAttribute(): string
    {
        return $this->title ?: __('legal.untitled_document');
    }
    /**
     * Handle getFormattedContentAttribute functionality with proper error handling.
     * @return string|null
     */
    public function getFormattedContentAttribute(): ?string
    {
        return $this->content ?: null;
    }
    /**
     * Handle getFormattedSlugAttribute functionality with proper error handling.
     * @return string
     */
    public function getFormattedSlugAttribute(): string
    {
        return $this->slug ?: \Illuminate\Support\Str::slug($this->title);
    }
    /**
     * Handle getFormattedSeoTitleAttribute functionality with proper error handling.
     * @return string|null
     */
    public function getFormattedSeoTitleAttribute(): ?string
    {
        return $this->seo_title ?: $this->title;
    }
    /**
     * Handle getFormattedSeoDescriptionAttribute functionality with proper error handling.
     * @return string|null
     */
    public function getFormattedSeoDescriptionAttribute(): ?string
    {
        return $this->seo_description ?: \Illuminate\Support\Str::limit(strip_tags($this->content), 160);
    }
    /**
     * Handle getMetaDataArrayAttribute functionality with proper error handling.
     * @return array
     */
    public function getMetaDataArrayAttribute(): array
    {
        return $this->meta_data ?? [];
    }
    // Helper methods
    /**
     * Handle hasTitle functionality with proper error handling.
     * @return bool
     */
    public function hasTitle(): bool
    {
        return !empty($this->title);
    }
    /**
     * Handle hasContent functionality with proper error handling.
     * @return bool
     */
    public function hasContent(): bool
    {
        return !empty($this->content);
    }
    /**
     * Handle hasSlug functionality with proper error handling.
     * @return bool
     */
    public function hasSlug(): bool
    {
        return !empty($this->slug);
    }
    /**
     * Handle hasSeoTitle functionality with proper error handling.
     * @return bool
     */
    public function hasSeoTitle(): bool
    {
        return !empty($this->seo_title);
    }
    /**
     * Handle hasSeoDescription functionality with proper error handling.
     * @return bool
     */
    public function hasSeoDescription(): bool
    {
        return !empty($this->seo_description);
    }
    /**
     * Handle hasMetaData functionality with proper error handling.
     * @return bool
     */
    public function hasMetaData(): bool
    {
        return !empty($this->meta_data);
    }
    /**
     * Handle isEmpty functionality with proper error handling.
     * @return bool
     */
    public function isEmpty(): bool
    {
        return !$this->hasTitle() && !$this->hasContent();
    }
    /**
     * Handle isComplete functionality with proper error handling.
     * @return bool
     */
    public function isComplete(): bool
    {
        return $this->hasTitle() && $this->hasContent() && $this->hasSlug();
    }
    /**
     * Handle getWordCount functionality with proper error handling.
     * @return int
     */
    public function getWordCount(): int
    {
        return str_word_count(strip_tags($this->content ?: ''));
    }
    /**
     * Handle getCharacterCount functionality with proper error handling.
     * @return int
     */
    public function getCharacterCount(): int
    {
        return strlen(strip_tags($this->content ?: ''));
    }
    /**
     * Handle getReadingTime functionality with proper error handling.
     * @return int
     */
    public function getReadingTime(): int
    {
        $wordCount = $this->getWordCount();
        return max(1, ceil($wordCount / 200));
        // Assuming 200 words per minute
    }
}