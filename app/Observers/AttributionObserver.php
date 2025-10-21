<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\AuditLog;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Stringable;

final class AttributionObserver
{
    public function creating(Model $model): void
    {
        $userId = Auth::id();

        if ($userId === null) {
            return;
        }

        if ($this->supportsAttribute($model, 'created_by') && ! $model->isDirty('created_by') && $model->getAttribute('created_by') === null) {
            $model->setAttribute('created_by', $userId);
        }

        if ($this->supportsAttribute($model, 'updated_by') && ! $model->isDirty('updated_by') && $model->getAttribute('updated_by') === null) {
            $model->setAttribute('updated_by', $userId);
        }
    }

    public function updating(Model $model): void
    {
        $userId = Auth::id();

        if ($userId === null) {
            return;
        }

        if ($this->supportsAttribute($model, 'updated_by') && ! $model->isDirty('updated_by')) {
            $model->setAttribute('updated_by', $userId);
        }
    }

    public function saving(Model $model): void
    {
        $userId = Auth::id();

        if ($userId === null) {
            return;
        }

        if (! $model->exists && $this->supportsAttribute($model, 'updated_by') && $model->getAttribute('updated_by') === null) {
            $model->setAttribute('updated_by', $userId);
        }
    }

    public function created(Model $model): void
    {
        $this->storeAuditLog($model, 'created', [], $this->normalizeAttributes($model->getAttributes()));
    }

    public function updated(Model $model): void
    {
        $changedKeys = array_keys($model->getChanges());

        if ($changedKeys === []) {
            return;
        }

        $before = Arr::only($model->getOriginal(), $changedKeys);
        $after = Arr::only($model->getAttributes(), $changedKeys);

        $this->storeAuditLog(
            $model,
            'updated',
            $this->normalizeAttributes($before),
            $this->normalizeAttributes($after)
        );
    }

    public function deleted(Model $model): void
    {
        $original = $this->normalizeAttributes($model->getOriginal());
        $this->storeAuditLog($model, 'deleted', $original, []);
    }

    public function restored(Model $model): void
    {
        $this->storeAuditLog(
            $model,
            'restored',
            [],
            $this->normalizeAttributes($model->getAttributes())
        );
    }

    private function supportsAttribute(Model $model, string $attribute): bool
    {
        return in_array($attribute, $model->getFillable(), true);
    }

    /**
     * Persist a new audit record describing the model transition.
     *
     * @param array<string, mixed> $before
     * @param array<string, mixed> $after
     */
    private function storeAuditLog(Model $model, string $action, array $before, array $after): void
    {
        if (! method_exists($model, 'auditLogs')) {
            return; // Skip models that are not explicitly wired for auditing.
        }

        $entityKey = $model->getKey() ?? $model->getOriginal($model->getKeyName());

        if ($entityKey === null) {
            return;
        }

        if ($entityKey instanceof Stringable) {
            $entityId = $entityKey->__toString();
        } elseif (is_scalar($entityKey)) {
            $entityId = (string) $entityKey;
        } else {
            return;
        }

        AuditLog::query()->create([
            'entity_type' => $model->getMorphClass(),
            'entity_id'   => $entityId,
            'action'      => $action,
            'user_id'     => $this->resolveUserId($model),
            'diff'        => [
                'before' => $before,
                'after'  => $after,
            ],
        ]);
    }

    private function resolveUserId(Model $model): ?int
    {
        $authId = Auth::id();

        if ($authId !== null) {
            return (int) $authId;
        }

        foreach (['updated_by', 'created_by', 'user_id'] as $column) {
            if (! $this->supportsAttribute($model, $column)) {
                continue;
            }

            $value = $model->getAttribute($column);

            if ($value === null || $value === '') {
                continue;
            }

            if (is_int($value)) {
                return $value;
            }

            if (is_numeric($value)) {
                return (int) $value;
            }
        }

        return null;
    }

    /**
     * @param  array<string|int, mixed> $attributes
     * @return array<string, mixed>
     */
    private function normalizeAttributes(array $attributes): array
    {
        return collect($attributes)
            ->except(['created_at', 'updated_at', 'deleted_at'])
            ->mapWithKeys(fn ($value, $key): array => [
                (string) $key => $this->normalizeValue($value),
            ])
            ->all();
    }

    private function normalizeValue(mixed $value): mixed
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        if ($value instanceof Stringable) {
            return $value->__toString();
        }

        if (is_array($value)) {
            return array_map(fn ($item) => $this->normalizeValue($item), $value);
        }

        return $value;
    }
}
