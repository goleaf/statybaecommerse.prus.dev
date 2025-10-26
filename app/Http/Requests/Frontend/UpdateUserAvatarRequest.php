<?php

declare(strict_types=1);

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request responsible for validating the authenticated user's avatar upload.
 */
class UpdateUserAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        // Cap avatar uploads using the secure media configuration to keep behaviour consistent across the app.
        $maxKilobytes = static::maxFileSizeKilobytes();
        $allowedMime = implode(',', static::allowedMimeTypes());
        $allowedExtensions = implode(',', static::allowedExtensions());

        return [
            // Enforce explicit file and MIME validation while keeping the legacy image rule for basic sanity checks.
            'avatar' => [
                'required',
                'file',
                'image',
                'mimetypes:' . $allowedMime,
                'mimes:' . $allowedExtensions,
                'max:' . $maxKilobytes,
            ],
        ];
    }

    public function messages(): array
    {
        return [];
    }

    /**
     * Avatar uploads share a strict MIME whitelist to keep the sanitiser predictable.
     *
     * @return array<int, string>
     */
    public static function allowedMimeTypes(): array
    {
        return ['image/jpeg', 'image/png', 'image/webp'];
    }

    /**
     * Provide the extension whitelist matching {@see allowedMimeTypes()} for additional defence in depth.
     *
     * @return array<int, string>
     */
    public static function allowedExtensions(): array
    {
        return ['jpg', 'jpeg', 'png', 'webp'];
    }

    /**
     * Resolve the maximum allowed size in kilobytes for avatar uploads.
     */
    public static function maxFileSizeKilobytes(): int
    {
        $configured = (int) config('media-security.max_size_kb', 5 * 1024);

        return $configured > 0 ? $configured : 5 * 1024;
    }
}
