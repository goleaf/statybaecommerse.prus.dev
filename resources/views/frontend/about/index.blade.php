@extends('frontend.layouts.app')

@section('title', __('frontend/about.meta.title'))
@section('meta_description', __('frontend/about.meta.description'))

@section('content')
    <div class="bg-gray-50 dark:bg-gray-900 py-12 sm:py-16 lg:py-20">
        <div class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8 space-y-16">
            <section class="grid gap-8 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
                <div class="space-y-6">
                    <span class="inline-flex items-center rounded-full bg-blue-100 px-4 py-1 text-sm font-semibold tracking-wide text-blue-700 dark:bg-blue-900/60 dark:text-blue-200">
                        {{ __('frontend/about.hero.eyebrow') }}
                    </span>
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl lg:text-5xl dark:text-white">
                        {{ __('frontend/about.hero.title') }}
                    </h1>
                    <p class="text-lg leading-7 text-gray-600 dark:text-gray-300">
                        {{ __('frontend/about.hero.subtitle') }}
                    </p>
                    <p class="text-base font-medium text-blue-700 dark:text-blue-300">
                        {{ __('footer_tagline') }}
                    </p>
                    <div>
                        <a href="{{ route('frontend.contact.index') }}"
                           class="inline-flex items-center gap-2 rounded-full bg-blue-600 px-6 py-3 text-base font-semibold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 dark:shadow-none dark:hover:bg-blue-500">
                            {{ __('frontend/about.hero.cta') }}
                            <x-untitledui-arrow-up-right class="h-4 w-4" />
                        </a>
                    </div>
                </div>
                <div class="grid gap-6 sm:grid-cols-2">
                    @foreach (trans('frontend/about.sections.metrics.items') as $metric)
                        <div class="rounded-3xl bg-white p-6 shadow-md ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-white/10">
                            <div class="text-3xl font-bold text-blue-600 dark:text-blue-300">
                                {{ $metric['value'] }}
                            </div>
                            <div class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-300">
                                {{ $metric['label'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="space-y-10">
                <div class="space-y-4 text-center">
                    <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl dark:text-white">
                        {{ __('frontend/about.sections.values.title') }}
                    </h2>
                    <p class="mx-auto max-w-3xl text-base text-gray-600 dark:text-gray-300">
                        {{ __('frontend/about.sections.values.subtitle') }}
                    </p>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach (trans('frontend/about.sections.values.items') as $item)
                        <article class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:border-white/10 dark:bg-gray-800">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $item['title'] }}
                            </h3>
                            <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-300">
                                {{ $item['description'] }}
                            </p>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="rounded-3xl bg-gradient-to-r from-blue-600 to-indigo-600 px-8 py-10 text-white shadow-xl">
                <div class="grid gap-6 sm:grid-cols-[1.1fr_auto] sm:items-center">
                    <div class="space-y-3">
                        <h2 class="text-2xl font-bold sm:text-3xl">
                            {{ __('frontend/about.cta.title') }}
                        </h2>
                        <p class="text-base text-blue-100">
                            {{ __('frontend/about.cta.subtitle') }}
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('frontend.contact.index') }}"
                           class="inline-flex items-center gap-2 rounded-full bg-white px-6 py-3 text-base font-semibold text-blue-700 shadow-sm transition hover:bg-blue-50">
                            {{ __('frontend/about.cta.button') }}
                            <x-untitledui-arrow-up-right class="h-4 w-4" />
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
