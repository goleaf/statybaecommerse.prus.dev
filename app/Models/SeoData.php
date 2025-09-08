<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class SeoData extends Model
{
    use HasFactory;

    protected $fillable = [
        'seoable_type',
        'seoable_id',
        'locale',
        'title',
        'description',
        'keywords',
        'canonical_url',
        'meta_tags',
        'structured_data',
        'no_index',
        'no_follow',
    ];

    protected $casts = [
        'meta_tags' => 'array',
        'structured_data' => 'array',
        'no_index' => 'boolean',
        'no_follow' => 'boolean',
    ];

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForLocale($query, string $locale)
    {
        return $query->where('locale', $locale);
    }

    public function getMetaTagsHtmlAttribute(): string
    {
        $html = '';
        
        if ($this->title) {
            $html .= '<title>' . e($this->title) . '</title>' . PHP_EOL;
            $html .= '<meta property="og:title" content="' . e($this->title) . '">' . PHP_EOL;
        }
        
        if ($this->description) {
            $html .= '<meta name="description" content="' . e($this->description) . '">' . PHP_EOL;
            $html .= '<meta property="og:description" content="' . e($this->description) . '">' . PHP_EOL;
        }
        
        if ($this->keywords) {
            $html .= '<meta name="keywords" content="' . e($this->keywords) . '">' . PHP_EOL;
        }
        
        if ($this->canonical_url) {
            $html .= '<link rel="canonical" href="' . e($this->canonical_url) . '">' . PHP_EOL;
        }
        
        if ($this->no_index || $this->no_follow) {
            $robots = [];
            if ($this->no_index) $robots[] = 'noindex';
            if ($this->no_follow) $robots[] = 'nofollow';
            $html .= '<meta name="robots" content="' . implode(', ', $robots) . '">' . PHP_EOL;
        }
        
        if ($this->meta_tags) {
            foreach ($this->meta_tags as $name => $content) {
                $html .= '<meta name="' . e($name) . '" content="' . e($content) . '">' . PHP_EOL;
            }
        }
        
        return $html;
    }

    public function getStructuredDataJsonAttribute(): ?string
    {
        if (!$this->structured_data) {
            return null;
        }
        
        return json_encode($this->structured_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
