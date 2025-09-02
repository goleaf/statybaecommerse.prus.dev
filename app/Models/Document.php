<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_template_id',
        'title',
        'content',
        'variables',
        'status',
        'format',
        'file_path',
        'documentable_type',
        'documentable_id',
        'created_by',
        'generated_at',
    ];

    protected $casts = [
        'variables' => 'array',
        'generated_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class, 'document_template_id');
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getVariablesUsed(): array
    {
        return $this->variables ?? [];
    }

    public function isPdf(): bool
    {
        return $this->format === 'pdf';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByFormat($query, string $format)
    {
        return $query->where('format', $format);
    }

    public function scopeForModel($query, Model $model)
    {
        return $query->where('documentable_type', get_class($model))
                    ->where('documentable_id', $model->id);
    }
}
