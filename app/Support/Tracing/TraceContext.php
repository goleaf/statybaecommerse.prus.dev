<?php

declare(strict_types=1);

namespace App\Support\Tracing;

use function count;

use Illuminate\Support\Str;

use function strlen;

use Symfony\Component\HttpFoundation\HeaderBag;

final class TraceContext
{
    public const DEFAULT_TRACE_FLAGS = '01';

    private function __construct(
        private readonly string $traceId,
        private readonly string $spanId,
        private readonly ?string $parentSpanId,
        private readonly string $correlationId,
        private readonly string $traceFlags,
    ) {}

    public static function fromHeaders(HeaderBag $headers, string $correlationId): self
    {
        $traceparent = self::sanitizeHeader($headers->get('traceparent'));

        if ($traceparent !== null) {
            $parts = explode('-', (string) $traceparent);
            if (count($parts) >= 4) {
                $traceId = self::validTraceId($parts[1] ?? '') ? strtolower($parts[1]) : null;
                $parentSpanId = self::validSpanId($parts[2] ?? '') ? strtolower($parts[2]) : null;
                $flags = self::sanitizeTraceFlags($parts[3] ?? self::DEFAULT_TRACE_FLAGS);

                if ($traceId !== null) {
                    return self::generate(
                        traceId: $traceId,
                        parentSpanId: $parentSpanId,
                        correlationId: $correlationId,
                        traceFlags: $flags,
                    );
                }
            }
        }

        $traceIdHeader = self::sanitizeHeader($headers->get('x-trace-id'));
        $spanIdHeader = self::sanitizeHeader($headers->get('x-span-id'));
        $parentSpanHeader = self::sanitizeHeader($headers->get('x-parent-span-id'));

        $traceId = self::validTraceId((string) $traceIdHeader) ? strtolower((string) $traceIdHeader) : null;
        $incomingSpan = self::validSpanId((string) $spanIdHeader) ? strtolower((string) $spanIdHeader) : null;
        $incomingParent = self::validSpanId((string) $parentSpanHeader) ? strtolower((string) $parentSpanHeader) : null;

        $parentSpanId = $incomingSpan ?? $incomingParent;

        return self::generate(
            traceId: $traceId,
            parentSpanId: $parentSpanId,
            correlationId: $correlationId,
            traceFlags: self::DEFAULT_TRACE_FLAGS,
        );
    }

    public static function generate(
        ?string $traceId = null,
        ?string $parentSpanId = null,
        ?string $correlationId = null,
        string $traceFlags = self::DEFAULT_TRACE_FLAGS,
    ): self {
        $traceId = self::validTraceId((string) $traceId)
            ? strtolower((string) $traceId)
            : self::generateTraceId();

        $spanId = self::generateSpanId();
        $parentSpanId = self::validSpanId((string) $parentSpanId) ? strtolower((string) $parentSpanId) : null;
        $correlationId = self::sanitizeCorrelationId($correlationId);
        $traceFlags = self::sanitizeTraceFlags($traceFlags);

        return new self($traceId, $spanId, $parentSpanId, $correlationId, $traceFlags);
    }

    public function toTraceParent(): string
    {
        $version = '00';

        return sprintf('%s-%s-%s-%s', $version, $this->traceId, $this->spanId, $this->traceFlags);
    }

    public function traceId(): string
    {
        return $this->traceId;
    }

    public function spanId(): string
    {
        return $this->spanId;
    }

    public function parentSpanId(): ?string
    {
        return $this->parentSpanId;
    }

    public function correlationId(): string
    {
        return $this->correlationId;
    }

    public function traceFlags(): string
    {
        return $this->traceFlags;
    }

    /**
     * Create a child span context that retains the trace and correlation identifiers.
     */
    public function child(): self
    {
        return self::generate(
            traceId: $this->traceId,
            parentSpanId: $this->spanId,
            correlationId: $this->correlationId,
            traceFlags: $this->traceFlags,
        );
    }

    private static function sanitizeHeader(?string $value): ?string
    {
        $value = is_string($value) ? trim($value) : null;

        return $value !== '' ? $value : null;
    }

    private static function validTraceId(string $value): bool
    {
        return strlen($value) === 32 && ctype_xdigit($value) && strtolower($value) !== str_repeat('0', 32);
    }

    private static function validSpanId(string $value): bool
    {
        return strlen($value) === 16 && ctype_xdigit($value) && strtolower($value) !== str_repeat('0', 16);
    }

    private static function generateTraceId(): string
    {
        return bin2hex(random_bytes(16));
    }

    private static function generateSpanId(): string
    {
        return bin2hex(random_bytes(8));
    }

    private static function sanitizeCorrelationId(?string $correlationId): string
    {
        if (is_string($correlationId) && $correlationId !== '') {
            return $correlationId;
        }

        return Str::uuid()->toString();
    }

    private static function sanitizeTraceFlags(?string $flags): string
    {
        $flags = is_string($flags) ? strtolower(trim($flags)) : self::DEFAULT_TRACE_FLAGS;

        if ($flags === '' || ! ctype_xdigit($flags)) {
            return self::DEFAULT_TRACE_FLAGS;
        }

        if (strlen($flags) !== 2) {
            $flags = str_pad(substr($flags, 0, 2), 2, '0');
        }

        return $flags;
    }
}
