<?php declare(strict_types=1);

namespace App\Filament\Resources\AnalyticsResource\Pages;

use App\Filament\Resources\AnalyticsResource;
use Filament\Resources\Pages\ListRecords;

final class AnalyticsDashboard extends ListRecords
{
    protected static string $resource = AnalyticsResource::class;
}

