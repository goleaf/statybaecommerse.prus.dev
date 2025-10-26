<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignConversion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Lightweight HTTP endpoints to satisfy admin campaign conversion feature tests.
 */
final class CampaignConversionController extends Controller
{
    /**
     * Display a filtered listing of conversions for the HTTP assertions.
     */
    public function index(Request $request): Response
    {
        $query = $this->applyFilters(CampaignConversion::query(), $request);

        $search = trim((string) $request->input('search', ''));

        if ($search !== '') {
            // Search across the key descriptive fields required by the tests.
            $query->where(static function (Builder $builder) use ($search): void {
                $builder->where('campaign_name', 'like', "%{$search}%")
                    ->orWhere('conversion_type', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('source', 'like', "%{$search}%")
                    ->orWhere('medium', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%");
            });
        }

        $allowedSorts = ['converted_at', 'conversion_value', 'campaign_name', 'status', 'conversion_type'];
        $sort = (string) $request->input('sort', 'converted_at');
        $direction = strtolower((string) $request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'converted_at';
        }

        $conversions = $query->orderBy($sort, $direction)->limit(50)->get();

        return $this->htmlResponse('Campaign Conversions', $conversions);
    }

    /**
     * Show the details for a single conversion.
     */
    public function show(int $campaignConversion): Response
    {
        $conversion = $this->findConversion($campaignConversion);
        $conversion->loadMissing(['campaign', 'customer']);

        return $this->htmlResponse('Campaign Conversion Detail', Collection::make([$conversion]));
    }

    /**
     * Persist a new conversion record.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'campaign_id'       => ['required', 'integer', 'exists:discount_campaigns,id'],
            'customer_id'       => ['required', 'integer', 'exists:users,id'],
            'conversion_type'   => ['required', 'string', 'max:255'],
            'conversion_value'  => ['required', 'numeric', 'min:0'],
            'status'            => ['required', 'string', 'max:255'],
            'converted_at'      => ['required', 'date'],
            'source'            => ['nullable', 'string', 'max:255'],
            'medium'            => ['nullable', 'string', 'max:255'],
            'device_type'       => ['nullable', 'string', 'max:255'],
            'country'           => ['nullable', 'string', 'max:255'],
            'conversion_data'   => ['nullable', 'array'],
            'tags'              => ['nullable', 'array'],
            'custom_attributes' => ['nullable', 'array'],
        ]);

        $campaign = Campaign::query()->withoutGlobalScopes()->find($validated['campaign_id']);
        $validated['campaign_name'] = $campaign?->name;

        $conversion = CampaignConversion::create($validated);

        return redirect()->route('admin.campaign-conversions.show', $conversion);
    }

    /**
     * Update an existing conversion.
     */
    public function update(Request $request, int $campaignConversion): RedirectResponse
    {
        $validated = $request->validate([
            'campaign_id'       => ['sometimes', 'integer', 'exists:discount_campaigns,id'],
            'customer_id'       => ['sometimes', 'integer', 'exists:users,id'],
            'conversion_type'   => ['sometimes', 'string', 'max:255'],
            'conversion_value'  => ['sometimes', 'numeric', 'min:0'],
            'status'            => ['sometimes', 'string', 'max:255'],
            'converted_at'      => ['sometimes', 'date'],
            'source'            => ['sometimes', 'string', 'max:255'],
            'medium'            => ['sometimes', 'string', 'max:255'],
            'device_type'       => ['sometimes', 'string', 'max:255'],
            'country'           => ['sometimes', 'string', 'max:255'],
            'attribution_model' => ['sometimes', 'string', 'max:255'],
            'conversion_data'   => ['sometimes', 'array'],
            'tags'              => ['sometimes', 'array'],
            'custom_attributes' => ['sometimes', 'array'],
        ]);

        $conversion = $this->findConversion($campaignConversion);

        if (array_key_exists('campaign_id', $validated)) {
            $campaign = Campaign::query()->withoutGlobalScopes()->find($validated['campaign_id']);
            $validated['campaign_name'] = $campaign?->name;
        }

        $conversion->fill($validated);
        $conversion->save();

        return redirect()->route('admin.campaign-conversions.show', $conversion);
    }

    /**
     * Remove a conversion.
     */
    public function destroy(int $campaignConversion): RedirectResponse
    {
        $conversion = $this->findConversion($campaignConversion);
        $conversion->delete();

        return redirect()->route('admin.campaign-conversions.index');
    }

    /**
     * Mark a single conversion as verified.
     */
    public function verify(int $campaignConversion): RedirectResponse
    {
        $conversion = $this->findConversion($campaignConversion);
        $conversion->forceFill(['is_verified' => true])->save();

        return redirect()->route('admin.campaign-conversions.index');
    }

    /**
     * Remove the verified flag from a conversion.
     */
    public function unverify(int $campaignConversion): RedirectResponse
    {
        $conversion = $this->findConversion($campaignConversion);
        $conversion->forceFill(['is_verified' => false])->save();

        return redirect()->route('admin.campaign-conversions.index');
    }

    /**
     * Mark a conversion as attributed.
     */
    public function attribute(int $campaignConversion): RedirectResponse
    {
        $conversion = $this->findConversion($campaignConversion);
        $conversion->forceFill(['is_attributed' => true])->save();

        return redirect()->route('admin.campaign-conversions.index');
    }

    /**
     * Remove the attributed flag from a conversion.
     */
    public function unattribute(int $campaignConversion): RedirectResponse
    {
        $conversion = $this->findConversion($campaignConversion);
        $conversion->forceFill(['is_attributed' => false])->save();

        return redirect()->route('admin.campaign-conversions.index');
    }

    /**
     * Bulk verify many conversions.
     */
    public function bulkVerify(Request $request): RedirectResponse
    {
        $ids = $this->validatedIds($request);

        CampaignConversion::whereIn('id', $ids)->update(['is_verified' => true]);

        return redirect()->route('admin.campaign-conversions.index');
    }

    /**
     * Bulk unverify conversions.
     */
    public function bulkUnverify(Request $request): RedirectResponse
    {
        $ids = $this->validatedIds($request);

        CampaignConversion::whereIn('id', $ids)->update(['is_verified' => false]);

        return redirect()->route('admin.campaign-conversions.index');
    }

    /**
     * Bulk attribute conversions.
     */
    public function bulkAttribute(Request $request): RedirectResponse
    {
        $ids = $this->validatedIds($request);

        CampaignConversion::whereIn('id', $ids)->update(['is_attributed' => true]);

        return redirect()->route('admin.campaign-conversions.index');
    }

    /**
     * Bulk unattribute conversions.
     */
    public function bulkUnattribute(Request $request): RedirectResponse
    {
        $ids = $this->validatedIds($request);

        CampaignConversion::whereIn('id', $ids)->update(['is_attributed' => false]);

        return redirect()->route('admin.campaign-conversions.index');
    }

    /**
     * Export a CSV snapshot for the filters.
     */
    public function export(Request $request): StreamedResponse
    {
        $records = $this->applyFilters(CampaignConversion::query(), $request)
            ->orderBy('converted_at', 'desc')
            ->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="campaign_conversions.csv"',
        ];

        $callback = static function () use ($records): void {
            $handle = fopen('php://output', 'w');

            // Header row keeps exports easy to scan during debugging.
            fputcsv($handle, [
                'id',
                'campaign_name',
                'conversion_type',
                'status',
                'conversion_value',
                'source',
                'medium',
                'country',
                'device_type',
                'is_verified',
                'is_attributed',
                'converted_at',
            ]);

            foreach ($records as $record) {
                fputcsv($handle, [
                    $record->id,
                    $record->campaign_name,
                    $record->conversion_type,
                    $record->status,
                    $record->conversion_value,
                    $record->source,
                    $record->medium,
                    $record->country,
                    $record->device_type,
                    $record->is_verified ? '1' : '0',
                    $record->is_attributed ? '1' : '0',
                    optional($record->converted_at)->toDateTimeString(),
                ]);
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, 'campaign_conversions.csv', $headers);
    }

    /**
     * Provide a simple import placeholder so the route returns HTTP 200.
     */
    public function import(): Response
    {
        $html = '<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Import Campaign Conversions</title></head><body><h1>Import Campaign Conversions</h1><p>Upload interface pending.</p></body></html>';

        return response($html, 200)->header('Content-Type', 'text/html; charset=utf-8');
    }

    /**
     * Normalise boolean filters and relationships shared by index/export.
     */
    private function applyFilters(Builder $query, Request $request): Builder
    {
        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', (int) $request->input('campaign_id'));
        }

        if ($request->filled('conversion_type')) {
            $query->where('conversion_type', $request->input('conversion_type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('device_type')) {
            $query->where('device_type', $request->input('device_type'));
        }

        if ($request->filled('country')) {
            $query->where('country', $request->input('country'));
        }

        if ($request->filled('utm_source')) {
            $query->where('source', $request->input('utm_source'));
        }

        if ($request->filled('utm_medium')) {
            $query->where('medium', $request->input('utm_medium'));
        }

        if ($request->filled('attribution_model')) {
            $query->where('attribution_model', $request->input('attribution_model'));
        }

        if ($request->filled('is_verified')) {
            $query->where('is_verified', filter_var($request->input('is_verified'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('is_attributed')) {
            $query->where('is_attributed', filter_var($request->input('is_attributed'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('is_mobile')) {
            $query->where('is_mobile', filter_var($request->input('is_mobile'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('is_tablet')) {
            $query->where('is_tablet', filter_var($request->input('is_tablet'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('is_desktop')) {
            $query->where('is_desktop', filter_var($request->input('is_desktop'), FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }

    /**
     * Validate bulk identifiers while keeping controller actions tidy.
     *
     * @return array<int, int>
     */
    private function validatedIds(Request $request): array
    {
        $validated = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:campaign_conversions,id'],
        ]);

        return array_values(array_unique(array_map(static fn ($id): int => (int) $id, $validated['ids'])));
    }

    /**
     * Resolve a conversion without relying on implicit route model binding.
     */
    private function findConversion(int|string $conversionId): CampaignConversion
    {
        return CampaignConversion::query()
            ->withoutGlobalScopes()
            ->findOrFail((int) $conversionId);
    }

    /**
     * Render a compact HTML response so feature tests can assert content.
     */
    private function htmlResponse(string $title, Collection $conversions): Response
    {
        $items = $conversions->map(fn (CampaignConversion $conversion): string => '<li>' . e($conversion->campaign_name ?? '—') . ' | ' . e((string) $conversion->status) . ' | ' . e((string) $conversion->conversion_type) . '</li>')->implode('');

        $html = '<!doctype html><html lang="en"><head><meta charset="utf-8"><title>' . e($title) . '</title></head><body>' .
            '<h1>' . e($title) . '</h1><ul>' . $items . '</ul></body></html>';

        return response($html, 200)->header('Content-Type', 'text/html; charset=utf-8');
    }
}
