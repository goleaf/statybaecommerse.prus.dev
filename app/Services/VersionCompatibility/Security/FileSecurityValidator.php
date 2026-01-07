<?php

declare(strict_types=1);

namespace App\Services\VersionCompatibility\Security;

use App\Services\VersionCompatibility\Exceptions\InvalidFileException;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * Comprehensive file security validator for version compatibility service
 *
 * Implements defense-in-depth security validation including:
 * - Path traversal prevention with realpath validation
 * - File extension whitelist validation
 * - File size limits with configurable thresholds
 * - MIME type validation for additional security
 * - Symlink attack prevention
 * - Security logging for audit trails
 */
final class FileSecurityValidator
{
    private readonly array $allowedExtensions;

    private readonly int $maxFileSize;

    private readonly bool $enablePathTraversalCheck;

    private readonly bool $enableMimeTypeCheck;

    private readonly array $allowedMimeTypes;

    private readonly string $basePath;

    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly ConfigRepository $config
    ) {
        $securityConfig = $this->config->get('version-compatibility.security', []);

        $this->allowedExtensions = $securityConfig['allowed_extensions'] ?? ['php'];
        $this->maxFileSize = $securityConfig['max_file_size'] ?? 1024 * 1024; // 1MB
        $this->enablePathTraversalCheck = ! ($securityConfig['disable_path_traversal_check'] ?? false);
        $this->enableMimeTypeCheck = $securityConfig['enable_mime_type_check'] ?? true;
        $this->allowedMimeTypes = $securityConfig['allowed_mime_types'] ?? [
            'text/x-php',
            'application/x-php',
            'text/plain',
        ];

        // Use Laravel's base_path() for secure path resolution
        $this->basePath = base_path();
    }

    /**
     * Validate file path with comprehensive security checks
     *
     * @throws InvalidFileException When file validation fails
     */
    public function validateFilePath(string $filePath): void
    {
        $this->validatePathTraversal($filePath);
        $this->validateFileExtension($filePath);
        $this->validateFileExists($filePath);
        $this->validateFileSize($filePath);
        $this->validateSymlinks($filePath);

        if ($this->enableMimeTypeCheck) {
            $this->validateMimeType($filePath);
        }

        // Log successful validation for audit trail
        Log::channel('security')->info('File validation successful', [
            'file_path' => $filePath,
            'file_size' => $this->filesystem->size($filePath),
            'mime_type' => $this->getMimeType($filePath),
        ]);
    }

    /**
     * Validate content security with size and pattern checks
     *
     * @throws InvalidArgumentException When content validation fails
     */
    public function validateContent(string $content): void
    {
        $contentLength = strlen($content);

        if ($contentLength === 0) {
            throw new InvalidArgumentException('Content cannot be empty');
        }

        if ($contentLength > $this->maxFileSize) {
            Log::channel('security')->warning('Content size limit exceeded', [
                'content_size' => $contentLength,
                'max_size'     => $this->maxFileSize,
            ]);

            throw new InvalidArgumentException(
                "Content size ({$contentLength} bytes) exceeds maximum: {$this->maxFileSize}"
            );
        }

        // Check for suspicious patterns
        $this->validateContentPatterns($content);
    }

    /**
     * Validate directory path for batch operations
     *
     * @throws InvalidArgumentException When directory validation fails
     */
    public function validateDirectoryPath(string $directoryPath): void
    {
        if ($this->enablePathTraversalCheck) {
            $this->validatePathTraversal($directoryPath);
        }

        if (! $this->filesystem->exists($directoryPath)) {
            throw new InvalidArgumentException("Directory does not exist: {$directoryPath}");
        }

        if (! is_dir($directoryPath)) {
            throw new InvalidArgumentException("Path is not a directory: {$directoryPath}");
        }

        // Ensure directory is within allowed base path
        $realDirectoryPath = realpath($directoryPath);
        if ($realDirectoryPath === false || ! str_starts_with($realDirectoryPath, $this->basePath)) {
            Log::channel('security')->error('Directory path outside base path', [
                'directory_path' => $directoryPath,
                'real_path'      => $realDirectoryPath,
                'base_path'      => $this->basePath,
            ]);

            throw new InvalidFileException('Directory path outside allowed base path');
        }
    }

    /**
     * Prevent path traversal attacks with comprehensive checks
     */
    private function validatePathTraversal(string $path): void
    {
        if (! $this->enablePathTraversalCheck) {
            return;
        }

        // Check for obvious path traversal patterns
        $dangerousPatterns = ['..', '~', '\0', '%00'];
        foreach ($dangerousPatterns as $pattern) {
            if (str_contains($path, $pattern)) {
                Log::channel('security')->error('Path traversal attempt detected', [
                    'path'       => $path,
                    'pattern'    => $pattern,
                    'ip'         => request()?->ip(),
                    'user_agent' => request()?->userAgent(),
                ]);

                throw new InvalidFileException('Invalid file path: path traversal detected');
            }
        }

        // Use realpath for additional validation
        $realPath = realpath($path);
        if ($realPath !== false && ! str_starts_with($realPath, $this->basePath)) {
            Log::channel('security')->error('File path outside base directory', [
                'path'      => $path,
                'real_path' => $realPath,
                'base_path' => $this->basePath,
            ]);

            throw new InvalidFileException('File path outside allowed base directory');
        }
    }

    /**
     * Validate file extension against whitelist
     */
    private function validateFileExtension(string $filePath): void
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if (! in_array($extension, $this->allowedExtensions, true)) {
            Log::channel('security')->warning('Invalid file extension', [
                'file_path'          => $filePath,
                'extension'          => $extension,
                'allowed_extensions' => $this->allowedExtensions,
            ]);

            throw new InvalidFileException(
                "File extension '{$extension}' not allowed. Allowed: " .
                implode(', ', $this->allowedExtensions)
            );
        }
    }

    /**
     * Validate file exists and is readable
     */
    private function validateFileExists(string $filePath): void
    {
        if (! $this->filesystem->exists($filePath)) {
            throw new InvalidFileException("File does not exist: {$filePath}");
        }

        if (! is_readable($filePath)) {
            Log::channel('security')->error('File not readable', [
                'file_path' => $filePath,
            ]);

            throw new InvalidFileException("File is not readable: {$filePath}");
        }
    }

    /**
     * Validate file size constraints
     */
    private function validateFileSize(string $filePath): void
    {
        $fileSize = $this->filesystem->size($filePath);

        if ($fileSize > $this->maxFileSize) {
            Log::channel('security')->warning('File size limit exceeded', [
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'max_size'  => $this->maxFileSize,
            ]);

            throw new InvalidFileException(
                "File size ({$fileSize} bytes) exceeds maximum: {$this->maxFileSize}"
            );
        }
    }

    /**
     * Prevent symlink attacks
     */
    private function validateSymlinks(string $filePath): void
    {
        if (is_link($filePath)) {
            $linkTarget = readlink($filePath);

            Log::channel('security')->warning('Symlink detected', [
                'file_path'   => $filePath,
                'link_target' => $linkTarget,
            ]);

            // Validate that symlink target is also within base path
            $realTarget = realpath($linkTarget);
            if ($realTarget === false || ! str_starts_with($realTarget, $this->basePath)) {
                throw new InvalidFileException('Symlink target outside allowed base directory');
            }
        }
    }

    /**
     * Validate MIME type for additional security
     */
    private function validateMimeType(string $filePath): void
    {
        $mimeType = $this->getMimeType($filePath);

        if (! in_array($mimeType, $this->allowedMimeTypes, true)) {
            Log::channel('security')->warning('Invalid MIME type', [
                'file_path'          => $filePath,
                'mime_type'          => $mimeType,
                'allowed_mime_types' => $this->allowedMimeTypes,
            ]);

            throw new InvalidFileException(
                "MIME type '{$mimeType}' not allowed. Allowed: " .
                implode(', ', $this->allowedMimeTypes)
            );
        }
    }

    /**
     * Get file MIME type safely
     */
    private function getMimeType(string $filePath): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        return $mimeType ?: 'application/octet-stream';
    }

    /**
     * Validate content for suspicious patterns
     */
    private function validateContentPatterns(string $content): void
    {
        // Check for potentially dangerous PHP patterns
        $suspiciousPatterns = [
            '/eval\s*\(/i',
            '/exec\s*\(/i',
            '/system\s*\(/i',
            '/shell_exec\s*\(/i',
            '/passthru\s*\(/i',
            '/file_get_contents\s*\(\s*["\']https?:\/\//i',
            '/curl_exec\s*\(/i',
            '/base64_decode\s*\(/i',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                Log::channel('security')->error('Suspicious content pattern detected', [
                    'pattern'         => $pattern,
                    'content_preview' => substr($content, 0, 200),
                ]);

                throw new InvalidArgumentException('Content contains suspicious patterns');
            }
        }
    }
}
