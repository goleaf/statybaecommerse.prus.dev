<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use function collect;
use function in_array;
use function strtolower;
use function trim;

final class LocationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Location::query()
            ->withoutGlobalScopes()
            ->with('country');

        if ($type = trim((string) $request->query('type', ''))) {
            $query->where('type', $type);
        }

        if ($request->filled('country_code')) {
            $query->where('country_code', $request->query('country_code'));
        }

        if ($request->filled('country_id')) {
            $country = Country::withoutGlobalScopes()->find((int) $request->query('country_id'));
            if ($country !== null) {
                $query->where('country_code', $country->cca2 ?? $country->code);
            }
        }

        if ($request->filled('is_enabled')) {
            $query->where('is_enabled', $request->boolean('is_enabled'));
        }

        if ($request->filled('is_default')) {
            $query->where('is_default', $request->boolean('is_default'));
        }

        if ($request->filled('has_coordinates')) {
            $value = strtolower((string) $request->query('has_coordinates'));
            if (in_array($value, ['yes', '1', 'true'], true)) {
                $query->whereNotNull('latitude')->whereNotNull('longitude');
            } elseif (in_array($value, ['no', '0', 'false'], true)) {
                $query->where(function ($builder): void {
                    $builder->whereNull('latitude')->orWhereNull('longitude');
                });
            }
        }

        if ($request->filled('has_opening_hours')) {
            $value = strtolower((string) $request->query('has_opening_hours'));
            if (in_array($value, ['yes', '1', 'true'], true)) {
                $query->whereNotNull('opening_hours');
            } elseif (in_array($value, ['no', '0', 'false'], true)) {
                $query->whereNull('opening_hours');
            }
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            if ($search !== '') {
                $query->where(function ($builder) use ($search): void {
                    $builder
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('address_line_1', 'like', "%{$search}%");
                });
            }
        }

        $perPage = (int) $request->integer('per_page', 50);
        $perPage = max(1, min($perPage, 200)); // Cap the per-page value to avoid excessive payloads.

        $locations = $query
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage)
            ->through(static function (Location $location): array {
                return [
                    'id' => $location->id,
                    'name' => $location->name,
                    'code' => $location->code,
                    'type' => $location->type,
                    'country_code' => $location->country_code,
                    'country_name' => $location->country?->name,
                    'is_enabled' => (bool) $location->is_enabled,
                    'is_default' => (bool) $location->is_default,
                    'has_coordinates' => $location->hasCoordinates(),
                    'has_opening_hours' => $location->hasOpeningHours(),
                ];
            });

        return response()->json($locations);
    }

    public function create(): \Illuminate\Http\Response
    {
        return response('Create Location Page');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        Location::withoutGlobalScopes()->create($data);

        return redirect()->route('admin.locations.index');
    }

    public function show(int $location): JsonResponse
    {
        \Log::debug('show route', ['location_id' => $location]);
        $record = Location::withoutGlobalScopes()
            ->with('country')
            ->findOrFail($location);

        return response()->json([
            'data' => [
                'id' => $record->id,
                'name' => $record->name,
                'code' => $record->code,
                'type' => $record->type,
                'country_code' => $record->country_code,
                'country_name' => $record->country?->name,
                'city' => $record->city,
                'full_address' => $record->full_address,
                'is_enabled' => (bool) $record->is_enabled,
                'is_default' => (bool) $record->is_default,
            ],
        ]);
    }

    public function edit(int $location): \Illuminate\Http\Response
    {
        $record = Location::withoutGlobalScopes()->findOrFail($location);

        return response("Edit Location: {$record->name}");
    }

    public function update(Request $request, int $location): RedirectResponse
    {
        $record = Location::withoutGlobalScopes()->findOrFail($location);

        $data = $this->validatedData($request, $record);

        $record->fill($data)->save();

        return redirect()->route('admin.locations.index');
    }

    public function destroy(int $location): RedirectResponse
    {
        $record = Location::withoutGlobalScopes()->findOrFail($location);
        $record->forceDelete();

        return redirect()->route('admin.locations.index');
    }

    public function bulkActions(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'action'  => ['required', Rule::in(['enable', 'disable'])],
            'records' => ['required', 'array', 'min:1'],
            'records.*' => ['integer', 'exists:locations,id'],
        ]);

        $isEnabled = $data['action'] === 'enable';

        Location::withoutGlobalScopes()
            ->whereIn('id', $data['records'])
            ->update(['is_enabled' => $isEnabled]);

        return redirect()->route('admin.locations.index');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:locations,id'],
            'items.*.sort_order' => ['required', 'integer'],
        ]);

        foreach ($data['items'] as $item) {
            Location::withoutGlobalScopes()
                ->where('id', $item['id'])
                ->update(['sort_order' => $item['sort_order']]);
        }

        return redirect()->route('admin.locations.index');
    }

    private function validatedData(Request $request, ?Location $location = null): array
    {
        $locationId = $location?->getKey();

        $rules = [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('locations', 'code')
                    ->ignore($locationId)
                    ->whereNull('deleted_at'),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string', 'max:50'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country_code' => ['nullable', 'string', 'max:3'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'opening_hours' => ['nullable', 'array'],
            'opening_hours.*.day' => ['required_with:opening_hours', 'string'],
            'opening_hours.*.open_time' => ['nullable', 'string'],
            'opening_hours.*.close_time' => ['nullable', 'string'],
            'opening_hours.*.is_closed' => ['nullable', 'boolean'],
            'contact_info' => ['nullable', 'array'],
            'sort_order' => ['nullable', 'integer'],
            'is_enabled' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ];

        $validated = $request->validate($rules);

        if ($request->filled('country_id') && empty($validated['country_code'])) {
            $country = Country::withoutGlobalScopes()->find((int) $request->input('country_id'));
            if ($country !== null) {
                $validated['country_code'] = $country->cca2 ?? $country->code;
            }
        }

        if ($location !== null && ! array_key_exists('sort_order', $validated)) {
            $validated['sort_order'] = $location->sort_order;
        }

        $validated['is_enabled'] = $request->has('is_enabled')
            ? $request->boolean('is_enabled')
            : ($location?->is_enabled ?? false);

        $validated['is_default'] = $request->has('is_default')
            ? $request->boolean('is_default')
            : ($location?->is_default ?? false);

        if (array_key_exists('opening_hours', $validated)) {
            $validated['opening_hours'] = collect($validated['opening_hours'])
                ->map(function (array $day): array {
                    $day['day'] = strtolower((string) ($day['day'] ?? ''));
                    if (array_key_exists('is_closed', $day)) {
                        $day['is_closed'] = (bool) $day['is_closed'];
                    }

                    return $day;
                })
                ->values()
                ->all();
        }

        return $validated;
    }
}
