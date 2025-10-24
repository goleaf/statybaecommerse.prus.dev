@extends('layouts.minimal')

@section('title', __('frontend.test_results.title'))
@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

@section('content')
    <div class="bg-slate-950 py-16 sm:py-20">
        <div class="mx-auto w-full max-w-6xl px-6 lg:px-8" data-test="test-results-wrapper">
            <div class="flex flex-col gap-6">
                <div class="flex flex-col gap-4 text-center text-white">
                    <p class="text-sm font-semibold uppercase tracking-wider text-emerald-400">
                        {{ __('frontend.test_results.meta.heading') }}
                    </p>
                    <h1 class="text-3xl font-bold sm:text-4xl">
                        {{ __('frontend.test_results.title') }}
                    </h1>
                    <p class="mx-auto max-w-3xl text-base text-slate-300">
                        {{ __('frontend.test_results.meta.description', ['path' => $viewModel->resultsPathRelative]) }}
                    </p>
                    <div class="flex flex-wrap items-center justify-center gap-3 text-sm text-slate-400"
                         data-test="meta-summary">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                            <span>{{ __('frontend.test_results.summary.success_rate') }}: <strong
                                        class="font-semibold text-white">{{ number_format((float) ($viewModel->summary['success_rate'] ?? 0), 1) }}%</strong></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-2.5 w-2.5 rounded-full bg-amber-400"></span>
                            <span>{{ __('frontend.test_results.meta.status.label') }}: <strong
                                        class="font-semibold text-white">{{ $viewModel->meta['status_label'] }}</strong></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-2.5 w-2.5 rounded-full bg-slate-500"></span>
                            <span>{{ __('frontend.test_results.meta.updated_at') }}: <strong
                                        class="font-semibold text-white">{{ $viewModel->meta['last_updated_at'] ?? '—' }}</strong></span>
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="lg:col-span-2" data-test="summary-panel">
                        <div
                             class="overflow-hidden rounded-3xl bg-slate-900/60 shadow-xl shadow-slate-950/50 ring-1 ring-white/10">
                            <div class="border-b border-white/10 bg-slate-900/70 px-6 py-5">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-300">
                                        {{ __('frontend.test_results.progress.title') }}
                                    </h2>
                                    <span class="rounded-full bg-slate-800 px-3 py-1 text-xs font-semibold text-slate-200">
                                        {{ __('frontend.test_results.summary.completed', ['completed' => $viewModel->summary['completed'], 'total' => $viewModel->summary['total']]) }}
                                    </span>
                                </div>
                            </div>
                            <div class="px-6 py-6">
                                <div class="relative h-4 overflow-hidden rounded-full bg-slate-800">
                                    <div class="absolute inset-y-0 left-0 bg-emerald-500 transition-all duration-500"
                                         style="width: {{ $viewModel->progressSegments['passed'] }}%"></div>
                                    <div class="absolute inset-y-0 left-0 bg-amber-400 transition-all duration-500"
                                         style="width: calc({{ $viewModel->progressSegments['passed'] }}% + {{ $viewModel->progressSegments['running'] }}%)">
                                    </div>
                                    <div class="absolute inset-y-0 left-0 bg-rose-500 transition-all duration-500"
                                         style="width: calc({{ $viewModel->progressSegments['passed'] }}% + {{ $viewModel->progressSegments['running'] }}% + {{ $viewModel->progressSegments['failed'] }}%)">
                                    </div>
                                </div>
                                <dl class="mt-6 grid gap-5 sm:grid-cols-4">
                                    <div class="flex flex-col gap-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                            {{ __('frontend.test_results.summary.total') }}</dt>
                                        <dd class="text-2xl font-bold text-white">{{ $viewModel->summary['total'] }}</dd>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-emerald-300">
                                            {{ __('frontend.test_results.status.passed') }}</dt>
                                        <dd class="text-2xl font-bold text-emerald-300">{{ $viewModel->summary['passed'] }}
                                        </dd>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-rose-300">
                                            {{ __('frontend.test_results.status.failed') }}</dt>
                                        <dd class="text-2xl font-bold text-rose-300">{{ $viewModel->summary['failed'] }}
                                        </dd>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-amber-300">
                                            {{ __('frontend.test_results.status.running') }}</dt>
                                        <dd class="text-2xl font-bold text-amber-200">{{ $viewModel->summary['running'] }}
                                        </dd>
                                    </div>
                                </dl>
                                <div class="mt-6 grid gap-2 sm:grid-cols-2">
                                    <div
                                         class="rounded-2xl bg-slate-900 px-4 py-4 text-sm text-slate-300 ring-1 ring-white/5">
                                        <div class="flex items-center justify-between">
                                            <span>{{ __('frontend.test_results.meta.started_at') }}</span>
                                            <span
                                                  class="font-semibold text-white">{{ $viewModel->meta['started_at'] ?? '—' }}</span>
                                        </div>
                                    </div>
                                    <div
                                         class="rounded-2xl bg-slate-900 px-4 py-4 text-sm text-slate-300 ring-1 ring-white/5">
                                        <div class="flex items-center justify-between">
                                            <span>{{ __('frontend.test_results.meta.completed_at') }}</span>
                                            <span
                                                  class="font-semibold text-white">{{ $viewModel->meta['completed_at'] ?? '—' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-6">
                        <div class="rounded-3xl bg-slate-900/60 shadow-xl shadow-slate-950/50 ring-1 ring-white/10"
                             data-test="meta-panel">
                            <div class="border-b border-white/10 bg-slate-900/70 px-6 py-4">
                                <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-300">
                                    {{ __('frontend.test_results.meta.title') }}
                                </h2>
                            </div>
                            <dl class="px-6 py-6 text-sm text-slate-200">
                                <div class="flex items-center justify-between border-b border-white/5 py-3">
                                    <dt class="text-slate-400">{{ __('frontend.test_results.meta.current_test') }}</dt>
                                    <dd class="text-right font-semibold text-white">
                                        {{ $viewModel->meta['current_test_short'] ?? __('frontend.test_results.meta.no_current_test') }}
                                    </dd>
                                </div>
                                <div class="flex items-center justify-between border-b border-white/5 py-3">
                                    <dt class="text-slate-400">{{ __('frontend.test_results.meta.current_position') }}</dt>
                                    <dd class="text-right text-white">
                                        {{ $viewModel->meta['current_index'] }} / {{ $viewModel->meta['total'] }}
                                    </dd>
                                </div>
                                <div class="flex items-center justify-between border-b border-white/5 py-3">
                                    <dt class="text-slate-400">{{ __('frontend.test_results.meta.completed_total') }}</dt>
                                    <dd class="text-right text-white">
                                        {{ $viewModel->meta['completed_total'] }}
                                    </dd>
                                </div>
                                <div class="flex items-center justify-between py-3">
                                    <dt class="text-slate-400">{{ __('frontend.test_results.meta.created_at') }}</dt>
                                    <dd class="text-right text-white">
                                        {{ $viewModel->meta['created_at'] ?? '—' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                        <div class="rounded-3xl bg-slate-900/60 shadow-xl shadow-slate-950/50 ring-1 ring-white/10">
                            <div class="border-b border-white/10 bg-slate-900/70 px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-300">
                                        {{ __('frontend.test_results.status.legend.title') }}
                                    </h2>
                                </div>
                            </div>
                            <div class="px-6 py-5">
                                <dl class="grid gap-3">
                                    @foreach ($viewModel->statusLegend as $status => $presentation)
                                        <div
                                             class="flex items-start justify-between gap-4 rounded-2xl bg-slate-900 px-4 py-3 ring-1 ring-white/5">
                                            <dt class="flex items-center gap-3">
                                                <span
                                                      class="inline-flex h-2.5 w-2.5 rounded-full {{ match ($status) {
                                                          'passed' => 'bg-emerald-400',
                                                          'failed' => 'bg-rose-400',
                                                          'running' => 'bg-amber-300',
                                                          default => 'bg-slate-500',
                                                      } }}"></span>
                                                <span
                                                      class="text-sm font-semibold text-white">{{ $presentation['label'] }}</span>
                                            </dt>
                                            <dd class="text-xs text-slate-400">
                                                {{ __('frontend.test_results.status.legend.descriptions.' . $status) }}
                                            </dd>
                                        </div>
                                    @endforeach
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                @if (!$viewModel->hasData)
                    <div class="rounded-3xl border border-dashed border-slate-700 bg-slate-900/40 px-6 py-12 text-center text-slate-300"
                         data-test="empty-state">
                        <div class="flex flex-col items-center gap-4">
                            <span
                                  class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-slate-800 text-2xl">🧪</span>
                            <h2 class="text-2xl font-semibold text-white">{{ __('frontend.test_results.empty.title') }}
                            </h2>
                            <p class="max-w-xl text-sm text-slate-400">{{ __('frontend.test_results.empty.description') }}
                            </p>
                            <div
                                 class="inline-flex items-center gap-3 rounded-full bg-slate-800/60 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-200">
                                <code>php artisan project:test</code>
                                <span
                                      class="rounded-full bg-slate-900 px-2 py-1 text-[10px] font-bold text-emerald-300">CLI</span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="space-y-6" data-test="results-sections">
                        <div
                             class="rounded-3xl bg-white/3 shadow-2xl shadow-slate-950/70 ring-1 ring-white/10 backdrop-blur">
                            <div class="border-b border-white/10 px-6 py-4">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-300">
                                        {{ __('frontend.test_results.table.title') }}
                                    </h2>
                                    <span
                                          class="rounded-full bg-slate-900/80 px-3 py-1 text-xs font-semibold text-slate-200">
                                        {{ __('frontend.test_results.table.total_rows', ['count' => $viewModel->summary['total']]) }}
                                    </span>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-white/5">
                                    <thead class="bg-slate-900/70">
                                        <tr class="text-xs uppercase tracking-wider text-slate-300">
                                            <th scope="col" class="px-6 py-4 text-left">#</th>
                                            <th scope="col" class="px-6 py-4 text-left">
                                                {{ __('frontend.test_results.table.headers.identifier') }}</th>
                                            <th scope="col" class="px-6 py-4 text-left">
                                                {{ __('frontend.test_results.table.headers.hash') }}</th>
                                            <th scope="col" class="px-6 py-4 text-left">
                                                {{ __('frontend.test_results.table.headers.groups') }}</th>
                                            <th scope="col" class="px-6 py-4 text-left">
                                                {{ __('frontend.test_results.table.headers.status') }}</th>
                                            <th scope="col" class="px-6 py-4 text-left">
                                                {{ __('frontend.test_results.table.headers.duration') }}</th>
                                            <th scope="col" class="px-6 py-4 text-left">
                                                {{ __('frontend.test_results.table.headers.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-white/5">
                                        @foreach ($viewModel->tests as $index => $test)
                                            <tr class="bg-slate-900/40 text-sm text-slate-200">
                                                <td class="px-6 py-4 align-top text-xs text-slate-400">
                                                    #{{ $index + 1 }}</td>
                                                <td class="px-6 py-4 align-top">
                                                    <div class="flex flex-col gap-1">
                                                        <span class="font-semibold text-white">{{ $test['id'] }}</span>
                                                        <span
                                                              class="text-xs text-slate-400">{{ $test['ran_at'] ?? __('frontend.test_results.table.columns.ran_at_unknown') }}</span>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 align-top">
                                                    <code
                                                          class="rounded bg-slate-800/80 px-2 py-1 text-xs text-slate-300">{{ $test['hash'] }}</code>
                                                </td>
                                                <td class="px-6 py-4 align-top">
                                                    <div class="flex flex-wrap gap-2">
                                                        @forelse($test['groups'] as $group)
                                                            <span
                                                                  class="inline-flex items-center gap-1 rounded-full bg-slate-800 px-2 py-1 text-xs text-slate-200">
                                                                <span
                                                                      class="inline-flex h-1.5 w-1.5 rounded-full bg-slate-500"></span>
                                                                {{ $group }}
                                                            </span>
                                                        @empty
                                                            <span
                                                                  class="text-xs text-slate-500">{{ __('frontend.test_results.table.columns.no_groups') }}</span>
                                                        @endforelse
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 align-top">
                                                    <span
                                                          class="{{ $test['status_badge_class'] }}">{{ $test['status_label'] }}</span>
                                                </td>
                                                <td class="px-6 py-4 align-top">
                                                    {{ $test['duration'] ?? __('frontend.test_results.table.columns.unknown_duration') }}
                                                </td>
                                                <td class="px-6 py-4 align-top">
                                                    <details
                                                             class="rounded-2xl bg-slate-900/60 px-3 py-2 text-xs text-slate-300 ring-1 ring-white/5">
                                                        <summary class="cursor-pointer font-semibold text-emerald-300">
                                                            {{ __('frontend.test_results.table.actions.view_details') }}
                                                        </summary>
                                                        <div class="mt-3 flex flex-col gap-3 text-left text-xs">
                                                            <div>
                                                                <p
                                                                   class="mb-1 font-semibold uppercase tracking-wide text-slate-400">
                                                                    {{ __('frontend.test_results.table.details.output') }}
                                                                </p>
                                                                <pre class="max-h-44 overflow-x-auto rounded-xl bg-slate-950/80 p-3 text-[10px] leading-relaxed text-slate-200">{{ $test['output'] ?? __('frontend.test_results.table.details.empty_output') }}</pre>
                                                            </div>
                                                            <div>
                                                                <p
                                                                   class="mb-1 font-semibold uppercase tracking-wide text-rose-300">
                                                                    {{ __('frontend.test_results.table.details.error') }}
                                                                </p>
                                                                <pre class="max-h-44 overflow-x-auto rounded-xl bg-rose-950/40 p-3 text-[10px] leading-relaxed text-rose-100">{{ $test['error_output'] ?? __('frontend.test_results.table.details.empty_error') }}</pre>
                                                            </div>
                                                        </div>
                                                    </details>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="rounded-3xl bg-rose-950/30 shadow-2xl shadow-rose-950/40 ring-1 ring-rose-500/20">
                            <div class="border-b border-rose-500/20 px-6 py-4">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <h2 class="text-sm font-semibold uppercase tracking-wider text-rose-200">
                                        {{ __('frontend.test_results.failed.title') }}
                                    </h2>
                                    <span
                                          class="rounded-full bg-rose-950/60 px-3 py-1 text-xs font-semibold text-rose-100">
                                        {{ __('frontend.test_results.failed.count', ['count' => count($viewModel->failedTests)]) }}
                                    </span>
                                </div>
                            </div>
                            <div class="px-6 py-6">
                                @forelse($viewModel->failedTests as $failed)
                                    <div class="mb-6 rounded-2xl bg-rose-950/40 p-5 ring-1 ring-rose-500/30">
                                        <div class="flex flex-col gap-3">
                                            <div class="flex items-start justify-between gap-3">
                                                <div>
                                                    <p class="text-xs font-semibold uppercase tracking-wide text-rose-300">
                                                        {{ __('frontend.test_results.failed.label') }}
                                                    </p>
                                                    <h3 class="text-base font-semibold text-white">{{ $failed['id'] }}
                                                    </h3>
                                                </div>
                                                <span
                                                      class="{{ $failed['status_badge_class'] }}">{{ $failed['status_label'] }}</span>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-2 text-xs text-rose-200">
                                                <code
                                                      class="rounded bg-rose-900/60 px-2 py-1">{{ $failed['hash'] }}</code>
                                                <span class="text-rose-200/70">·</span>
                                                <span>{{ $failed['duration'] ?? __('frontend.test_results.table.columns.unknown_duration') }}</span>
                                            </div>
                                            <div class="grid gap-4 sm:grid-cols-2">
                                                <div>
                                                    <p
                                                       class="mb-2 text-xs font-semibold uppercase tracking-wide text-rose-200">
                                                        {{ __('frontend.test_results.table.details.error') }}
                                                    </p>
                                                    <pre class="max-h-48 overflow-x-auto rounded-xl bg-rose-950/70 p-3 text-[11px] leading-relaxed text-rose-100">{{ $failed['error_output'] ?? __('frontend.test_results.table.details.empty_error') }}</pre>
                                                </div>
                                                <div>
                                                    <p
                                                       class="mb-2 text-xs font-semibold uppercase tracking-wide text-rose-200">
                                                        {{ __('frontend.test_results.table.details.output') }}
                                                    </p>
                                                    <pre class="max-h-48 overflow-x-auto rounded-xl bg-slate-950/80 p-3 text-[11px] leading-relaxed text-slate-200">{{ $failed['output'] ?? __('frontend.test_results.table.details.empty_output') }}</pre>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div
                                         class="rounded-2xl border border-rose-500/30 bg-rose-950/20 px-6 py-6 text-center text-rose-200">
                                        {{ __('frontend.test_results.failed.none') }}
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
