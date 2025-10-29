@extends('components.layouts.base')

@section('title', __('campaign_clicks.all_clicks'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('campaign_clicks.all_clicks') }}</h1>
        
        @auth
            <a href="{{ route('campaign-clicks.create') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                {{ __('campaign_clicks.create_new') }}
            </a>
        @endauth
    </div>

    <!-- Statistics Cards -->
    <p
        id="stats-period-label"
        data-analytics-period="label"
        class="mb-3 text-sm text-gray-500 text-right"
    >
        {{ __('campaign_clicks.all_time') }}
    </p>
    <div class="relative mb-8">
        <div id="statistics-loading" class="absolute inset-0 z-10 hidden items-center justify-center rounded-lg bg-white/75">
            <svg class="h-8 w-8 animate-spin text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        </div>
        <div id="statistics-cards" class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">{{ __('campaign_clicks.total_clicks') }}</p>
                        <p
                            class="text-2xl font-semibold text-gray-900"
                            id="total-clicks"
                            data-analytics-stat="total_clicks"
                        >
                            -
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">{{ __('campaign_clicks.converted_clicks') }}</p>
                        <p
                            class="text-2xl font-semibold text-gray-900"
                            id="converted-clicks"
                            data-analytics-stat="converted_clicks"
                        >
                            -
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">{{ __('campaign_clicks.conversion_rate') }}</p>
                        <p
                            class="text-2xl font-semibold text-gray-900"
                            id="conversion-rate"
                            data-analytics-stat="conversion_rate"
                        >
                            -
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">{{ __('campaign_clicks.conversion_value') }}</p>
                        <p
                            class="text-2xl font-semibold text-gray-900"
                            id="conversion-value"
                            data-analytics-stat="conversion_value"
                        >
                            €0.00
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('campaign_clicks.filters') }}</h3>
        <form id="filters-form" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label for="click_type" class="block text-sm font-medium text-gray-700">{{ __('campaign_clicks.click_type') }}</label>
                <select id="click_type" name="click_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">{{ __('campaign_clicks.all_types') }}</option>
                    <option value="cta">{{ __('campaign_clicks.click_type.cta') }}</option>
                    <option value="banner">{{ __('campaign_clicks.click_type.banner') }}</option>
                    <option value="link">{{ __('campaign_clicks.click_type.link') }}</option>
                    <option value="button">{{ __('campaign_clicks.click_type.button') }}</option>
                    <option value="image">{{ __('campaign_clicks.click_type.image') }}</option>
                </select>
            </div>

            <div>
                <label for="device_type" class="block text-sm font-medium text-gray-700">{{ __('campaign_clicks.device_type') }}</label>
                <select id="device_type" name="device_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">{{ __('campaign_clicks.all_devices') }}</option>
                    <option value="desktop">{{ __('campaign_clicks.device_type.desktop') }}</option>
                    <option value="mobile">{{ __('campaign_clicks.device_type.mobile') }}</option>
                    <option value="tablet">{{ __('campaign_clicks.device_type.tablet') }}</option>
                </select>
            </div>

            <div>
                <label for="country" class="block text-sm font-medium text-gray-700">{{ __('campaign_clicks.country') }}</label>
                <input type="text" id="country" name="country" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="{{ __('campaign_clicks.enter_country') }}">
            </div>

            <div>
                <label for="date_range" class="block text-sm font-medium text-gray-700">{{ __('campaign_clicks.date_range') }}</label>
                <select id="date_range" name="date_range" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">{{ __('campaign_clicks.all_time') }}</option>
                    <option value="day">{{ __('campaign_clicks.day') }}</option>
                    <option value="week">{{ __('campaign_clicks.week') }}</option>
                    <option value="month">{{ __('campaign_clicks.month') }}</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Clicks Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">{{ __('campaign_clicks.clicks_list') }}</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('campaign_clicks.campaign') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('campaign_clicks.click_type') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('campaign_clicks.device_type') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('campaign_clicks.country') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('campaign_clicks.clicked_at') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('campaign_clicks.converted') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('campaign_clicks.actions') }}</th>
                    </tr>
                </thead>
                <tbody id="clicks-table-body" class="bg-white divide-y divide-gray-200">
                    <!-- Data will be loaded here via JavaScript -->
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div id="pagination" class="px-6 py-4 border-t border-gray-200">
            <!-- Pagination will be loaded here via JavaScript -->
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadStatistics();
    loadClicks();

    const filtersForm = document.getElementById('filters-form');
    if (filtersForm) {
        const debouncedReload = debounce(function() {
            loadStatistics();
            loadClicks();
        }, 250);

        filtersForm.addEventListener('change', debouncedReload);
    }
});

/**
 * Lightweight debounce helper so timeframe changes do not spam API calls.
 */
function debounce(callback, delay) {
    let timeoutId;

    return function(...args) {
        window.clearTimeout(timeoutId);
        timeoutId = window.setTimeout(() => callback.apply(this, args), delay);
    };
}

/**
 * Fetch KPI buckets for the requested period and update the dashboard cards.
 */
function loadStatistics() {
    const periodSelect = document.getElementById('date_range');
    const rawPeriod = periodSelect && periodSelect.value ? periodSelect.value.toLowerCase() : 'day';
    const period = rawPeriod || 'day';

    const statsLoading = document.getElementById('statistics-loading');
    const statsCards = document.getElementById('statistics-cards');

    if (statsLoading && statsCards) {
        statsLoading.classList.remove('hidden');
        statsCards.classList.add('opacity-40');
    }

    const url = new URL('/api/campaign-clicks/statistics', window.location.origin);
    url.searchParams.set('period', period);

    fetch(url.toString())
        .then(response => response.json())
        .then(payload => {
            const data = payload && typeof payload === 'object' ? (payload.data ?? payload) : {};
            const normalizedBuckets = mapBuckets(data.buckets ?? {});
            const periodLabels = extractPeriodLabels(periodSelect);

            const activeBucket = normalizedBuckets[period] ?? Object.values(normalizedBuckets)[0] ?? null;
            const activeMetrics = extractMetrics(activeBucket, data);

            updateStatisticsCards(activeMetrics, periodLabels, period);
        })
        .catch(error => console.error('Error loading statistics:', error))
        .finally(() => {
            if (statsLoading && statsCards) {
                statsLoading.classList.add('hidden');
                statsCards.classList.remove('opacity-40');
            }
        });
}

/**
 * Normalise the bucket payload so we can safely address entries by key.
 */
function mapBuckets(buckets) {
    const mapped = {};

    if (Array.isArray(buckets)) {
        buckets.forEach(bucket => {
            const key = String(bucket?.period ?? bucket?.key ?? bucket?.name ?? '').toLowerCase();
            if (key) {
                mapped[key] = bucket;
            }
        });
    } else if (buckets && typeof buckets === 'object') {
        Object.entries(buckets).forEach(([key, value]) => {
            mapped[String(key).toLowerCase()] = value;
        });
    }

    return mapped;
}

/**
 * Build a lookup for translated period labels directly from the select options.
 */
function extractPeriodLabels(selectElement) {
    const fallback = {
        day: 'Day',
        week: 'Week',
        month: 'Month',
    };

    if (!selectElement) {
        return fallback;
    }

    return {
        day: selectElement.querySelector('option[value="day"]')?.textContent?.trim() || fallback.day,
        week: selectElement.querySelector('option[value="week"]')?.textContent?.trim() || fallback.week,
        month: selectElement.querySelector('option[value="month"]')?.textContent?.trim() || fallback.month,
    };
}

/**
 * Merge bucket-level metrics with legacy totals so the UI stays resilient.
 */
function extractMetrics(bucket, data) {
    if (!bucket && !data) {
        // Nothing to map when both sources are missing.
        return { metrics: {}, totals: {}, enriched: null, label: null };
    }

    const metrics = bucket?.metrics ?? bucket ?? {};
    const totals = data?.totals ?? {};
    const periodLabel = data?.period?.label ?? bucket?.label ?? bucket?.title ?? bucket?.period ?? null;

    // Harmonise the enriched analytics payload (period/totals/insights/charts) the API now emits so
    // the legacy cards can surface the same metrics without custom client-side mapping everywhere.
    const insights = data?.insights ?? null;
    const charts = data?.charts ?? null;
    let enriched = null;

    if (insights && typeof insights === 'object') {
        const totalClicks = Number(insights?.views_clicks?.metrics?.total_clicks);
        const totalConversions = Number(insights?.conversions?.metrics?.total_conversions);
        const averageConversionRate = Number(insights?.conversions?.metrics?.average_conversion_rate);
        const totalRevenue = Number(
            insights?.roi_tracking?.metrics?.total_revenue ??
                charts?.conversion_trend?.kpis?.total_revenue ??
                insights?.conversions?.metrics?.assisted_conversion_value
        );

        enriched = {
            totalClicks: Number.isFinite(totalClicks) ? totalClicks : null,
            totalConversions: Number.isFinite(totalConversions) ? totalConversions : null,
            averageConversionRate: Number.isFinite(averageConversionRate) ? averageConversionRate : null,
            totalRevenue: Number.isFinite(totalRevenue) ? totalRevenue : null,
            label: periodLabel,
        };
    }

    return {
        metrics,
        totals,
        enriched,
        label: periodLabel,
    };
}

/**
 * Update DOM nodes with the latest KPI values.
 */
function updateStatisticsCards(extracted, periodLabels, period) {
    const { metrics, totals, enriched, label } = extracted;

    // Prefer the enriched payload when available, falling back to legacy totals afterwards.
    const clicks = coalesceNumber(
        enriched?.totalClicks,
        pickMetric(metrics, ['clicks', 'total_clicks', 'click_total', 'count']),
        pickMetric(totals, ['clicks', 'total_clicks'])
    );
    const converted = coalesceNumber(
        enriched?.totalConversions,
        pickMetric(metrics, ['converted_clicks', 'conversions', 'converted', 'conversion_total']),
        pickMetric(totals, ['converted_clicks', 'conversions'])
    );
    const conversionRate = coalesceNumber(
        enriched?.averageConversionRate,
        pickMetric(metrics, ['conversion_rate', 'ctr', 'click_through_rate']),
        pickMetric(totals, ['conversion_rate'])
    );
    const conversionValue = coalesceNumber(
        enriched?.totalRevenue,
        pickMetric(metrics, ['conversion_value', 'total_conversion_value', 'value', 'revenue']),
        pickMetric(totals, ['conversion_value', 'total_conversion_value'])
    );

    // Target the updated data attributes first to support selector-based widget tests.
    const totalClicksNode = resolveAnalyticsStatElement('total_clicks', 'total-clicks');
    if (totalClicksNode) {
        totalClicksNode.textContent = formatNumber(clicks);
    }

    const convertedNode = resolveAnalyticsStatElement('converted_clicks', 'converted-clicks');
    if (convertedNode) {
        convertedNode.textContent = formatNumber(converted);
    }

    const conversionRateNode = resolveAnalyticsStatElement('conversion_rate', 'conversion-rate');
    if (conversionRateNode) {
        conversionRateNode.textContent = formatPercentage(conversionRate);
    }

    const conversionValueNode = resolveAnalyticsStatElement('conversion_value', 'conversion-value');
    if (conversionValueNode) {
        conversionValueNode.textContent = formatCurrency(conversionValue);
    }

    const periodLabelNode = resolveAnalyticsPeriodLabel();
    if (periodLabelNode) {
        periodLabelNode.textContent = enriched?.label ?? label ?? periodLabels[period] ?? periodLabels.day;
    }
}

/**
 * Resolve the first numeric value from the provided candidates.
 */
function coalesceNumber(...candidates) {
    for (const candidate of candidates) {
        if (typeof candidate === 'number' && !Number.isNaN(candidate)) {
            return candidate;
        }
    }

    return null;
}

/**
 * Find the DOM node for an analytics stat using the new data attributes while retaining legacy IDs.
 */
function resolveAnalyticsStatElement(statKey, fallbackId) {
    // Honour the enriched selector contract tests rely on while keeping compatibility with the original IDs.
    return document.querySelector(`[data-analytics-stat="${statKey}"]`) ?? document.getElementById(fallbackId);
}

/**
 * Locate the element used for displaying the currently active analytics period label.
 */
function resolveAnalyticsPeriodLabel() {
    // The label may be addressed by tests through the data attribute, so prefer it when available.
    return document.querySelector('[data-analytics-period="label"]') ?? document.getElementById('stats-period-label');
}

/**
 * Try multiple candidate keys and return the first numeric value.
 */
function pickMetric(source, candidates) {
    if (!source || typeof source !== 'object') {
        return null;
    }

    for (const key of candidates) {
        if (Object.prototype.hasOwnProperty.call(source, key)) {
            const value = Number(source[key]);
            if (!Number.isNaN(value)) {
                return value;
            }
        }
    }

    return null;
}

/**
 * Format numeric values with locale-aware separators.
 */
function formatNumber(value) {
    if (typeof value !== 'number' || Number.isNaN(value)) {
        return '-';
    }

    return value.toLocaleString();
}

/**
 * Format rates to two decimal places.
 */
function formatPercentage(value) {
    if (typeof value !== 'number' || Number.isNaN(value)) {
        return '-';
    }

    return `${value.toFixed(2)}%`;
}

/**
 * Prefix conversion values with the euro sign while keeping thousands separators.
 */
function formatCurrency(value) {
    if (typeof value !== 'number' || Number.isNaN(value)) {
        return '€0.00';
    }

    return `€${value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function loadClicks(page = 1) {
    const formData = new FormData(document.getElementById('filters-form'));
    const params = new URLSearchParams();
    
    for (let [key, value] of formData.entries()) {
        if (value) params.append(key, value);
    }
    params.append('page', page);
    
    fetch(`/api/campaign-clicks?${params}`)
        .then(response => response.json())
        .then(data => {
            renderClicksTable(data.data);
            renderPagination(data.meta, data.links);
        })
        .catch(error => console.error('Error loading clicks:', error));
}

function renderClicksTable(clicks) {
    const tbody = document.getElementById('clicks-table-body');
    tbody.innerHTML = '';
    
    clicks.forEach(click => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                ${click.campaign ? click.campaign.name : '-'}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                    ${click.click_type_label}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${click.device_type_label}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${click.country || '-'}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${new Date(click.clicked_at).toLocaleDateString()}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${click.is_converted ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                    ${click.is_converted ? '{{ __("campaign_clicks.yes") }}' : '{{ __("campaign_clicks.no") }}'}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <a href="/campaign-clicks/${click.id}" class="text-blue-600 hover:text-blue-900">{{ __('campaign_clicks.view') }}</a>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function renderPagination(meta, links) {
    const pagination = document.getElementById('pagination');
    if (meta.total_pages <= 1) {
        pagination.innerHTML = '';
        return;
    }
    
    let html = '<nav class="flex items-center justify-between">';
    html += '<div class="flex-1 flex justify-between sm:hidden">';
    
    if (links.prev) {
        html += `<a href="#" onclick="loadClicks(${meta.current_page - 1})" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Previous</a>`;
    }
    
    if (links.next) {
        html += `<a href="#" onclick="loadClicks(${meta.current_page + 1})" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Next</a>`;
    }
    
    html += '</div>';
    html += '<div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">';
    html += `<div><p class="text-sm text-gray-700">Showing ${meta.count} of ${meta.total} results</p></div>`;
    html += '<div>';
    
    if (links.prev) {
        html += `<a href="#" onclick="loadClicks(${meta.current_page - 1})" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">Previous</a>`;
    }
    
    if (links.next) {
        html += `<a href="#" onclick="loadClicks(${meta.current_page + 1})" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">Next</a>`;
    }
    
    html += '</div></div></nav>';
    pagination.innerHTML = html;
}
</script>
@endsection
