<?php

declare(strict_types=1);

namespace App\Logging\Processors;

use App\Support\Tracing\Trace;
use Monolog\LogRecord;

final class TraceContextProcessor
{
    public function __invoke(LogRecord|array $record): LogRecord|array
    {
        $context = Trace::current();
        $extra = [
            'trace_id'       => $context->traceId(),
            'span_id'        => $context->spanId(),
            'correlation_id' => $context->correlationId(),
            'traceparent'    => $context->toTraceParent(),
            'trace'          => [
                'id'   => $context->traceId(),
                'span' => [
                    'id' => $context->spanId(),
                ],
                'flags' => $context->traceFlags(),
            ],
            'correlation' => [
                'id' => $context->correlationId(),
            ],
        ];

        if ($context->parentSpanId() !== null) {
            $extra['parent_span_id'] = $context->parentSpanId();
            $extra['trace']['parent'] = [
                'id' => $context->parentSpanId(),
            ];
        }

        if ($record instanceof LogRecord) {
            return $record->with(extra: array_merge($record->extra, $extra));
        }

        $record['extra'] = array_merge($record['extra'] ?? [], $extra);

        return $record->with(extra: $extra);
    }
}
