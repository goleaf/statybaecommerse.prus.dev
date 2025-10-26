<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\ExportStatus;
use App\Http\Controllers\Controller;
use App\Models\Export;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ExportDownloadController extends Controller
{
    public function __invoke(Export $export): StreamedResponse
    {
        // Ensure the export has fully completed before attempting a download.
        abort_if($export->status !== ExportStatus::Completed, 404);

        // Resolve the storage disk and artifact path, falling back to sensible defaults.
        $diskName = $export->artifact_disk ?? (string) config('export.disk', config('filesystems.default', 'public'));
        $filesystem = Storage::disk($diskName);
        $path = $export->artifact_path;

        // Abort early if the artifact path is missing or no longer exists on the chosen disk.
        abort_if(! $path || ! $filesystem->exists($path), 404);

        try {
            // Use a stream to avoid loading the whole export file into memory at once.
            $stream = $filesystem->readStream($path);
        } catch (FileNotFoundException) {
            abort(404);
        }

        // If the stream could not be opened we cannot continue with the download.
        abort_if($stream === false, 404);

        $filename = $export->artifact_filename ?? basename($path);

        return Response::streamDownload(
            static function () use ($stream): void {
                // Output the streamed content and ensure the resource handle is closed afterwards.
                if (is_resource($stream)) {
                    fpassthru($stream);
                    fclose($stream);
                }
            },
            $filename,
            [
                'Content-Type'        => $this->contentType($export->format),
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
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
