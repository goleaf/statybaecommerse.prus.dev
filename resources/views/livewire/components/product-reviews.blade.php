@php
    use Illuminate\Support\Str;

    $reviewerName = static function ($review): string {
        return $review->reviewer_name
            ?? optional($review->user)->name
            ?? __('frontend.reviews.anonymous');
    };

    $reviewerInitial = static function ($review) use ($reviewerName): string {
        return Str::upper(Str::substr($reviewerName($review), 0, 1));
    };
@endphp

<section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
    <div class="space-y-8 p-6 lg:p-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <div class="space-y-3">
                <h2 class="text-lg font-semibold text-slate-900">
                    {{ __('product_page.customer_feedback') }}
                </h2>

                <p class="text-sm text-slate-500">
                    {{ trans_choice('translations.reviews_count', $totalReviews, ['count' => $totalReviews]) }}
                </p>

                @if ($totalReviews > 0)
                    <div class="flex items-center gap-3 text-sm text-slate-600">
                        <div class="flex items-center gap-1 text-amber-400">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg class="h-4 w-4 {{ $i <= round($averageRating) ? 'fill-current' : 'stroke-current text-slate-200' }}"
                                     viewBox="0 0 20 20" aria-hidden="true">
                                    <path
                                        d="M10 15.27l-5.18 2.73 1-5.82-4.23-4.12 5.85-.85L10 2l2.56 5.21 5.85.85-4.23 4.12 1 5.82z" />
                                </svg>
                            @endfor
                        </div>
                        <span class="text-sm font-semibold text-slate-900">{{ number_format((float) $averageRating, 1) }}</span>
                        <span class="text-xs text-slate-500">/ 5</span>
                    </div>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-3">
                @auth
                    <button
                        type="button"
                        wire:click="toggleReviewForm"
                        wire:loading.attr="disabled"
                        wire:confirm="{{ __('translations.confirm_toggle_review_form') }}"
                        class="inline-flex items-center gap-2 rounded-full bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 4v12m6-6H4" />
                        </svg>
                        {{ __('translations.write_review') }}
                    </button>
                @else
                    <button
                        type="button"
                        wire:click="promptLogin"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-full bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.5a2 2 0 11-4 0 2 2 0 014 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16a6 6 0 1112 0H4z" />
                        </svg>
                        {{ __('translations.login_to_review') }}
                    </button>
                @endauth
            </div>
        </div>

        {{-- Display a login prompt when guests attempt to interact with review features. --}}
        @if ($showLoginPrompt)
            <div class="rounded-2xl border border-sky-200 bg-sky-50 p-5 text-sky-800">
                <div class="flex items-start gap-3">
                    <svg class="h-5 w-5 mt-0.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-.75-11.25a.75.75 0 011.5 0V9.5a.75.75 0 01-1.5 0V6.75zm0 4.5a.75.75 0 011.5 0v1a.75.75 0 01-1.5 0v-1z" clip-rule="evenodd" />
                    </svg>
                    <div class="space-y-2">
                        <p class="text-sm font-semibold text-sky-900">
                            {{ __('translations.review_login_prompt_title') }}
                        </p>
                        <p class="text-sm text-sky-800">
                            {{ __('translations.review_login_prompt_body') }}
                        </p>
                        <div class="flex flex-wrap items-center gap-3">
                            <a
                                href="{{ route('login') }}"
                                class="inline-flex items-center gap-2 rounded-full bg-primary-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1">
                                {{ __('translations.login_to_review') }}
                            </a>
                            <button
                                type="button"
                                wire:click="hideLoginPrompt"
                                wire:loading.attr="disabled"
                                class="text-xs font-semibold text-sky-700 hover:text-sky-900">
                                {{ __('translations.dismiss_prompt') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if (session()->has('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @error('review')
            <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-600">
                {{ $message }}
            </div>
        @enderror

        {{-- Highlight the authenticated customer's pending review submission. --}}
        @if ($pendingReview)
            <article class="rounded-2xl border border-amber-200 bg-amber-50 p-6 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 text-sm font-semibold text-amber-700">
                        {{ Str::upper(Str::substr(optional($pendingReview->user)->name ?? __('frontend.reviews.anonymous'), 0, 1)) }}
                    </div>
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-amber-200 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-amber-800">
                                {{ __('translations.review_pending_badge') }}
                            </span>
                            <span class="text-xs text-amber-700">
                                {{ __('translations.review_pending_visibility') }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-amber-800">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg class="h-4 w-4 {{ $i <= (int) $pendingReview->rating ? 'text-amber-500' : 'text-amber-200' }}" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 15.27l-5.18 2.73 1-5.82-4.23-4.12 5.85-.85L10 2l2.56 5.21 5.85.85-4.23 4.12 1 5.82z" />
                                </svg>
                            @endfor
                            <span>{{ $pendingReview->created_at?->translatedFormat('Y-m-d') }}</span>
                        </div>
                        @if ($pendingReview->title)
                            <p class="text-sm font-semibold text-amber-900">{{ $pendingReview->title }}</p>
                        @endif
                        <p class="text-sm leading-relaxed text-amber-800">{{ $pendingReview->content }}</p>
                    </div>
                </div>
            </article>
        @endif

        @if ($showReviewForm)
            <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-6">
                <h3 class="text-base font-semibold text-slate-900">
                    {{ __('translations.write_your_review') }}
                </h3>

                <form wire:submit.prevent="submitReview" class="mt-6 space-y-5">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">
                            {{ __('translations.rating') }} <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-2">
                            @for ($i = 1; $i <= 5; $i++)
                                <button
                                    type="button"
                                    wire:click="$set('rating', {{ $i }})"
                                    class="text-2xl {{ $i <= $rating ? 'text-amber-400' : 'text-slate-300' }} transition hover:text-amber-400">
                                    ★
                                </button>
                            @endfor
                            <span class="text-sm text-slate-500">({{ $rating }}/5)</span>
                        </div>
                        @error('rating')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="title" class="mb-2 block text-sm font-medium text-slate-700">
                            {{ __('translations.review_title') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            wire:model="title"
                            type="text"
                            id="title"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200"
                            placeholder="{{ __('translations.review_title_placeholder') }}"
                        >
                        @error('title')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="content" class="mb-2 block text-sm font-medium text-slate-700">
                            {{ __('translations.review_content') }} <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            wire:model="content"
                            id="content"
                            rows="4"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200"
                            placeholder="{{ __('translations.review_content_placeholder') }}"
                        ></textarea>
                        @error('content')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 rounded-full bg-primary-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1">
                            {{ __('translations.submit_review') }}
                        </button>

                        <button
                            type="button"
                            wire:click="toggleReviewForm"
                            wire:loading.attr="disabled"
                            wire:confirm="{{ __('translations.confirm_toggle_review_form') }}"
                            class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-200 focus:ring-offset-1">
                            {{ __('translations.cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        @endif

        @if ($totalReviews > 0)
            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-6">
                    <p class="text-sm font-medium uppercase tracking-wide text-slate-500">
                        {{ __('translations.average_rating') }}
                    </p>
                    <div class="mt-4 space-y-3">
                        <div class="text-4xl font-bold text-slate-900">{{ number_format((float) $averageRating, 1) }}</div>
                        <div class="flex items-center gap-1 text-amber-400">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg class="h-5 w-5 {{ $i <= round($averageRating) ? 'fill-current' : 'stroke-current text-slate-200' }}"
                                     viewBox="0 0 20 20" aria-hidden="true">
                                    <path
                                        d="M10 15.27l-5.18 2.73 1-5.82-4.23-4.12 5.85-.85L10 2l2.56 5.21 5.85.85-4.23 4.12 1 5.82z" />
                                </svg>
                            @endfor
                        </div>
                        <p class="text-sm text-slate-500">
                            {{ trans_choice('translations.reviews_count', $totalReviews, ['count' => $totalReviews]) }}
                        </p>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-6 space-y-3">
                    <p class="text-sm font-medium uppercase tracking-wide text-slate-500">
                        {{ __('translations.rating_distribution') }}
                    </p>
                    @for ($ratingValue = 5; $ratingValue >= 1; $ratingValue--)
                        @php
                            $count = $ratingDistribution[$ratingValue] ?? 0;
                            $percentage = $totalReviews > 0 ? ($count / $totalReviews) * 100 : 0;
                        @endphp
                        <div class="flex items-center gap-3 text-sm text-slate-600">
                            <span class="w-10 text-right font-medium text-slate-700">{{ $ratingValue }}★</span>
                            <div class="h-2 flex-1 rounded-full bg-slate-200">
                                <div class="h-2 rounded-full bg-amber-400" style="width: {{ $percentage }}%;"></div>
                            </div>
                            <span class="w-10 text-right text-slate-500">{{ $count }}</span>
                        </div>
                    @endfor
                </div>
            </div>
        @endif

        <div class="space-y-6">
            @forelse ($reviews as $review)
                @php
                    $name = $reviewerName($review);
                    $initial = $reviewerInitial($review);
                @endphp
                <article class="rounded-2xl border border-slate-100 p-6 shadow-sm" wire:key="review-{{ $review->id }}">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-100 text-sm font-semibold text-primary-600">
                                {{ $initial }}
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-semibold text-slate-900">{{ $name }}</p>
                                <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                    <div class="flex items-center gap-1 text-amber-400">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <svg class="h-4 w-4 {{ $i <= (int) $review->rating ? 'fill-current' : 'stroke-current text-slate-200' }}"
                                                 viewBox="0 0 20 20" aria-hidden="true">
                                                <path
                                                    d="M10 15.27l-5.18 2.73 1-5.82-4.23-4.12 5.85-.85L10 2l2.56 5.21 5.85.85-4.23 4.12 1 5.82z" />
                                            </svg>
                                        @endfor
                                    </div>
                                    <span>{{ $review->created_at?->translatedFormat('Y-m-d') }}</span>
                                </div>
                            </div>
                        </div>

                        @if ($review->title)
                            <p class="text-sm font-medium text-slate-700">{{ $review->title }}</p>
                        @endif
                    </div>

                    <p class="mt-4 text-sm leading-relaxed text-slate-600">{{ $review->content }}</p>

                    {{-- Provide interaction controls for helpful votes and reporting. --}}
                    <div class="mt-6 flex flex-wrap items-center justify-between gap-4 text-sm text-slate-500">
                        <div class="flex items-center gap-3">
                            <span class="font-medium text-slate-600">
                                {{ __('translations.review_helpful_prompt') }}
                            </span>
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    wire:click="markReviewHelpful({{ $review->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="markReviewHelpful({{ $review->id }})"
                                    class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100 disabled:opacity-70">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 5.5l3.5 3.5-3.5 3.5M6.5 9h7" />
                                    </svg>
                                    {{ __('translations.review_mark_helpful') }} ({{ $review->helpful_count ?? 0 }})
                                </button>
                                <button
                                    type="button"
                                    wire:click="reportReview({{ $review->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="reportReview({{ $review->id }})"
                                    wire:confirm="{{ __('translations.confirm_report_review') }}"
                                    class="inline-flex items-center gap-1 rounded-full border border-red-200 bg-red-50 px-3 py-1 text-xs font-semibold text-red-700 transition hover:bg-red-100 disabled:opacity-70">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 4.5l5.5 10h-11L10 4.5zm0 4v3m0 2h.01" />
                                    </svg>
                                    {{ __('translations.review_report_label') }} ({{ $review->reported_count ?? 0 }})
                                </button>
                            </div>
                        </div>
                        <span class="text-xs text-slate-400">
                            {{ __('translations.review_visibility_public') }}
                        </span>
                    </div>
                </article>
            @empty
                <div class="flex flex-col items-center justify-center gap-4 rounded-2xl border border-dashed border-slate-200 bg-slate-50/70 p-10 text-center">
                    <svg class="h-12 w-12 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h6m5 7-4-4H8a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3h8a3 3 0 0 1 3 3v12Z" />
                    </svg>
                    <div class="space-y-1">
                        <h3 class="text-base font-semibold text-slate-900">
                            {{ __('translations.no_reviews_yet') }}
                        </h3>
                        <p class="text-sm text-slate-500">
                            {{ __('translations.be_first_to_review') }}
                        </p>
                    </div>
                    @auth
                        <button
                            type="button"
                            wire:click="toggleReviewForm"
                            wire:loading.attr="disabled"
                            wire:confirm="{{ __('translations.confirm_toggle_review_form') }}"
                            class="inline-flex items-center gap-2 rounded-full bg-primary-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1">
                            {{ __('translations.write_review') }}
                        </button>
                    @else
                        <button
                            type="button"
                            wire:click="promptLogin"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 rounded-full bg-primary-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1">
                            {{ __('translations.login_to_review') }}
                        </button>
                    @endauth
                </div>
            @endforelse
        </div>

        @if ($reviews->hasPages())
            <div>
                {{ $reviews->links('components.pagination') }}
            </div>
        @endif
    </div>
</section>
