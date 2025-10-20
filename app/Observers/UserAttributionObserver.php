<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

final class UserAttributionObserver
{
    private static ?int $cachedSystemUserId = null;

    private static bool $systemUserResolved = false;

    /** @var array<int, string|null> */
    private static array $userNameCache = [];

    public function creating(Model $model): void
    {
        $userId = $this->resolveUserId();

        if ($userId === null) {
            return;
        }

        if ($this->shouldSetAttribute($model, 'created_by') && $model->getAttribute('created_by') === null) {
            $model->setAttribute('created_by', $userId);
        }

        if ($this->shouldSetAttribute($model, 'updated_by') && $model->getAttribute('updated_by') === null) {
            $model->setAttribute('updated_by', $userId);
        }

        $userName = $this->resolveUserName($userId);

        if ($userName === null) {
            return;
        }

        if ($this->shouldSetAttribute($model, 'created_by_name') && $model->getAttribute('created_by_name') === null) {
            $model->setAttribute('created_by_name', $userName);
        }

        if ($this->shouldSetAttribute($model, 'updated_by_name') && $model->getAttribute('updated_by_name') === null) {
            $model->setAttribute('updated_by_name', $userName);
        }
    }

    public function updating(Model $model): void
    {
        $userId = $this->resolveUserId();

        if ($userId === null) {
            return;
        }

        if ($this->shouldSetAttribute($model, 'updated_by')) {
            $model->setAttribute('updated_by', $userId);
        }

        $userName = $this->resolveUserName($userId);

        if ($userName !== null && $this->shouldSetAttribute($model, 'updated_by_name')) {
            $model->setAttribute('updated_by_name', $userName);
        }
    }

    private function resolveUserId(): ?int
    {
        $authenticatedId = Auth::id();

        if ($authenticatedId !== null) {
            return (int) $authenticatedId;
        }

        if (self::$systemUserResolved) {
            return self::$cachedSystemUserId;
        }

        $configuredId = config('attribution.system_user_id');

        if ($configuredId !== null) {
            self::$cachedSystemUserId = (int) $configuredId;
            self::$systemUserResolved = true;

            return self::$cachedSystemUserId;
        }

        $systemUserEmail = config('attribution.system_user_email');

        if (! is_string($systemUserEmail) || $systemUserEmail === '') {
            self::$systemUserResolved = true;

            return null;
        }

        self::$cachedSystemUserId = User::query()
            ->where('email', $systemUserEmail)
            ->value('id');
        self::$systemUserResolved = true;

        return self::$cachedSystemUserId;
    }

    private function resolveUserName(?int $userId): ?string
    {
        if ($userId === null) {
            return null;
        }

        $authenticatedUser = Auth::user();

        if ($authenticatedUser !== null && (int) $authenticatedUser->getAuthIdentifier() === $userId) {
            $name = $authenticatedUser->getAttribute('name');

            return is_string($name) && $name !== '' ? $name : null;
        }

        if (! array_key_exists($userId, self::$userNameCache)) {
            self::$userNameCache[$userId] = User::query()->whereKey($userId)->value('name');
        }

        $cached = self::$userNameCache[$userId];

        return is_string($cached) && $cached !== '' ? $cached : null;
    }

    private function shouldSetAttribute(Model $model, string $attribute): bool
    {
        static $columnCache = [];

        if ($model->isFillable($attribute)) {
            return true;
        }

        $table = $model->getTable();
        $connectionName = $model->getConnectionName() ?? config('database.default');
        $cacheKey = $connectionName.'::'.$table;

        if (! array_key_exists($cacheKey, $columnCache)) {
            $columnCache[$cacheKey] = Schema::connection($connectionName)->getColumnListing($table);
        }

        return in_array($attribute, $columnCache[$cacheKey], true);
    }
}
