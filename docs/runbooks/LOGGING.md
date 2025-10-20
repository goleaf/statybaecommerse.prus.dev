# Logging Runbook

This guide outlines how structured logging works in the application and how to troubleshoot issues by querying logs from HTTP requests and Artisan commands.

## Structured Logging Overview

* All application logs are emitted as JSON. Channels use Monolog's JSON formatter so records can be parsed by log aggregation tools.
* The default `stack` channel fans out to the `single` and `maintenance` files. Both include consistent metadata injected by `App\Logging\ConfigureContextProcessors`.
* Each log entry includes:
  * `correlation_id` – per-request / per-command identifier propagated in responses and CLI output.
  * `request_id` – unique identifier for the individual HTTP request or command invocation.
  * `user_id` – authenticated user's primary key (when available).
  * Operation metadata (`event`, `operation`, `metrics`, `duration_ms`) produced by `App\Support\Logging\StructuredLogger`.
* HTTP requests automatically log start / finish events via `App\Http\Middleware\AssignCorrelationId`.
* Console commands receive the same treatment through listeners registered in `App\Console\Kernel`.

## Finding Logs by Correlation ID

### HTTP Requests

1. Grab the `X-Correlation-ID` header from the HTTP response (or specify your own on the request).
2. Query your log store for the exact `correlation_id` value:
   ```bash
   jq 'select(.correlation_id == "<id>")' storage/logs/laravel.log
   ```
3. You should see paired `event:start` and `event:finish` entries with request metadata and timing metrics.

### Artisan Commands

1. Every command execution receives a generated correlation ID recorded in logs.
2. Filter the JSON logs for the command name to retrieve lifecycle events:
   ```bash
   jq 'select(.operation == "console_command" and .context.command == "reports:generate")' storage/logs/laravel.log
   ```
3. The `metrics` payload contains exit code, memory usage, and any domain-specific counts logged from within the command or services.

## Maintenance Channel

Long-running maintenance jobs can be tailed separately:
```bash
jq '.' storage/logs/maintenance.log
```
The maintenance channel contains the same JSON schema as the default stack and is useful for isolating nightly jobs or background scripts.

## Troubleshooting Checklist

1. **Missing Correlation IDs** – ensure the request passed through the HTTP middleware or the command is executed via Artisan (not an ad-hoc script).
2. **User ID Not Present** – confirm the user is authenticated before logging occurs. Logs emitted prior to authentication will not contain a `user_id`.
3. **Log Noise** – focus on `operation` and `event` fields. `event:error` entries include exception class, message, and duration.
4. **Chained Services** – use the same `StructuredLogger` inside services to record start/finish events for long-running operations. The shared context keeps traces linked.
5. **Querying Specific Metrics** – filter on the `metrics` object. Example: count successful report generations.
   ```bash
   jq 'select(.operation == "reports_generate_command" and .event == "finish") | .metrics.reports_generated' storage/logs/laravel.log
   ```

## Adding New Logs

1. Inject `App\Support\Logging\StructuredLogger` into the class.
2. Start an operation with `$operation = $logger->operation('meaningful_name', $context);`.
3. Call `$operation->finish([...metrics...]);` on success.
4. Call `$operation->fail($exception, [...metrics...]);` inside catch blocks.

Following this pattern ensures every subsystem emits consistent, traceable JSON logs.
