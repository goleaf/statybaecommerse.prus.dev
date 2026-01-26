@props([])

<section class="relative">
	<div class="relative overflow-hidden bg-dark">
		<div class="pointer-events-none absolute inset-0 opacity-20">
			<div class="absolute -top-20 -left-20 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
			<div class="absolute -bottom-24 -right-16 h-72 w-72 rounded-full bg-white/10 blur-3xl"></div>
		</div>

		<div class="relative p-8 sm:p-12">
			<div class="grid grid-cols-1 gap-8">
				<div class="rounded-xl border border-white/15 bg-white/5 backdrop-blur-sm p-8 text-white">
                    @php
                        $locale = app()->getLocale();
                    @endphp

                    <div class="space-y-4 max-w-2xl">
						<span class="inline-flex items-center gap-2 rounded-full border border-white/20 px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-black">{{ __('messages.home_mission_badge') }}</span>
						<h2 class="text-2xl sm:text-3xl font-bold">{{ __('messages.home_mission_title') }}</h2>
						<p class="text-white/80 leading-relaxed">{{ __('messages.home_mission_subtitle') }}</p>
                        <div class="flex flex-wrap items-center gap-3">
                            <a href="mailto:{{ config('app.email', 'support@statybae.com') }}" class="px-6 py-3 bg-sage font-semibold transition-all duration-300 transform hover:scale-105 flex items-center justify-center gap-2 relative overflow-hidden group rounded-md">
								<div class="absolute inset-0 bg-gradient-to-r from-brand-primary to-brand-primary-dark opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-10"></div>
								<svg class="w-4 h-4 relative z-30 transition-all duration-300 group-hover:scale-110 !text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 12l9 9 11-18" />
								</svg>
								<span class="relative z-30 transition-colors duration-300 !text-black">{{ __('messages.home_mission_consultation') }}</span>
							</a>
                            <a href="{{ Route::has('localized.collections.index') ? route('localized.collections.index', ['locale' => $locale]) : '#' }}" class="px-6 py-3 border-2 border-sage font-semibold transition-all duration-300 transform hover:scale-105 flex items-center justify-center gap-2 relative group rounded-md text-white hover:text-black">
								<svg class="w-4 h-4 relative z-10 transition-all duration-300 group-hover:scale-110 text-white group-hover:text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
								</svg>
								<span class="relative z-10 transition-colors duration-300 text-white group-hover:text-black">{{ __('messages.home_mission_view_solutions') }}</span>
							</a>
						</div>
					</div>
				</div>

				<div class="rounded-xl border border-white/15 bg-white/5 backdrop-blur-sm p-8 text-white">
                    <div class="space-y-3">
						<span class="inline-flex items-center gap-2 rounded-full border border-white/20 px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-black">{{ __('messages.home_loyalty_badge') }}</span>
						<h3 class="text-2xl font-bold">{{ __('messages.home_loyalty_title') }}</h3>
						<p class="text-white/80">{{ __('messages.home_loyalty_subtitle') }}</p>
					</div>

                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        <a href="{{ Route::has('register') ? route('register') : '#' }}" class="px-6 py-3 bg-sage font-semibold transition-all duration-300 transform hover:scale-105 flex items-center justify-center gap-2 relative overflow-hidden group rounded-md">
							<div class="absolute inset-0 bg-gradient-to-r from-brand-primary to-brand-primary-dark opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-10"></div>
							<svg class="w-4 h-4 relative z-30 transition-all duration-300 group-hover:scale-110 !text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
							</svg>
							<span class="relative z-30 transition-colors duration-300 !text-black">{{ __('messages.home_loyalty_join') }}</span>
						</a>
                        <a href="{{ Route::has('refer.friend') ? route('refer.friend') : (Route::has('register') ? route('register') : '#') }}" class="px-6 py-3 border-2 border-sage font-semibold transition-all duration-300 transform hover:scale-105 flex items-center justify-center gap-2 relative group rounded-md text-white hover:text-black">
							<svg class="w-4 h-4 relative z-10 transition-all duration-300 group-hover:scale-110 text-white group-hover:text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 8a3 3 0 11-6 0 3 3 0 016 0zM2 20a8 8 0 1116 0" />
							</svg>
							<span class="relative z-10 transition-colors duration-300 text-white group-hover:text-black">{{ __('messages.home_loyalty_invite_friend') }}</span>
						</a>
					</div>

					<div class="mt-8 rounded-lg border-2 border-dashed border-white/25 p-6">
						<div class="flex items-start gap-3">
							<svg class="h-6 w-6 text-white/90" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 12h12M3 17h18" />
							</svg>
							<div>
								<p class="text-white font-semibold">{{ __('messages.home_loyalty_catalog_title') }}</p>
								<p class="text-white/75 text-sm">{{ __('messages.home_loyalty_catalog_subtitle') }}</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

