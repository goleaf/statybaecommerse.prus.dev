<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Export;
use App\Services\Export\ExportStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ExportDownloadController extends Controller
{
    public function __invoke(Request $request, Export $export): BinaryFileResponse
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Invalid or expired export URL.');
        }

        if ($export->status !== ExportStatus::Completed || ! $export->isDownloadable()) {
            abort(404);
        }

        $disk = Storage::disk(config('export.disk'));
        if (! $disk->exists($export->path)) {
            abort(404);
        }

        $filename = sprintf('%s.%s', $export->name, $export->format->extension());

        return response()->download($disk->path($export->path), $filename);
    }
}
