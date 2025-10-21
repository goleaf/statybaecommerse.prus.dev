<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface TranslatableRecord
{
    public function translations(): HasMany;
}
