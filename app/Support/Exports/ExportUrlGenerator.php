<?php

declare(strict_types=1);

namespace App\Support\Exports;

use App\Models\Export;
use Illuminate\Support\Facades\URL;

final class ExportUrlGenerator
{
    public static function temporarySignedDownloadUrl(Export $export, ?int $minutes = null): string
    {
        return URL::temporarySignedRoute(
            'api.exports.download',
            now()->addMinutes($minutes ?? 60),
            ['export' => $export],
        );
    }
}
