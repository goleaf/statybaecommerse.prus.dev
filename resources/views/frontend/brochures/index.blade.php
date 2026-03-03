<x-layouts.base title="{{ __('frontend.brochures.meta.title') }}" description="{{ __('frontend.brochures.meta.description') }}">
    <div class="bg-white py-12">
        <x-container class="px-4 sm:px-6">
            <div class="mx-auto w-full max-w-7xl space-y-8">
                <header class="space-y-3">
                    <h1 class="text-3xl font-semibold text-gray-900 sm:text-4xl">
                        {{ __('frontend.brochures.heading') }}
                    </h1>
                    <p class="max-w-3xl text-sm text-gray-600 sm:text-base">
                        {{ __('frontend.brochures.subheading') }}
                    </p>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        {{ __('frontend.brochures.results_summary', ['brochures' => (int) ($stats['total_brochures'] ?? 0), 'files' => (int) ($stats['total_files'] ?? 0)]) }}
                    </p>
                </header>

                @if ($brochures->isEmpty())
                    <section class="rounded-3xl border border-dashed border-gray-300 bg-gray-50 p-10 text-center">
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('frontend.brochures.empty_title') }}</h2>
                        <p class="mt-2 text-sm text-gray-600">{{ __('frontend.brochures.empty_description') }}</p>
                    </section>
                @else
                    <section class="grid gap-6 lg:grid-cols-2">
                        @foreach ($brochures as $brochure)
                            <article class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                                <header class="flex items-start justify-between gap-4">
                                    <div class="min-w-0 space-y-2">
                                        <h2 class="text-xl font-semibold text-gray-900">{{ $brochure->title }}</h2>
                                        @if (! empty($brochure->description))
                                            <p class="text-sm leading-6 text-gray-600">{{ $brochure->description }}</p>
                                        @endif
                                    </div>
                                    <span class="shrink-0 rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                        {{ __('frontend.brochures.files_count_badge', ['count' => $brochure->files->count()]) }}
                                    </span>
                                </header>

                                <ul class="mt-5 space-y-3">
                                    @foreach ($brochure->files as $file)
                                        <li class="flex flex-col gap-3 rounded-2xl border border-gray-200 bg-gray-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-gray-900">{{ $file->name }}</p>
                                            </div>

                                            <a
                                                href="{{ $file->downloadUrl() }}"
                                                class="inline-flex shrink-0 items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700"
                                            >
                                                {{ __('frontend.brochures.download_cta') }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </article>
                        @endforeach
                    </section>
                @endif
            </div>
        </x-container>
    </div>
</x-layouts.base>
