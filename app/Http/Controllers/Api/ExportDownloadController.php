<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Export;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

final class ExportDownloadController extends Controller
{
    public function __invoke(Request $request, Export $export)
    {
        abort_unless($request->hasValidSignature(), 403);

        if ($export->expires_at && now()->greaterThan($export->expires_at)) {
            abort(410, 'Export link has expired.');
        }

        $this->authorize('download', $export);

        $disk = Storage::disk('local');

        if (! $disk->exists((string) $export->file_path)) {
            abort(404, 'Export file not found.');
        }

        return $disk->download((string) $export->file_path, $export->file_name ?? 'export.'.$export->format->extension(), [
            'Content-Type' => $export->mime_type ?? $export->format->mimeType(),
        ]);
    }
}
