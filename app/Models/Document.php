<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\StatusScope;
use App\Observers\AttributionObserver;
use App\Support\Storage\SecureStorage;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Document
 *
 * Eloquent model representing the Document entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 * @property mixed $casts
 *
 * @phpstan-use HasFactory<\Database\Factories\DocumentFactory>
 *
 * @method static \Database\Factories\DocumentFactory            factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Document newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Document newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Document query()
 *
 * @mixin \Eloquent
 */
#[ObservedBy(AttributionObserver::class)]
#[ScopedBy([StatusScope::class])]
final class Document extends Model
{
    /** @use HasFactory<\Database\Factories\DocumentFactory> */
    use HasFactory;

    protected $fillable = [
        'document_template_id',
        'title',
        'name',
        'type',
        'version',
        'content',
        'variables',
        'status',
        'format',
        'file_path',
        'file_size',
        'mime_type',
        'is_public',
        'is_downloadable',
        'access_password',
        'documentable_type',
        'documentable_id',
        'created_by',
        'updated_by',
        'generated_at',
        'expires_at',
        'description',
        'notes',
    ];

    protected $casts = [
        'variables'       => 'array',
        'generated_at'    => 'datetime',
        'expires_at'      => 'datetime',
        'is_public'       => 'bool',
        'is_downloadable' => 'bool',
        'created_by'      => 'int',
        'updated_by'      => 'int',
    ];

    protected $with = ['creator', 'updater'];

    /**
     * Handle template functionality with proper error handling.
     *
     * @return BelongsTo<DocumentTemplate, Document>
     */
    public function template(): BelongsTo
    {
        /** @var BelongsTo<DocumentTemplate, Document> $relation */
        $relation = $this->belongsTo(DocumentTemplate::class, 'document_template_id');

        return $relation;
    }

    /**
     * Handle documentable functionality with proper error handling.
     *
     * @return MorphTo<Model, Document>
     */
    public function documentable(): MorphTo
    {
        /** @var MorphTo<Model, Document> $relation */
        $relation = $this->morphTo();

        return $relation;
    }

    /**
     * Handle creator functionality with proper error handling.
     *
     * @return BelongsTo<User, Document>
     */
    public function creator(): BelongsTo
    {
        /** @var BelongsTo<User, Document> $relation */
        $relation = $this->belongsTo(User::class, 'created_by');

        return $relation;
    }

    /**
     * Handle updater functionality with proper error handling.
     *
     * @return BelongsTo<User, Document>
     */
    public function updater(): BelongsTo
    {
        /** @var BelongsTo<User, Document> $relation */
        $relation = $this->belongsTo(User::class, 'updated_by');

        return $relation;
    }

    /**
     * Expose a chronological audit trail so the UI and API can surface changes.
     *
     * @return MorphMany<AuditLog, Document>
     */
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'entity')->latest('created_at');
    }

    /**
     * Handle getVariablesUsed functionality with proper error handling.
     *
     * @return array<string, mixed>
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

        return SecureStorage::temporarySignedUrl(
            $this->file_path,
            now()->addMinutes((int) config('media-security.url_lifetime', 30)),
            true
        );
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
     * @param  Builder<Document> $query
     * @return Builder<Document>
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Handle scopeByFormat functionality with proper error handling.
     *
     * @param  Builder<Document> $query
     * @return Builder<Document>
     */
    public function scopeByFormat(Builder $query, string $format): Builder
    {
        return $query->where('format', $format);
    }

    /**
     * Handle scopeOfStatus functionality with proper error handling.
     *
     * @param  Builder<Document> $query
     * @return Builder<Document>
     */
    public function scopeOfStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Handle scopeOfFormat functionality with proper error handling.
     *
     * @param  Builder<Document> $query
     * @return Builder<Document>
     */
    public function scopeOfFormat(Builder $query, string $format): Builder
    {
        return $query->where('format', $format);
    }

    /**
     * Handle scopeForModel functionality with proper error handling.
     *
     * @param  Builder<Document> $query
     * @return Builder<Document>
     */
    public function scopeForModel(Builder $query, Model $model): Builder
    {
        return $query
            ->where('documentable_type', $model::class)
            ->where('documentable_id', $model->getKey());
    }
}
