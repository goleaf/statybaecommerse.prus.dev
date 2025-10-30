<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

final class EmailMarketingPage extends Page
{
    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations while documenting
     * the accepted value types for static analysis and future contributors.
     */
//    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-envelope-open';

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-envelope-open';
    }

    protected string $view = 'filament.pages.email-marketing-page';

    protected static ?int $navigationSort = 3;

    public function getTitle(): string
    {
        return 'Email Marketing Manager';
    }

    public function getHeading(): string
    {
        return 'Email Marketing Manager';
    }

    public function getSubheading(): ?string
    {
        return 'Manage your email campaigns and subscriber sync with Mailchimp';
    }
}
