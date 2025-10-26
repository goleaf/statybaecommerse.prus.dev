<?php

declare(strict_types=1);

namespace App\Support\Telemetry;

use Illuminate\Support\Facades\Log;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\Sampler\TraceIdRatioBasedSampler;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProviderBuilder;
use OpenTelemetry\SDK\Trace\TracerProviderInterface;
use OpenTelemetry\SemConv\ResourceAttributes;
use Throwable;

final class TelemetryManager
{
    private ?TracerProviderInterface $tracerProvider = null;

    private ?TracerInterface $tracer = null;

    private bool $enabled;

    public function __construct()
    {
        $this->enabled = (bool) config('observability.tracing.enabled', false);

        if ($this->enabled) {
            $this->bootTracer();
        }
    }

    public function isEnabled(): bool
    {
        return $this->enabled && $this->tracer instanceof TracerInterface;
    }

    /**
     * @template TReturn
     *
     * @param  callable(?SpanInterface):TReturn          $callback
     * @param  array<string, int|float|string|bool|null> $attributes
     * @return TReturn
     */
    public function inSpan(string $name, callable $callback, array $attributes = [])
    {
        if (! $this->isEnabled()) {
            return $callback(null);
        }

        $span = $this->tracer?->spanBuilder($name)->startSpan();
        if ($span === null) {
            return $callback(null);
        }

        foreach ($attributes as $key => $value) {
            if ($value === null) {
                continue;
            }

            $span->setAttribute($key, $value);
        }

        try {
            return $callback($span);
        } catch (Throwable $exception) {
            $span->recordException($exception);
            $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());

            throw $exception;
        } finally {
            $span->end();
        }
    }

    private function bootTracer(): void
    {
        try {
            $exporter = $this->createExporter();
            if ($exporter === null) {
                $this->enabled = false;

                return;
            }

            $sampler = new ParentBased(new TraceIdRatioBasedSampler(
                max(0.0, min(1.0, (float) config('observability.tracing.sampler_ratio', 1.0))),
            ));

            $resource = $this->createResource();

            $builder = (new TracerProviderBuilder)
                ->addSpanProcessor(new BatchSpanProcessor($exporter))
                ->setSampler($sampler)
                ->setResource($resource);

            $this->tracerProvider = $builder->build();
            $this->tracer = $this->tracerProvider->getTracer('app.telemetry');

            app()->terminating(function (): void {
                $this->shutdown();
            });
        } catch (Throwable $exception) {
            Log::warning('Telemetry boot failed', [
                'exception' => $exception::class,
                'message'   => $exception->getMessage(),
            ]);
            $this->enabled = false;
            $this->tracerProvider = null;
            $this->tracer = null;
        }
    }

    private function createResource(): ResourceInfo
    {
        $default = ResourceInfoFactory::defaultResource();

        $custom = ResourceInfoFactory::create(Attributes::create([
            ResourceAttributes::SERVICE_NAME           => config('observability.tracing.service_name', config('app.name', 'laravel')),
            ResourceAttributes::SERVICE_NAMESPACE      => config('observability.tracing.service_namespace', 'statybaecommerse'),
            ResourceAttributes::DEPLOYMENT_ENVIRONMENT => config('app.env'),
        ]));

        return $default->merge($custom);
    }

    private function createExporter(): ?SpanExporterInterface
    {
        $endpoint = (string) config('observability.tracing.otlp.endpoint', 'http://localhost:4318/v1/traces');

        try {
            $transport = $this->createTransport($endpoint);
        } catch (Throwable $exception) {
            Log::warning('Telemetry transport creation failed', [
                'endpoint'  => $endpoint,
                'exception' => $exception::class,
                'message'   => $exception->getMessage(),
            ]);

            return null;
        }

        return new SpanExporter($transport);
    }

    private function createTransport(string $endpoint): TransportInterface
    {
        $headers = $this->parseHeaders((string) config('observability.tracing.otlp.headers', ''));
        $compression = config('observability.tracing.otlp.compression');
        $timeout = (float) config('observability.tracing.otlp.timeout', 10.0);

        return (new OtlpHttpTransportFactory)->create(
            $endpoint,
            'application/x-protobuf',
            $headers,
            $compression,
            $timeout,
        );
    }

    /**
     * @return array<string, string>
     */
    private function parseHeaders(string $headers): array
    {
        if (trim($headers) === '') {
            return [];
        }

        $parsed = [];
        foreach (explode(',', $headers) as $part) {
            $pair = explode('=', $part, 2);
            $name = trim($pair[0] ?? '');
            $value = trim($pair[1] ?? '');

            if ($name === '' || $value === '') {
                continue;
            }

            $parsed[$name] = $value;
        }

        return $parsed;
    }

    private function shutdown(): void
    {
        if ($this->tracerProvider === null) {
            return;
        }

        try {
            $this->tracerProvider->shutdown();
        } catch (Throwable $exception) {
            Log::warning('Telemetry shutdown failed', [
                'exception' => $exception::class,
                'message'   => $exception->getMessage(),
            ]);
        }
    }
}
