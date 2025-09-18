<div class="bg-slate-950 text-slate-50">
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(99,102,241,0.35),_transparent_60%)]"></div>
        <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width="80" height="80" viewBox="0 0 80 80" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" stroke="%23FFFFFF" stroke-opacity="0.04"%3E%3Cpath d="M0 79.5H79.5V0"/%3E%3C/g%3E%3C/svg%3E');"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-24 lg:pb-32">
            <div class="grid gap-16 lg:grid-cols-[minmax(0,_1fr)_minmax(340px,_400px)]">
                <div class="space-y-10">
                    <div class="space-y-6 max-w-3xl">
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.35em] text-white/80">
                            {{ __('Statybae Commerce') }}
                        </span>
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-heading font-semibold leading-tight text-balance">
                            {{ __('Вдохновляющий маркетплейс с категориями, коллекциями и акциями в одном месте') }}
                        </h1>
                        <p class="text-base sm:text-lg text-white/75 leading-relaxed max-w-2xl">
                            {{ __('Откройте свежие подборки, следите за специальными предложениями и управляйте покупками через продуманную экосистему Statybae Commerce.') }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('products.index') }}" class="inline-flex items-center gap-3 rounded-full bg-indigo-500 px-6 py-3 text-sm font-semibold text-white shadow-xl transition hover:bg-indigo-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01" />
                            </svg>
                            {{ __('Начать покупки') }}
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-3 rounded-full border border-white/15 bg-white/10 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/20">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            {{ __('Создать аккаунт') }}
                        </a>
                    </div>
                </div>

                <div class="relative rounded-3xl border border-white/10 bg-white/5 p-8 shadow-2xl backdrop-blur-xl space-y-6">
                    <div class="flex items-center justify-between">
                        <span class="text-xs uppercase tracking-[0.25em] text-white/60">
                            {{ __('Статистика магазина') }}
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-[11px] font-semibold text-white/80">
                            <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            {{ __('Live') }}
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="rounded-2xl bg-white/10 px-4 py-6 text-center shadow-inner">
                            <div class="text-3xl font-bold text-white">
                                {{ number_format($stats['products_count']) }}
                            </div>
                            <div class="mt-2 text-xs uppercase tracking-[0.2em] text-white/60">
                                {{ __('товаров') }}
                            </div>
                        </div>
                        <div class="rounded-2xl bg-white/10 px-4 py-6 text-center shadow-inner">
                            <div class="text-3xl font-bold text-white">
                                {{ number_format($stats['categories_count']) }}
                            </div>
                            <div class="mt-2 text-xs uppercase tracking-[0.2em] text-white/60">
                                {{ __('категорий') }}
                            </div>
                        </div>
                        <div class="rounded-2xl bg-white/10 px-4 py-6 text-center shadow-inner">
                            <div class="text-3xl font-bold text-white">
                                {{ number_format($stats['brands_count']) }}
                            </div>
                            <div class="mt-2 text-xs uppercase tracking-[0.2em] text-white/60">
                                {{ __('брендов') }}
                            </div>
                        </div>
                        <div class="rounded-2xl bg-white/10 px-4 py-6 text-center shadow-inner">
                            <div class="text-3xl font-bold text-white">
                                {{ number_format($stats['reviews_count']) }}
                            </div>
                            <div class="mt-2 text-xs uppercase tracking-[0.2em] text-white/60">
                                {{ __('отзывов') }}
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-xs text-white/70">
                        <span class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ __('Средняя оценка покупателей') }}
                        </span>
                        <span class="inline-flex items-center gap-1 font-semibold text-amber-300">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M11.48 3.499a.562.562 0 011.04 0l2.01 4.073 4.495.654a.563.563 0 01.311.96l-3.25 3.166.768 4.477a.563.563 0 01-.817.593L12 15.347l-4.037 2.125a.563.563 0 01-.817-.593l.768-4.477-3.25-3.165a.563.563 0 01.311-.96l4.495-.654 2.01-4.073z" />
                            </svg>
                            {{ number_format((float) $stats['avg_rating'], 1) }} / 5
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <livewire:home.categories-showcase />
    <livewire:home.collections-showcase />

    <livewire:home.product-shelf :preset="'featured'" :limit="8" />
    <livewire:home.product-shelf :preset="'latest'" :limit="8" />
    <livewire:home.product-shelf :preset="'sale'" :limit="12" :title="__('Акции на продукты')" :subtitle="__('Все товары со скидками и спецпредложениями сейчас доступны без переходов по разделам — выбирайте и добавляйте в корзину.')" />

</div>
