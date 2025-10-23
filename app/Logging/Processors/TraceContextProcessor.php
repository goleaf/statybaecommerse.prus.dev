<?php

declare(strict_types=1);

namespace App\Logging\Processors;

use App\Support\Tracing\Trace;
use Monolog\LogRecord;

final class TraceContextProcessor
{
    public function __invoke(LogRecord $record): LogRecord
    {
        $extra = $record->extra;
        $context = Trace::current();

        $extra['trace_id'] = $context->traceId();
        $extra['span_id'] = $context->spanId();

        if ($context->parentSpanId() !== null) {
            $extra['parent_span_id'] = $context->parentSpanId();
        }

        $extra['correlation_id'] = $context->correlationId();
        $extra['traceparent'] = $context->toTraceParent();
        $extra['trace'] = [
            'id' => $context->traceId(),
            'span' => [
                'id' => $context->spanId(),
            ],
        ];

        if ($context->parentSpanId() !== null) {
            $extra['trace']['parent'] = [
                'id' => $context->parentSpanId(),
            ];
        }

        $extra['correlation'] = [
            'id' => $context->correlationId(),
        ];

        return $record->with(extra: $extra);
    }
}
