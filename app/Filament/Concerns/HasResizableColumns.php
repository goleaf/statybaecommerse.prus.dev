<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use Asmit\ResizedColumn\HasResizableColumn as BaseHasResizableColumn;
use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;

trait HasResizableColumns
{
    use BaseHasResizableColumn {
        getUserId as private getBaseUserId;
    }

    protected function getUserId(): mixed
    {
        $guard = $this->getFilamentGuard();

        if ($guard instanceof Guard) {
            $identifier = method_exists($guard, 'id') ? $guard->id() : null;

            if ($identifier !== null) {
                return $identifier;
            }

            $user = $guard->user();

            if ($user instanceof Authenticatable) {
                return $user->getAuthIdentifier();
            }
        }

        return $this->getBaseUserId();
    }

    private function getFilamentGuard(): ?Guard
    {
        $panel = Filament::getCurrentPanel();

        if ($panel === null) {
            return null;
        }

        return Filament::auth();
    }
}
