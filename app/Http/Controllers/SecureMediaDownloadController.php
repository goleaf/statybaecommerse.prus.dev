<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\Storage\SecureStorage;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class SecureMediaDownloadController extends Controller
{
    public function __invoke(Request $request, string $encodedPath): StreamedResponse
    {
        $path = SecureStorage::decodePath($encodedPath);
        abort_if($path === null, 404);

        $disk = SecureStorage::disk();
        $filesystem = Storage::disk($disk);
        abort_if(! $filesystem->exists($path), 404);

        try {
            $mime = $filesystem->mimeType($path) ?: 'application/octet-stream';
            $filename = SecureStorage::filename($path);

            // Generate a safe fallback filename (preserves extension) to avoid header injection issues.
            $fallback = 'download' . (($extension = pathinfo($filename, PATHINFO_EXTENSION)) !== '' ? '.' . $extension : '');

            // Build a secure Content-Disposition header to prevent header injection or broken filenames.
            $disposition = HeaderUtils::makeDisposition(
                $request->boolean('download') ? HeaderUtils::DISPOSITION_ATTACHMENT : HeaderUtils::DISPOSITION_INLINE,
                $filename,
                $fallback,
            );

            $headers = [
                'Content-Type'            => $mime,
                'X-Content-Type-Options'  => 'nosniff',
                'Content-Security-Policy' => "default-src 'none'; img-src 'self'; media-src 'self'",
                'Content-Disposition'     => $disposition,
            ];

            if ($request->boolean('download')) {
                // When forcing download we still pass the sanitized Content-Disposition header for safety.
                return $filesystem->download($path, $filename, $headers);
            }

            // Serve the file inline with the sanitized disposition header.
            return $filesystem->response($path, null, $headers);
        } catch (FileNotFoundException) {
            abort(404);
        }
    }
}
