@php
    $translation = fn(string $key, array $replace = []): string => __($key, $replace);
@endphp

<div class="min-h-screen bg-slate-950 text-white"
     wire:poll.3s="refreshResults"
     aria-live="polite"
     aria-busy="{{ $isRunning ? 'true' : 'false' }}">
    <div class="mx-auto flex max-w-6xl flex-col gap-10 px-4 py-12 lg:px-8">
        <header class="text-center">
            <h1 class="text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                {{ $translation('frontend.test_results.title') }}
            </h1>

            @if ($isRunning)
                <div
                     class="mt-3 inline-flex items-center gap-2 rounded-full bg-indigo-500/10 px-4 py-2 text-sm font-medium text-indigo-200">
                    <span class="relative inline-flex h-3 w-3">
                        <span
                              class="absolute inline-flex h-full w-full animate-ping rounded-full bg-indigo-400 opacity-75"></span>
                        <span class="relative inline-flex h-3 w-3 rounded-full bg-indigo-300"></span>
                    </span>

                    {{ $translation('frontend.test_results.running_hint') }}
                </div>
            @endif
        </header>

        @if ($results['status'] === 'no_data')
            <section class="rounded-2xl border border-white/10 bg-white/5 p-8 text-center shadow-xl shadow-black/20">
                <h2 class="text-xl font-medium text-white">
                    {{ $translation('frontend.test_results.no_data.title') }}
                </h2>

                <p class="mt-2 text-sm text-slate-300">
                    {{ $translation('frontend.test_results.no_data.description') }}
                </p>

                <p class="mt-4 text-xs font-mono text-slate-400">
                    {{ $translation('frontend.test_results.no_data.command') }}
                </p>
            </section>
        @else
            @if ($isRunning)
                <section
                         class="rounded-2xl border border-indigo-500/30 bg-indigo-500/10 p-6 shadow-xl shadow-indigo-500/20">
                    <header class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="text-lg font-medium text-indigo-100">
                            {{ $translation('frontend.test_results.progress.title') }}
                        </h2>

                        <p class="text-sm text-indigo-200">
                            {{ $translation('frontend.test_results.progress.running_message') }}
                        </p>
                    </header>

                    <div class="mt-5">
                        <div class="h-3 w-full overflow-hidden rounded-full border border-indigo-400/40 bg-indigo-950">
                            <div class="h-full rounded-full bg-gradient-to-r from-indigo-300 via-indigo-200 to-indigo-100"
                                 role="progressbar"
                                 aria-valuemin="0"
                                 aria-valuemax="100"
                                 aria-valuenow="{{ $progress }}"
                                 style="width: {{ $progress }}%">
                            </div>
                        </div>

                        <div class="mt-3 flex flex-wrap items-center justify-between text-xs text-indigo-200">
                            <span>
                                {{ $translation('frontend.test_results.progress.percentage', ['value' => $progress]) }}
                            </span>

                            <span>
                                {{ $translation('frontend.test_results.progress.completed', [
                                    'completed' => $results['completed_tests'],
                                    'total' => $results['total_tests'],
                                ]) }}
                            </span>
                        </div>
                    </div>
                </section>
            @endif

            <section>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <article class="rounded-2xl border border-white/10 bg-white/5 p-6 shadow-lg shadow-black/20">
                        <p class="text-sm text-slate-300">
                            {{ $translation('frontend.test_results.stats.total_tests') }}
                        </p>
                        <p class="mt-2 text-3xl font-semibold text-white">
                            {{ number_format($results['total_tests']) }}
                        </p>
                    </article>

                    <article
                             class="rounded-2xl border border-emerald-400/30 bg-emerald-400/10 p-6 shadow-lg shadow-emerald-500/20">
                        <p class="text-sm text-emerald-100">
                            {{ $translation('frontend.test_results.stats.completed_tests') }}
                        </p>
                        <p class="mt-2 text-3xl font-semibold text-emerald-50">
                            {{ number_format($results['completed_tests']) }}
                        </p>
                    </article>

                    <article
                             class="rounded-2xl border border-emerald-400/30 bg-emerald-400/10 p-6 shadow-lg shadow-emerald-500/20">
                        <p class="text-sm text-emerald-100">
                            {{ $translation('frontend.test_results.stats.passed_tests') }}
                        </p>
                        <p class="mt-2 text-3xl font-semibold text-emerald-50">
                            {{ number_format($results['passed_tests']) }}
                        </p>
                    </article>

                    <article
                             class="rounded-2xl border border-rose-400/30 bg-rose-400/10 p-6 shadow-lg shadow-rose-500/20">
                        <p class="text-sm text-rose-100">
                            {{ $translation('frontend.test_results.stats.failed_tests') }}
                        </p>
                        <p class="mt-2 text-3xl font-semibold text-rose-50">
                            {{ number_format($results['failed_tests']) }}
                        </p>
                    </article>
                </div>
            </section>

            <section class="rounded-2xl border border-white/10 bg-white/5 p-6 shadow-xl shadow-black/20">
                <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-slate-300">
                            {{ $translation('frontend.test_results.meta.started_at') }}
                        </p>
                        <p class="text-base font-medium text-white">
                            {{ $results['started_at'] ?? $translation('frontend.test_results.meta.not_available') }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-300">
                            {{ $translation('frontend.test_results.meta.completed_at') }}
                        </p>
                        <p class="text-base font-medium text-white">
                            {{ $results['completed_at'] ?? $translation('frontend.test_results.meta.in_progress') }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-300">
                            {{ $translation('frontend.test_results.meta.state') }}
                        </p>
                        <span
                              class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-sm font-medium text-white">
                            <span class="relative inline-flex h-2 w-2">
                                <span
                                      class="absolute inline-flex h-full w-full rounded-full bg-current opacity-60"></span>
                                <span class="relative inline-flex h-2 w-2 rounded-full bg-current"></span>
                            </span>
                            {{ $translation('frontend.test_results.status.' . ($results['status'] ?? 'completed')) }}
                        </span>
                    </div>
                </header>
            </section>

            @if (!empty($results['tests']))
                <section class="flex flex-col gap-6">
                    <header class="flex flex-col gap-2">
                        <h2 class="text-lg font-semibold text-white">
                            {{ $translation('frontend.test_results.tests.title') }}
                        </h2>
                        <p class="text-sm text-slate-300">
                            {{ $translation('frontend.test_results.tests.description') }}
                        </p>
                    </header>

                    <div class="grid gap-4">
                        @foreach ($results['tests'] as $test)
                            <article
                                     class="rounded-2xl border border-white/10 bg-slate-900/80 p-5 shadow-lg shadow-black/20">
                                <div class="flex flex-col gap-2">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <span @class([
                                            'inline-flex h-8 items-center rounded-full px-3 text-xs font-semibold uppercase tracking-wide',
                                            'bg-emerald-400/10 text-emerald-100 ring-1 ring-inset ring-emerald-400/40' =>
                                                $test['status'] === 'passed',
                                            'bg-rose-400/10 text-rose-100 ring-1 ring-inset ring-rose-400/40' =>
                                                $test['status'] === 'failed',
                                            'bg-slate-500/10 text-slate-200 ring-1 ring-inset ring-slate-400/30' =>
                                                $test['status'] === 'running',
                                            'bg-slate-700/10 text-slate-300 ring-1 ring-inset ring-slate-600/30' => !in_array(
                                                $test['status'],
                                                ['passed', 'failed', 'running'],
                                                true),
                                        ])>
                                            {{ $translation('frontend.test_results.tests.status.' . ($test['status'] ?? 'pending')) }}
                                        </span>

                                        <p class="truncate text-sm text-slate-300" title="{{ $test['file'] }}">
                                            {{ $test['file'] }}
                                        </p>
                                    </div>

                                    <p class="text-xs text-slate-400">
                                        {{ $translation('frontend.test_results.tests.executed_at', ['value' => $test['run_at'] ?? '—']) }}
                                    </p>
                                </div>

                                @if (!empty($test['output']))
                                    <details class="mt-4">
                                        <summary
                                                 class="group inline-flex cursor-pointer items-center gap-2 text-sm font-medium text-indigo-200">
                                            <svg class="h-4 w-4 text-indigo-300 transition-transform group-open:rotate-90"
                                                 viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd"
                                                      d="M6.22 4.22a.75.75 0 0 1 1.06 0l4.5 4.5a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 0 1-1.06-1.06L9.94 10 6.22 6.28a.75.75 0 0 1 0-1.06Z"
                                                      clip-rule="evenodd" />
                                            </svg>
                                            {{ $translation('frontend.test_results.tests.view_output') }}
                                        </summary>

                                        <pre
                                             class="mt-3 max-h-64 overflow-auto rounded-xl border border-indigo-400/20 bg-slate-950/80 p-4 text-xs leading-relaxed text-indigo-100">
                                            {{ $test['output'] }}
                                        </pre>
                                    </details>
                                @endif

                                @if (!empty($test['error']))
                                    <div class="mt-4 rounded-xl border border-rose-500/40 bg-rose-500/10 p-4">
                                        <h3 class="text-sm font-semibold text-rose-100">
                                            {{ $translation('frontend.test_results.tests.error_heading') }}
                                        </h3>
                                        <pre class="mt-2 max-h-48 overflow-auto text-xs text-rose-100/90">
                                            {{ $test['error'] }}
                                        </pre>
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if (!empty($results['errors']))
                <section
                         class="rounded-2xl border border-rose-500/40 bg-rose-500/5 p-6 shadow-inner shadow-rose-900/30">
                    <header class="flex flex-col gap-2">
                        <h2 class="text-lg font-semibold text-rose-100">
                            {{ $translation('frontend.test_results.errors.title', ['count' => count($results['errors'])]) }}
                        </h2>
                        <p class="text-sm text-rose-200">
                            {{ $translation('frontend.test_results.errors.description') }}
                        </p>
                    </header>

                    <div class="mt-5 space-y-6">
                        @foreach ($results['errors'] as $error)
                            <article class="rounded-2xl border border-rose-500/30 bg-rose-500/10 p-5">
                                <p class="text-sm font-semibold text-rose-100">
                                    {{ basename($error['file'] ?? '') }}
                                </p>
                                <p class="mt-1 text-xs text-rose-100/80">
                                    {{ $error['file'] ?? $translation('frontend.test_results.errors.unknown_file') }}
                                </p>

                                @if (!empty($error['error']))
                                    <pre class="mt-3 overflow-auto rounded-xl border border-rose-500/40 bg-rose-500/10 p-4 text-xs text-rose-100/90">
                                        {{ $error['error'] }}
                                    </pre>
                                @endif

                                @if (!empty($error['output']))
                                    <details class="mt-3">
                                        <summary class="text-xs font-medium text-rose-100/90">
                                            {{ $translation('frontend.test_results.errors.view_full_output') }}
                                        </summary>
                                        <pre class="mt-2 overflow-auto rounded-xl border border-rose-500/40 bg-rose-500/10 p-4 text-xs text-rose-100/90">
                                            {{ $error['output'] }}
                                        </pre>
                                    </details>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        @endif

        <footer class="pt-6 text-center text-xs text-slate-400">
            <p>
                {{ $translation('frontend.test_results.footer.last_updated', ['value' => now()->toDateTimeString()]) }}
            </p>

            @if ($isRunning)
                <p class="mt-1">
                    {{ $translation('frontend.test_results.footer.auto_refresh_hint') }}
                </p>
            @endif
        </footer>
    </div>
</div>
