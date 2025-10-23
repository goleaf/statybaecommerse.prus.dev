<?php

declare(strict_types=1);

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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

    private function supportsAttribute(Model $model, string $attribute): bool
    {
        return in_array($attribute, $model->getFillable(), true);
    }
}
