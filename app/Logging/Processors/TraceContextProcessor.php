<?php

declare(strict_types=1);

namespace App\Logging\Processors;

use App\Support\Tracing\Trace;

final class TraceContextProcessor
{
    public function __invoke(array $record): array
    {
        $context = Trace::current();

        $record['extra']['trace_id'] = $context->traceId();
        $record['extra']['span_id'] = $context->spanId();

        if ($context->parentSpanId() !== null) {
            $record['extra']['parent_span_id'] = $context->parentSpanId();
        }

        $record['extra']['correlation_id'] = $context->correlationId();
        $record['extra']['traceparent'] = $context->toTraceParent();
        $record['extra']['trace'] = [
            'id' => $context->traceId(),
            'span' => [
                'id' => $context->spanId(),
            ],
            'flags' => $context->traceFlags(),
        ];

        if ($context->parentSpanId() !== null) {
            $record['extra']['trace']['parent'] = [
                'id' => $context->parentSpanId(),
            ];
        }

        $record['extra']['correlation'] = [
            'id' => $context->correlationId(),
        ];

        return $record;
    }
}
