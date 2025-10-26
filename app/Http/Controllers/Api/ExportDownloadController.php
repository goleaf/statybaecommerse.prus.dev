<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\ExportStatus;
use App\Http\Controllers\Controller;
use App\Models\Export;
use App\Models\User;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ExportDownloadController extends Controller
{
    public function __invoke(Request $request, Export $export): StreamedResponse
    {
        // Ensure that only authorised principals can access the generated artifact.
        $user = $request->user();

        if ($user instanceof User) {
            // Authenticated users must satisfy the download policy before the file is exposed.
            abort_unless(Gate::forUser($user)->allows('download', $export), 403);
        } elseif ($export->requested_by !== null) {
            // Exports linked to a specific user should not be retrievable anonymously even with a signed URL.
            abort(401);
        }

        // Ensure the export has fully completed before attempting a download.
        abort_if($export->status !== ExportStatus::Completed, 404);

        // Resolve the storage disk and artifact path, falling back to sensible defaults.
        $diskName = $export->artifact_disk ?? (string) config('export.disk', config('filesystems.default', 'public'));
        $filesystem = Storage::disk($diskName);
        $path = $export->artifact_path;

        // Abort early if the artifact path is missing or no longer exists on the chosen disk.
        abort_if($path === null || $path === '' || ! $filesystem->exists($path), 404);

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
                // Derive the Content-Type header based on the stored format, normalising casing along the way.
                'Content-Type'        => $this->contentType($export->format),
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }

    private function contentType(string $format): string
    {
        // Normalise the incoming format to avoid mismatches caused by upper-case values stored in legacy records.
        $normalizedFormat = strtolower($format);

        return match ($normalizedFormat) {
            'csv'   => 'text/csv; charset=UTF-8',
            'xlsx'  => 'application/vnd.ms-excel',
            'pdf'   => 'application/pdf',
            default => 'application/octet-stream',
        };
    }
}
