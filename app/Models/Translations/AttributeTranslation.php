<?php declare(strict_types=1);

namespace App\Models\Translations;

use App\Models\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

final class AttributeTranslation extends Model
{
    use HasFactory;

    protected $table = 'attribute_translations';

    protected $fillable = [
        'attribute_id',
        'locale',
        'name',
        'slug',
        'description',
        'placeholder',
        'help_text',
    ];

    protected function casts(): array
    {
        return [
            'attribute_id' => 'integer',
        ];
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    // Scopes
    public function scopeByLocale($query, string $locale)
    {
        return $query->where('locale', $locale);
    }

    public function scopeByAttribute($query, int $attributeId)
    {
        return $query->where('attribute_id', $attributeId);
    }

    public function scopeWithName($query)
    {
        return $query->whereNotNull('name');
    }

    public function scopeWithDescription($query)
    {
        return $query->whereNotNull('description');
    }

    // Accessors
    public function getFormattedNameAttribute(): string
    {
        return $this->name ?: __('attributes.untitled_attribute');
    }

    public function getFormattedDescriptionAttribute(): ?string
    {
        return $this->description ?: null;
    }

    public function getFormattedPlaceholderAttribute(): ?string
    {
        return $this->placeholder ?: null;
    }

    public function getFormattedHelpTextAttribute(): ?string
    {
        return $this->help_text ?: null;
    }

    // Helper methods
    public function hasName(): bool
    {
        return !empty($this->name);
    }

    public function hasDescription(): bool
    {
        return !empty($this->description);
    }

    public function hasPlaceholder(): bool
    {
        return !empty($this->placeholder);
    }

    public function hasHelpText(): bool
    {
        return !empty($this->help_text);
    }

    public function isEmpty(): bool
    {
        return !$this->hasName() && !$this->hasDescription() && !$this->hasPlaceholder() && !$this->hasHelpText();
    }

    public function isComplete(): bool
    {
        return $this->hasName();
    }

    // Static helper methods
    public static function getByAttributeAndLocale(int $attributeId, string $locale): ?self
    {
        return static::where('attribute_id', $attributeId)
            ->where('locale', $locale)
            ->first();
    }

    public static function getOrCreateForAttributeAndLocale(int $attributeId, string $locale): self
    {
        return static::firstOrCreate(
            [
                'attribute_id' => $attributeId,
                'locale' => $locale,
            ],
            [
                'name' => '',
            ]
        );
    }

    public static function getTranslationsForAttribute(int $attributeId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('attribute_id', $attributeId)
            ->orderBy('locale')
            ->get();
    }

    public static function getAvailableLocalesForAttribute(int $attributeId): array
    {
        return static::where('attribute_id', $attributeId)
            ->pluck('locale')
            ->toArray();
    }

    public static function getMissingLocalesForAttribute(int $attributeId, array $supportedLocales): array
    {
        $existingLocales = static::getAvailableLocalesForAttribute($attributeId);
        return array_diff($supportedLocales, $existingLocales);
    }
}
