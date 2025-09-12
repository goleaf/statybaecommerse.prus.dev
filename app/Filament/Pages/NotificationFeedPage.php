<?php declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Components\LiveNotificationFeed;
use Filament\Pages\Page;

final class NotificationFeedPage extends Page
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-bell';
    protected static string $view = 'filament.pages.notification-feed-page';
    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string
    {
        return __('admin.notifications.title');
    }

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }

    protected function getViewData(): array
    {
        return [
            'notificationFeed' => new LiveNotificationFeed(),
        ];
    }
}
