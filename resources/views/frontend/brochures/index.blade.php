<x-layouts.base title="{{ __('frontend.brochures.meta.title') }}">
    <div class="min-h-screen bg-slate-50">
        <section class="border-b border-slate-200 bg-white">
            <x-container class="px-4 py-10">
                <div class="mx-auto w-full max-w-6xl space-y-3">
                    <h1 class="text-2xl font-bold text-slate-900 md:text-3xl">
                        {{ __('frontend.brochures.heading') }}
                    </h1>
                    <p class="max-w-3xl text-sm text-slate-600">
                        {{ __('frontend.brochures.subheading') }}
                    </p>
                </div>
            </x-container>
        </section>

        <x-container class="px-4 py-10">
            <div class="mx-auto w-full max-w-6xl rounded-2xl border border-slate-200 bg-white shadow-sm">
                @if ($downloads->isEmpty())
                    <div class="p-8 text-center">
                        <h2 class="text-lg font-semibold text-slate-900">
                            {{ __('frontend.brochures.empty_title') }}
                        </h2>
                        <p class="mt-2 text-sm text-slate-600">
                            {{ __('frontend.brochures.empty_description') }}
                        </p>
                    </div>
                @else
                    <ul class="divide-y divide-slate-200">
                        @foreach ($downloads as $download)
                            @php
                                /** @var \App\Models\BrochureFile $file */
                                $file = $download['file'];
                            @endphp

                            <li class="flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <p class="truncate text-base font-semibold text-slate-900">
                                        {{ $file->name }}
                                    </p>
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ $download['brochure_title'] }}
                                    </p>
                                </div>
                                <a
                                    href="{{ $file->downloadUrl() }}"
                                    class="inline-flex shrink-0 items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700"
                                >
                                    {{ __('frontend.brochures.download_cta') }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </x-container>
    </div>
</x-layouts.base>
