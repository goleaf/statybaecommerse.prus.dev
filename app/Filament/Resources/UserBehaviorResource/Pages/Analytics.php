<?php declare(strict_types=1);

namespace App\Filament\Resources\UserBehaviorResource\Pages;

use App\Filament\Resources\UserBehaviorResource;
use Filament\Resources\Pages\Page;

final class Analytics extends Page
{
    protected static string $resource = UserBehaviorResource::class;

    protected string $view = 'filament.resources.user-behaviors.analytics';
}
