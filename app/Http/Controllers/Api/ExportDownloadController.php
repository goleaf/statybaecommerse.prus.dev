<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\ExportStatus;
use App\Http\Controllers\Controller;
use App\Models\Export;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

final class ExportDownloadController extends Controller
{
    public function __invoke(Export $export): HttpResponse
    {
        abort_if($export->status !== ExportStatus::Completed, 404);

        $disk = $export->artifact_disk ?? (string) config('export.disk', config('filesystems.default', 'public'));
        $path = $export->artifact_path;

        abort_if(! $path || ! Storage::disk($disk)->exists($path), 404);

        try {
            $content = Storage::disk($disk)->get($path);
        } catch (FileNotFoundException) {
            abort(404);
        }

        $filename = $export->artifact_filename ?? basename((string) $path);

        return Response::make($content, 200, [
            'Content-Type'        => $this->contentType($export->format),
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function contentType(string $format): string
    {
        return match ($format) {
            'csv'   => 'text/csv; charset=UTF-8',
            'xlsx'  => 'application/vnd.ms-excel',
            'pdf'   => 'application/pdf',
            default => 'application/octet-stream',
        };
    }
}
