<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\StatusScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Document
 *
 * Eloquent model representing the Document entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 * @property mixed $casts
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Document newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Document newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Document query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([StatusScope::class])]
final class Document extends Model
{
    use HasFactory;

    protected $fillable = ['document_template_id', 'title', 'content', 'variables', 'status', 'format', 'file_path', 'documentable_type', 'documentable_id', 'created_by', 'generated_at'];

    protected $casts = ['variables' => 'array', 'generated_at' => 'datetime'];

    /**
     * Handle template functionality with proper error handling.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class, 'document_template_id');
    }

    /**
     * Handle documentable functionality with proper error handling.
     */
    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Handle creator functionality with proper error handling.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Handle getVariablesUsed functionality with proper error handling.
     */
    public function getVariablesUsed(): array
    {
        return $this->variables ?? [];
    }

    /**
     * Handle isGenerated functionality with proper error handling.
     */
    public function isGenerated(): bool
    {
        return $this->status === 'generated' || $this->status === 'published';
    }

    /**
     * Handle getFileUrl functionality with proper error handling.
     */
    public function getFileUrl(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        return asset('storage/'.$this->file_path);
    }

    /**
     * Handle isPdf functionality with proper error handling.
     */
    public function isPdf(): bool
    {
        return $this->format === 'pdf';
    }

    /**
     * Handle isDraft functionality with proper error handling.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Handle isPublished functionality with proper error handling.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Handle isArchived functionality with proper error handling.
     */
    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    /**
     * Handle scopeByStatus functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Handle scopeByFormat functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByFormat($query, string $format)
    {
        return $query->where('format', $format);
    }

    /**
     * Handle scopeOfStatus functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeOfStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Handle scopeOfFormat functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeOfFormat($query, string $format)
    {
        return $query->where('format', $format);
    }

    /**
     * Handle scopeForModel functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeForModel($query, Model $model)
    {
        return $query->where('documentable_type', get_class($model))->where('documentable_id', $model->id);
    }
}
