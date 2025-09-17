<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\CodeStyleService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class CodeStyleMiddleware
{
    public function __construct(
        private readonly CodeStyleService $codeStyleService
    ) {}

    public function handle(Request $request, Closure $next)
    {
        // Only apply in development environment
        if (! app()->environment('local', 'testing')) {
            return $next($request);
        }

        // Check if this is a file upload or modification request
        if ($this->isFileModificationRequest($request)) {
            $this->validateUploadedFiles($request);
        }

        return $next($request);
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

    private function validateSingleFile($file): void
    {
        if (! $file->isValid() || ! str_ends_with($file->getClientOriginalName(), '.php')) {
            return;
        }

        $content = file_get_contents($file->getPathname());
        $violations = $this->codeStyleService->validateFile($file->getClientOriginalName());

        if (! empty($violations)) {
            Log::warning('Code style violations detected in uploaded file', [
                'file' => $file->getClientOriginalName(),
                'violations' => $violations,
            ]);
        }
    }
}
