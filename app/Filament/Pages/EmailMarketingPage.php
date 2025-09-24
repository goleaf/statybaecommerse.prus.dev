<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;

final class EmailMarketingPage extends Page
{
    // /** @var BackedEnum|string|null */
    // protected static BackedEnum|string|null $navigationIcon =  'heroicon-o-envelope-open';

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
