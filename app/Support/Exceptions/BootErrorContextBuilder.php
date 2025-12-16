<?php

declare(strict_types=1);

namespace App\Support\Exceptions;

use Throwable;

/**
 * Builds comprehensive context for boot error logging.
 */
final class BootErrorContextBuilder
{
    public function __construct(
        private readonly BootErrorDetector $detector,
        private readonly ErrorMessageSanitizer $sanitizer,
        private readonly ActionableMessageGenerator $messageGenerator
    ) {}

    /**
     * Build comprehensive context for boot error logging.
     *
     * @return array<string, mixed>
     */
    public function buildContext(Throwable $e): array
    {
        $message = $this->sanitizer->sanitizeMessage($e->getMessage());
        $isTranslatableError = $this->detector->isTranslatableRecordError($e);

        $context = [
            'error_type'         => 'boot_failure',
            'exception_class'    => get_class($e),
            'message'            => $message,
            'file'               => $this->sanitizer->sanitizeFilePath($e->getFile()),
            'line'               => $e->getLine(),
            'actionable_message' => $this->messageGenerator->generate($e),
            'timestamp'          => now()->toISOString(),
            'environment'        => app()->environment(),
            'request_id'         => request()->header('X-Request-ID') ?? uniqid('req_', true),
        ];

        // Add specific context for interface implementation errors
        if ($isTranslatableError) {
            $context['fix_suggestion'] = 'Ensure all models implementing TranslatableRecord have a public translations(): HasMany method';
            $context['affected_models'] = ['Product', 'Brand', 'Collection', 'ProductVariant'];
            $context['interface_issue'] = true;
        }

        return $context;
    }
}
