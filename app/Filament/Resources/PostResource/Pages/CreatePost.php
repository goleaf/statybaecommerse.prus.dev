<?php

declare(strict_types=1);

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use Filament\Resources\Pages\CreateRecord;

final /**
 * CreatePost
 * 
 * Filament resource for admin panel management.
 */
class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;
}
