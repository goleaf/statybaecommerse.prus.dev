@extends('frontend.layouts.app')

@section('title', __('frontend.about.meta.title'))
@section('meta_description', __('frontend.about.meta.description'))

@php
    $metricsItems = trans('frontend.about.sections.metrics.items');
    $metricsItems = is_array($metricsItems)
        ? array_values(array_filter($metricsItems, static fn ($item): bool => is_array($item) && isset($item['value'], $item['label'])))
        : [];

    $valuesItems = trans('frontend.about.sections.values.items');
    $valuesItems = is_array($valuesItems)
        ? array_values(array_filter($valuesItems, static fn ($item): bool => is_array($item) && isset($item['title'], $item['description'])))
        : [];
@endphp

@section('content')
    <div class="relative overflow-hidden bg-slate-50 py-12 sm:py-16 lg:py-20">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -top-28 left-1/2 h-72 w-72 -translate-x-[130%] rounded-full bg-emerald-200/55 blur-3xl"></div>
            <div class="absolute right-0 top-20 h-80 w-80 translate-x-1/3 rounded-full bg-lime-200/55 blur-3xl"></div>
        </div>

        <div class="relative mx-auto w-full max-w-6xl space-y-16 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-10 lg:grid-cols-[1.15fr_0.85fr] lg:items-center">
                <div class="space-y-6 rounded-[2rem] border border-slate-200 bg-white/90 p-8 shadow-[0_28px_80px_-52px_rgba(15,23,42,0.7)] backdrop-blur-sm sm:p-10">
                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-4 py-1 text-sm font-semibold tracking-wide text-emerald-700">
                        {{ __('frontend.about.hero.eyebrow') }}
                    </span>
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl lg:text-5xl">
                        {{ __('frontend.about.hero.title') }}
                    </h1>
                    <p class="text-lg leading-8 text-slate-600">
                        {{ __('frontend.about.hero.subtitle') }}
                    </p>
                    <p class="text-base font-semibold text-emerald-700">
                        {{ __('messages.footer_tagline') }}
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($metricsItems as $metric)
                        <article class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-emerald-300 hover:shadow-lg">
                            <p class="text-3xl font-black tracking-tight text-emerald-700">
                                {{ $metric['value'] }}
                            </p>
                            <p class="mt-2 text-sm font-medium leading-6 text-slate-600">
                                {{ $metric['label'] }}
                            </p>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="rounded-[2rem] border border-slate-200 bg-white/80 p-8 shadow-[0_24px_60px_-45px_rgba(15,23,42,0.8)] backdrop-blur-sm sm:p-10">
                <div class="space-y-4 text-center">
                    <h2 class="text-2xl font-bold text-slate-900 sm:text-3xl">
                        {{ __('frontend.about.sections.values.title') }}
                    </h2>
                    <p class="mx-auto max-w-3xl text-base leading-7 text-slate-600">
                        {{ __('frontend.about.sections.values.subtitle') }}
                    </p>
                </div>

                <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($valuesItems as $item)
                        <article class="flex h-full flex-col rounded-2xl border border-slate-200 bg-slate-50 p-6 transition duration-300 hover:border-emerald-300 hover:bg-white hover:shadow-md">
                            <h3 class="text-lg font-semibold text-slate-900">
                                {{ $item['title'] }}
                            </h3>
                            <p class="mt-3 text-sm leading-6 text-slate-600">
                                {{ $item['description'] }}
                            </p>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="overflow-hidden rounded-[2rem] border border-emerald-200 bg-gradient-to-r from-emerald-50 via-green-50 to-lime-50 p-8 shadow-[0_24px_65px_-45px_rgba(5,150,105,0.45)] sm:p-10">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-[1.15fr_auto] sm:items-center">
                    <div class="space-y-3">
                        <h2 class="text-2xl font-bold text-slate-900 sm:text-3xl">
                            {{ __('frontend.about.cta.title') }}
                        </h2>
                        <p class="text-base leading-7 text-slate-600">
                            {{ __('frontend.about.cta.subtitle') }}
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
