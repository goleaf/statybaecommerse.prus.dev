<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\Storage\SecureStorage;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class SecureMediaDownloadController extends Controller
{
    public function __invoke(Request $request, string $encodedPath): Response|StreamedResponse
    {
        $path = SecureStorage::decodePath($encodedPath);
        abort_if($path === null, 404);

        $disk = SecureStorage::disk();
        $filesystem = Storage::disk($disk);
        abort_if(! $filesystem->exists($path), 404);

        try {
            $mime = $filesystem->mimeType($path) ?: 'application/octet-stream';
            $filename = SecureStorage::filename($path);
            $headers = [
                'Content-Type'            => $mime,
                'X-Content-Type-Options'  => 'nosniff',
                'Content-Security-Policy' => "default-src 'none'; img-src 'self'; media-src 'self'",
            ];

            if ($request->boolean('download')) {
                return $filesystem->download($path, $filename, $headers);
            }

            return $filesystem->response($path, null, array_merge($headers, [
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]));
        } catch (FileNotFoundException) {
            abort(404);
        }
    }
}
