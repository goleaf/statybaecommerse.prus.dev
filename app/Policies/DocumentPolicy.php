<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function viewAny(User|AdminUser $user): bool
    {
        return true;
    }

    public function view(User|AdminUser $user, Document $document): bool
    {
        return true;
    }

    public function create(User|AdminUser $user): bool
    {
        return true;
    }

    public function update(User|AdminUser $user, Document $document): bool
    {
        return true;
    }

    public function delete(User|AdminUser $user, Document $document): bool
    {
        return $user instanceof AdminUser;
    }
}
