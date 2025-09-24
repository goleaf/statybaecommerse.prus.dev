<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserWishlistResource\Pages;

use App\Filament\Resources\UserWishlistResource;
use Filament\Resources\Pages\ViewRecord;

final class ViewUserWishlist extends ViewRecord
{
    protected static string $resource = UserWishlistResource::class;
}
