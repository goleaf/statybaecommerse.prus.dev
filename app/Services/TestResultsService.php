<?php

declare(strict_types=1);

namespace App\Services;

use App\ViewModels\TestResultsViewModel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Throwable;

final class TestResultsService
{
    private const RELATIVE_RESULTS_PATH = 'storage/app/test-results.json';

    /**
     * @var array<string, mixed>
     */
    private const DEFAULT_META = [
        'created_at'      => null,
        'started_at'      => null,
        'completed_at'    => null,
        'last_updated_at' => null,
        'is_running'      => false,
        'total'           => 0,
        'completed_total' => 0,
        'current_test'    => null,
        'current_index'   => 0,
    ];

    /**
     * @var array<string, array<string, string>>
     */
    private const STATUS_PRESENTATION = [
        'passed' => [
            'label_key' => 'frontend.test_results.status.passed',
            'badge'     => 'inline-flex items-center justify-center rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-600 ring-1 ring-inset ring-emerald-400/60',
        ],
        'failed' => [
            'label_key' => 'frontend.test_results.status.failed',
            'badge'     => 'inline-flex items-center justify-center rounded-full bg-rose-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-rose-600 ring-1 ring-inset ring-rose-400/60',
        ],
        'running' => [
            'label_key' => 'frontend.test_results.status.running',
            'badge'     => 'inline-flex items-center justify-center rounded-full bg-amber-400/20 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-700 ring-1 ring-inset ring-amber-500/50',
        ],
        'pending' => [
            'label_key' => 'frontend.test_results.status.pending',
            'badge'     => 'inline-flex items-center justify-center rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-700 ring-1 ring-inset ring-slate-300',
        ],
    ];

    public function __construct(
        private readonly ?string $resultsPath = null
    ) {}

    public function buildViewModel(): TestResultsViewModel
    {
        $raw = $this->readRawDataset();

        $tests = $this->normalizeTests($raw['tests']);
        $orderedIdentifiers = $this->normalizeOrder($raw['order'], $tests);
        $orderedTests = $this->applyOrder($tests, $orderedIdentifiers);
        $summary = $this->summarise($orderedTests);
        $failedTests = $this->extractFailed($orderedTests);
        $progressSegments = $this->progressSegments($summary);
        $meta = $this->formatMeta($raw['meta'], $summary, $orderedTests);

        return new TestResultsViewModel(
            meta: $meta,
            tests: $orderedTests,
            summary: $summary,
            failedTests: $failedTests,
            progressSegments: $progressSegments,
            hasData: count($orderedTests) > 0,
            resultsPathRelative: self::RELATIVE_RESULTS_PATH,
            statusLegend: $this->statusLegend(),
        );
    }

    /**
     * @return array{meta: array<string, mixed>, tests: array<string, mixed>, order: array<int, string>}
     */
    private function readRawDataset(): array
    {
        $path = $this->resultsPath ?? storage_path('app/test-results.json');

        if (! File::exists($path)) {
            return [
                'meta'  => self::DEFAULT_META,
                'tests' => [],
                'order' => [],
            ];
        }

        $contents = File::get($path);
        $decoded = json_decode($contents ?: '[]', true);

        if (! is_array($decoded)) {
            return [
                'meta'  => self::DEFAULT_META,
                'tests' => [],
                'order' => [],
            ];
        }

        return [
            'meta'  => array_replace(self::DEFAULT_META, (array) ($decoded['meta'] ?? [])),
            'tests' => (array) ($decoded['tests'] ?? []),
            'order' => array_values(array_filter((array) ($decoded['order'] ?? []))),
        ];
    }

    /**
     * @param  array<string|int, mixed>            $tests
     * @return array<string, array<string, mixed>>
     */
    private function normalizeTests(array $tests): array
    {
        $legend = $this->statusLegend();
        $normalized = [];

        foreach ($tests as $identifier => $rawTest) {
            $test = (array) $rawTest;
            $id = (string) ($test['id'] ?? $identifier);

            if ($id === '') {
                continue;
            }

            $status = $this->normalizeStatus($test['status'] ?? 'pending');
            $groups = $this->normalizeGroups($test['groups'] ?? []);

            $normalized[$id] = [
                'id'                 => $id,
                'status'             => $status,
                'status_label'       => $legend[$status]['label'],
                'status_badge_class' => $legend[$status]['badge'],
                'hash'               => (string) ($test['hash'] ?? md5($id)),
                'groups'             => $groups,
                'output'             => $this->normalizeText($test['output'] ?? null),
                'error_output'       => $this->normalizeText($test['error_output'] ?? null),
                'duration'           => $this->formatDuration($test['last_run_duration'] ?? null),
                'raw_duration'       => $this->normalizeFloat($test['last_run_duration'] ?? null),
                'ran_at'             => $this->formatTimestamp($test['run_at'] ?? null),
            ];
        }

        return $normalized;
    }

    /**
     * @param  array<int|string, mixed>            $order
     * @param  array<string, array<string, mixed>> $tests
     * @return array<int, string>
     */
    private function normalizeOrder(array $order, array $tests): array
    {
        $normalized = [];

        foreach ($order as $identifier) {
            $identifier = (string) $identifier;

            if ($identifier === '' || ! isset($tests[$identifier])) {
                continue;
            }

            $normalized[] = $identifier;
        }

        return $normalized;
    }

    /**
     * @param  array<string, array<string, mixed>> $tests
     * @param  array<int, string>                  $orderedIdentifiers
     * @return array<int, array<string, mixed>>
     */
    private function applyOrder(array $tests, array $orderedIdentifiers): array
    {
        $ordered = [];
        $used = [];

        foreach ($orderedIdentifiers as $identifier) {
            $ordered[] = $tests[$identifier];
            $used[$identifier] = true;
        }

        foreach ($tests as $identifier => $test) {
            if (! isset($used[$identifier])) {
                $ordered[] = $test;
            }
        }

        return $ordered;
    }

    /**
     * @param  array<int, array<string, mixed>> $tests
     * @return array<string, int|float>
     */
    private function summarise(array $tests): array
    {
        $total = count($tests);
        $passed = 0;
        $failed = 0;
        $running = 0;

        foreach ($tests as $test) {
            $status = (string) ($test['status'] ?? 'pending');

            if ($status === 'passed') {
                $passed++;
            } elseif ($status === 'failed') {
                $failed++;
            } elseif ($status === 'running') {
                $running++;
            }
        }

        $completed = $passed + $failed;
        $pending = max($total - $completed - $running, 0);
        $successRate = $total > 0 ? round(($passed / $total) * 100, 1) : 0.0;

        return [
            'total'        => $total,
            'passed'       => $passed,
            'failed'       => $failed,
            'running'      => $running,
            'pending'      => $pending,
            'completed'    => $completed,
            'success_rate' => $successRate,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>> $tests
     * @return array<int, array<string, mixed>>
     */
    private function extractFailed(array $tests): array
    {
        return array_values(array_filter($tests, static fn (array $test): bool => ($test['status'] ?? 'pending') === 'failed'));
    }

    /**
     * @param  array<string, int|float> $summary
     * @return array<string, string>
     */
    private function progressSegments(array $summary): array
    {
        $total = max((int) ($summary['total'] ?? 0), 1);

        return [
            'passed'  => $this->formatPercentage((int) ($summary['passed'] ?? 0), $total),
            'failed'  => $this->formatPercentage((int) ($summary['failed'] ?? 0), $total),
            'running' => $this->formatPercentage((int) ($summary['running'] ?? 0), $total),
            'pending' => $this->formatPercentage((int) ($summary['pending'] ?? 0), $total),
        ];
    }

    /**
     * @param  array<string, mixed>             $meta
     * @param  array<string, int|float>         $summary
     * @param  array<int, array<string, mixed>> $orderedTests
     * @return array<string, mixed>
     */
    private function formatMeta(array $meta, array $summary, array $orderedTests): array
    {
        $meta = array_replace(self::DEFAULT_META, $meta);

        $meta['total'] = $meta['total'] ?: (int) ($summary['total'] ?? 0);
        $meta['completed_total'] = $meta['completed_total'] ?: (int) ($summary['completed'] ?? 0);
        $meta['last_updated_at'] = $meta['last_updated_at'] ?? now()->toIso8601String();

        $statusKey = $meta['is_running'] === true
            ? 'frontend.test_results.meta.status.running'
            : 'frontend.test_results.meta.status.idle';

        return [
            'status_label'       => __($statusKey),
            'is_running'         => (bool) $meta['is_running'],
            'started_at'         => $this->formatTimestamp($meta['started_at']),
            'completed_at'       => $this->formatTimestamp($meta['completed_at']),
            'created_at'         => $this->formatTimestamp($meta['created_at']),
            'last_updated_at'    => $this->formatTimestamp($meta['last_updated_at']),
            'current_test'       => $meta['current_test'] ?? null,
            'current_test_short' => $this->shortIdentifier($meta['current_test'] ?? null),
            'current_index'      => (int) $meta['current_index'],
            'total'              => (int) $meta['total'],
            'completed_total'    => (int) $meta['completed_total'],
            'has_results'        => count($orderedTests) > 0,
        ];
    }

    /**
     * @return array<string, array{label: string, badge: string}>
     */
    private function statusLegend(): array
    {
        $legend = [];

        foreach (self::STATUS_PRESENTATION as $status => $presentation) {
            $legend[$status] = [
                'label' => __($presentation['label_key']),
                'badge' => $presentation['badge'],
            ];
        }

        return $legend;
    }

    private function normalizeStatus(string $status): string
    {
        $status = strtolower(trim($status));

        if (! isset(self::STATUS_PRESENTATION[$status])) {
            return 'pending';
        }

        return $status;
    }

    /**
     * @return array<int, string>
     */
    private function normalizeGroups(mixed $groups): array
    {
        $normalized = [];

        foreach ((array) $groups as $group) {
            $group = trim((string) $group);

            if ($group === '') {
                continue;
            }

            $normalized[] = $group;
        }

        return array_values(array_unique($normalized));
    }

    private function normalizeText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function normalizeFloat(null|int|float|string $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return round((float) $value, 4);
    }

    private function formatDuration(null|int|float|string $value): ?string
    {
        $normalized = $this->normalizeFloat($value);

        if ($normalized === null) {
            return null;
        }

        $formatted = number_format($normalized, 3, '.', '');

        return __('frontend.test_results.duration_seconds', ['value' => $formatted]);
    }

    private function formatTimestamp(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)
                ->locale(app()->getLocale())
                ->translatedFormat('Y-m-d H:i:s');
        } catch (Throwable) {
            return $value;
        }
    }

    private function shortIdentifier(?string $identifier): ?string
    {
        if ($identifier === null || $identifier === '') {
            return null;
        }

        return (string) str($identifier)->afterLast('\\')->afterLast(':');
    }

    private function formatPercentage(int $portion, int $total): string
    {
        $percentage = $total > 0 ? ($portion / $total) * 100 : 0.0;

        return rtrim(rtrim(number_format($percentage, 2, '.', ''), '0'), '.') ?: '0';
    }
}
