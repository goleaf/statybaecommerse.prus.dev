<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\CodeStyleService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class CodeStyleMiddleware
{
    public function __construct(
        private readonly CodeStyleService $codeStyleService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Only apply in development environment
        if (! app()->environment('local', 'testing')) {
            /** @var Response $response */
            $response = $next($request);

            return $response;
        }

        // Check if this is a file upload or modification request
        if ($this->isFileModificationRequest($request)) {
            $this->validateUploadedFiles($request);
        }

        /** @var Response $response */
        $response = $next($request);

        return $response;
    }

    private function isFileModificationRequest(Request $request): bool
    {
        $fileModificationRoutes = [
            'admin.filament.resources',
            'admin.upload',
            'admin.import',
        ];

        foreach ($fileModificationRoutes as $route) {
            if ($request->routeIs($route)) {
                return true;
            }
        }

        return false;
    }

    private function validateUploadedFiles(Request $request): void
    {
        $uploadedFiles = $request->allFiles();

        foreach ($uploadedFiles as $file) {
            if (is_array($file)) {
                foreach ($file as $singleFile) {
                    $this->validateSingleFile($singleFile);
                }
            } else {
                $this->validateSingleFile($file);
            }
        }
    }

    private function validateSingleFile(UploadedFile $file): void
    {
        if (! $file->isValid() || ! str_ends_with($file->getClientOriginalName(), '.php')) {
            return;
        }

        // Analyse the temporary upload path so the validation service can read the file contents.
        $violations = $this->codeStyleService->validateFile($file->getPathname());

        if (! empty($violations)) {
            Log::warning('Code style violations detected in uploaded file', [
                'file' => $file->getClientOriginalName(),
                'violations' => $violations,
            ]);
        }
    }
}
