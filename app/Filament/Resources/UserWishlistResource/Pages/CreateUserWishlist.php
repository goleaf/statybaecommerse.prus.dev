<?php declare(strict_types=1);

namespace App\Filament\Resources\UserWishlistResource\Pages;

use App\Filament\Resources\UserWishlistResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateUserWishlist extends CreateRecord
{
    protected static string $resource = UserWishlistResource::class;
}

