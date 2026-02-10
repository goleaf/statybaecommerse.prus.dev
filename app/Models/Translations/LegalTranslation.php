<?php

declare(strict_types=1);

namespace App\Models\Translations;

use App\Models\Legal;
use App\Support\Html\HtmlSanitizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * LegalTranslation
 *
 * Eloquent model representing translated legal content.
 */
final class LegalTranslation extends Model
{
    use HasFactory;

    protected $table = 'legal_translations';

    protected $fillable = [
        'legal_id',
        'locale',
        'title',
        'slug',
        'content',
        'seo_title',
        'seo_description',
        'meta_data',
    ];

    protected $casts = [
        'legal_id'  => 'integer',
        'meta_data' => 'array',
    ];

    public $timestamps = true;

    protected static function newFactory()
    {
        return \Database\Factories\LegalTranslationFactory::new();
    }

    protected static function booted(): void
    {
        self::saving(static function (LegalTranslation $translation): void {
            /** @var HtmlSanitizer $sanitizer */
            $sanitizer = app(HtmlSanitizer::class);

            $content = $translation->content;
            if (! is_string($content) || trim($content) === '') {
                return;
            }

            $translation->content = $sanitizer->sanitize($content);
        });
    }

    public function legal(): BelongsTo
    {
        return $this->belongsTo(Legal::class);
    }

    public function scopeByLocale(Builder $query, string $locale): Builder
    {
        return $query->where('locale', $locale);
    }

    public function scopeByLegal(Builder $query, int $legalId): Builder
    {
        return $query->where('legal_id', $legalId);
    }

    public function scopeWithTitle(Builder $query): Builder
    {
        return $query->whereNotNull('title')->where('title', '!=', '');
    }

    public function scopeWithContent(Builder $query): Builder
    {
        return $query->whereNotNull('content')->where('content', '!=', '');
    }

    public function scopeWithSlug(Builder $query): Builder
    {
        return $query->whereNotNull('slug')->where('slug', '!=', '');
    }

    public function getFormattedTitleAttribute(): string
    {
        return $this->title ?: __('messages.legal');
    }

    public function getFormattedContentAttribute(): ?string
    {
        return $this->content ?: null;
    }

    public function getFormattedSlugAttribute(): string
    {
        return $this->slug ?: \Illuminate\Support\Str::slug((string) $this->title);
    }

    public function getFormattedSeoTitleAttribute(): ?string
    {
        return $this->seo_title ?: $this->title;
    }

    public function getFormattedSeoDescriptionAttribute(): ?string
    {
        return $this->seo_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $this->content), 160);
    }

    public function getMetaDataArrayAttribute(): array
    {
        return $this->meta_data ?? [];
    }

    public function hasTitle(): bool
    {
        return ! empty($this->title);
    }

    public function hasContent(): bool
    {
        return ! empty($this->content);
    }

    public function hasSlug(): bool
    {
        return ! empty($this->slug);
    }

    public function hasSeoTitle(): bool
    {
        return ! empty($this->seo_title);
    }

    public function hasSeoDescription(): bool
    {
        return ! empty($this->seo_description);
    }

    public function hasMetaData(): bool
    {
        return ! empty($this->meta_data);
    }

    public function isEmpty(): bool
    {
        return ! $this->hasTitle() && ! $this->hasContent();
    }

    public function isComplete(): bool
    {
        return $this->hasTitle() && $this->hasContent() && $this->hasSlug();
    }

    public function getWordCount(): int
    {
        return str_word_count(strip_tags((string) ($this->content ?: '')));
    }

    public function getCharacterCount(): int
    {
        return strlen(strip_tags((string) ($this->content ?: '')));
    }

    public function getReadingTime(): int
    {
        $wordCount = $this->getWordCount();

        return max(1, (int) ceil($wordCount / 200));
    }
}
