<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class DocumentTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'content',
        'variables',
        'type',
        'category',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (DocumentTemplate $template): void {
            if (empty($template->slug)) {
                $template->slug = Str::slug($template->name);
            }
        });

        static::updating(function (DocumentTemplate $template): void {
            if ($template->isDirty('name') && empty($template->slug)) {
                $template->slug = Str::slug($template->name);
            }
        });
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function getAvailableVariables(): array
    {
        return $this->variables ?? [];
    }

    public function hasVariable(string $variable): bool
    {
        return in_array($variable, $this->getAvailableVariables());
    }

    public function getSettings(): array
    {
        return $this->settings ?? [];
    }

    public function getSetting(string $key, $default = null)
    {
        return $this->getSettings()[$key] ?? $default;
    }

    public function render(array $variables = []): string
    {
        $content = $this->content;

        foreach ($variables as $key => $value) {
            $content = str_replace('{{' . $key . '}}', (string) $value, $content);
        }

        return $content;
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOfCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function getPrintSettings(): array
    {
        return $this->settings ?? [
            'header' => null,
            'footer' => null,
            'css' => null,
            'page_size' => 'A4',
            'orientation' => 'portrait',
            'margins' => ['top' => 20, 'right' => 20, 'bottom' => 20, 'left' => 20],
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
