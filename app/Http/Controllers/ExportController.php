<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ExportStatus;
use App\Models\Export;
use App\Services\Export\ExportService;
use App\Support\Exports\ExportUrlGenerator;
use App\Support\Storage\SecureStorage;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * ExportController
 *
 * HTTP controller handling ExportController related web requests, responses, and business logic with proper validation and error handling.
 */
class ExportController extends Controller
{
    public function __construct(private readonly ExportService $service) {}

    /**
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(): ViewContract
    {
        $exports = Export::query()
            ->whereNotNull('artifact_path')
            ->latest('requested_at')
            ->get();

        $files = $exports->map(function (Export $export): array {
            $disk = $export->artifact_disk ?? config('media-security.disk', 'secure-media');
            $path = $export->artifact_path;

            $size = null;
            if ($path && Storage::disk($disk)->exists($path)) {
                $size = Storage::disk($disk)->size($path);
            }

            return [
                'name' => $export->artifact_filename ?? basename((string) $path),
                'path' => $path,
                'size' => $size,
                'url'  => ExportUrlGenerator::temporarySignedDownloadUrl($export, 60),
            ];
        })->filter(fn (array $file): bool => $file['path'] !== null)->values()->all();

        return view('exports.index', ['files' => $files]);
    }

    /**
     * Handle download functionality with proper error handling.
     */
    public function download(string $filename): HttpResponse|StreamedResponse|RedirectResponse
    {
        $export = Export::query()
            ->where('artifact_filename', $filename)
            ->orWhere('uuid', pathinfo($filename, PATHINFO_FILENAME))
            ->first();

        if ($export instanceof Export && $export->status === ExportStatus::Completed) {
            return redirect()->away(ExportUrlGenerator::temporarySignedDownloadUrl($export));
        }

        $path = 'exports/' . $filename;
        $disk = Storage::disk(SecureStorage::disk());
        if (! $disk->exists($path)) {
            return redirect()->route('exports.index')->with('error', __('File not found.'));
        }

        try {
            return Response::streamDownload(function () use ($disk, $path): void {
                echo $disk->get($path);
            }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
        } catch (FileNotFoundException) {
            return redirect()->route('exports.index')->with('error', __('File not found.'));
        }
    }
}
