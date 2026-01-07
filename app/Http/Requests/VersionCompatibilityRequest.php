<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

/**
 * Secure form request for version compatibility operations
 *
 * Implements comprehensive validation rules including:
 * - File upload security validation
 * - Content size limits
 * - Path validation with whitelist approach
 * - CSRF protection (inherited from FormRequest)
 */
final class VersionCompatibilityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        // Add authorization logic here based on your requirements
        // For now, allow all authenticated users
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request
     */
    public function rules(): array
    {
        return [
            'file_path' => [
                'sometimes',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\/_\-\.]+$/', // Only allow safe characters
                function ($attribute, $value, $fail) {
                    // Additional path validation
                    if (str_contains($value, '..') || str_contains($value, '~')) {
                        $fail('The file path contains invalid characters.');
                    }

                    // Ensure path starts with allowed directories
                    $allowedPaths = ['app/', 'resources/', 'config/'];
                    $isAllowed = false;

                    foreach ($allowedPaths as $allowedPath) {
                        if (str_starts_with($value, $allowedPath)) {
                            $isAllowed = true;
                            break;
                        }
                    }

                    if (! $isAllowed) {
                        $fail('The file path is not in an allowed directory.');
                    }
                },
            ],

            'directory_path' => [
                'sometimes',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\/_\-]+$/', // Only allow safe characters for directories
                function ($attribute, $value, $fail) {
                    // Directory-specific validation
                    if (str_contains($value, '..') || str_contains($value, '~')) {
                        $fail('The directory path contains invalid characters.');
                    }

                    // Ensure directory starts with allowed paths
                    $allowedDirs = ['app/', 'resources/', 'config/'];
                    $isAllowed = false;

                    foreach ($allowedDirs as $allowedDir) {
                        if (str_starts_with($value, $allowedDir)) {
                            $isAllowed = true;
                            break;
                        }
                    }

                    if (! $isAllowed) {
                        $fail('The directory path is not in an allowed location.');
                    }
                },
            ],

            'content' => [
                'sometimes',
                'string',
                'max:' . (1024 * 1024), // 1MB max content size
                function ($attribute, $value, $fail) {
                    // Content security validation
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
                        if (preg_match($pattern, $value)) {
                            $fail('The content contains potentially dangerous code patterns.');
                            break;
                        }
                    }
                },
            ],

            'uploaded_file' => [
                'sometimes',
                'file',
                File::types(['php'])
                    ->max(1024) // 1MB max file size
                    ->rules([
                        function ($attribute, $value, $fail) {
                            // Additional file validation
                            if ($value && method_exists($value, 'getClientOriginalName')) {
                                $originalName = $value->getClientOriginalName();

                                // Validate original filename
                                if (! preg_match('/^[a-zA-Z0-9\._\-]+\.php$/', $originalName)) {
                                    $fail('The uploaded file has an invalid name format.');
                                }

                                // Check for double extensions
                                if (substr_count($originalName, '.') > 1) {
                                    $fail('The uploaded file cannot have multiple extensions.');
                                }
                            }
                        },
                    ]),
            ],

            'batch_size' => [
                'sometimes',
                'integer',
                'min:1',
                'max:100', // Reasonable batch size limit
            ],

            'options' => [
                'sometimes',
                'array',
                'max:10', // Limit number of options
            ],

            'options.*' => [
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9_\-]+$/', // Only allow safe option values
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules
     */
    public function messages(): array
    {
        return [
            'file_path.regex'      => 'The file path contains invalid characters. Only letters, numbers, slashes, dots, and hyphens are allowed.',
            'directory_path.regex' => 'The directory path contains invalid characters. Only letters, numbers, slashes, and hyphens are allowed.',
            'content.max'          => 'The content size cannot exceed 1MB.',
            'uploaded_file.max'    => 'The uploaded file cannot exceed 1MB.',
            'uploaded_file.mimes'  => 'Only PHP files are allowed for upload.',
            'batch_size.max'       => 'Batch size cannot exceed 100 items.',
            'options.max'          => 'Cannot specify more than 10 options.',
        ];
    }

    /**
     * Prepare the data for validation
     */
    protected function prepareForValidation(): void
    {
        // Sanitize input data
        if ($this->has('file_path')) {
            $this->merge([
                'file_path' => $this->sanitizePath($this->input('file_path')),
            ]);
        }

        if ($this->has('directory_path')) {
            $this->merge([
                'directory_path' => $this->sanitizePath($this->input('directory_path')),
            ]);
        }
    }

    /**
     * Sanitize file/directory paths
     */
    private function sanitizePath(string $path): string
    {
        // Remove null bytes and other dangerous characters
        $path = str_replace(["\0", "\x00"], '', $path);

        // Normalize path separators
        $path = str_replace('\\', '/', $path);

        // Remove multiple consecutive slashes
        $path = preg_replace('/\/+/', '/', $path);

        // Remove leading/trailing slashes and whitespace
        return trim($path, "/ \t\n\r\0\x0B");
    }
}
