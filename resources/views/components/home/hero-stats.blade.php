@props(['stats' => []])

@php
    $metrics = [];

    if ($stats instanceof \App\Data\Storefront\Home\HomeStatsData) {
        $metrics = $stats->toArray();
    } elseif (is_array($stats)) {
        $metrics = $stats;
    }
@endphp

<section {{ $attributes->class('relative bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950 text-slate-50') }}>
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(96,165,250,0.08),_transparent_60%)]" aria-hidden="true"></div>

    <div class="relative mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8 lg:py-20">
        <div class="rounded-3xl border border-white/10 bg-white/5 p-1 shadow-[0_0_40px_rgba(15,23,42,0.35)] backdrop-blur">
            <x-stats
                :products="$metrics['products_count'] ?? 0"
                :categories="$metrics['categories_count'] ?? 0"
                :brands="$metrics['brands_count'] ?? 0"
                :reviews="$metrics['reviews_count'] ?? 0"
                :rating="$metrics['avg_rating'] ?? 0"
            />
        </div>
    </div>
</section>
