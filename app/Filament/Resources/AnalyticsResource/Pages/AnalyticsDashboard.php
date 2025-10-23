<?php

declare(strict_types=1);

namespace App\Filament\Resources\AnalyticsResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\AnalyticsResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

final class AnalyticsDashboard extends BaseListRecords
{
    protected static string $resource = AnalyticsResource::class;
}
