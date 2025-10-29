<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EnumValue;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

/**
 * Handle simplified REST-style endpoints for EnumValue management so tests can
 * interact with Filament-backed data using HTTP verbs.
 */
final class EnumValueController extends Controller
{
    /**
     * Persist a new enum value record using familiar form-style validation.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate the incoming payload and normalise metadata into an array structure.
        $validated = $this->validatePayload($request);

        // Create the enum value so tests can assert against the database state.
        /** @var array<string, mixed> $validated */
        EnumValue::create($validated);

        return redirect('/admin/enum-values');
    }

    /**
     * Update an existing enum value with the provided attributes.
     */
    public function update(Request $request, EnumValue $enumValue): RedirectResponse
    {
        // Reuse the validation logic while instructing the unique rule to ignore the record.
        $validated = $this->validatePayload($request, $enumValue);

        // Apply the validated attributes and persist the changes.
        /** @var array<string, mixed> $validated */
        $enumValue->fill($validated)->save();

        return redirect('/admin/enum-values');
    }

    /**
     * Bulk activate the provided enum value identifiers.
     */
    public function bulkActivate(Request $request): RedirectResponse
    {
        // Collect the identifiers and discard any non-numeric entries for safety.
        $ids = $this->extractIdentifiers($request);

        if ($ids->isNotEmpty()) {
            // Mark all selected records as active in a single query for efficiency.
            EnumValue::query()->whereIn('id', $ids)->update(['is_active' => true]);
        }

        return redirect('/admin/enum-values');
    }

    /**
     * Bulk deactivate the provided enum value identifiers.
     */
    public function bulkDeactivate(Request $request): RedirectResponse
    {
        // Collect the identifiers and discard any non-numeric entries for safety.
        $ids = $this->extractIdentifiers($request);

        if ($ids->isNotEmpty()) {
            // Mark all selected records as inactive in a single query for efficiency.
            EnumValue::query()->whereIn('id', $ids)->update(['is_active' => false]);
        }

        return redirect('/admin/enum-values');
    }

    /**
     * Mark the supplied enum value as the default option for its type.
     */
    public function setDefault(EnumValue $enumValue): RedirectResponse
    {
        // Delegate to the model helper so sibling records are automatically reset.
        $enumValue->setAsDefault();

        return redirect('/admin/enum-values');
    }

    /**
     * Validate and normalise the request payload for create/update operations.
     *
     * @return array<string, mixed>
     */
    private function validatePayload(Request $request, ?EnumValue $enumValue = null): array
    {
        $typeForRule = $request->string('type')->toString();

        // Build the unique rule so keys remain unique per type while allowing updates.
        $keyRule = Rule::unique('enum_values', 'key')
            ->where(fn (QueryBuilder $query): QueryBuilder => $query->where('type', $typeForRule));

        if ($enumValue !== null) {
            // Ignore the current record when checking uniqueness during updates.
            $keyRule->ignore($enumValue->getKey());
        }

        $typeRules = [$enumValue === null ? 'required' : 'sometimes', 'string', 'max:255'];
        $keyRules = [$enumValue === null ? 'required' : 'sometimes', 'string', 'max:255', $keyRule];
        $valueRules = [$enumValue === null ? 'required' : 'sometimes', 'string', 'max:255'];

        /** @var array<string, mixed> $validated */
        $validated = $request->validate([
            'type'        => $typeRules,
            'key'         => $keyRules,
            'value'       => $valueRules,
            'name'        => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['sometimes', 'boolean'],
            'is_default'  => ['sometimes', 'boolean'],
            'metadata'    => ['sometimes', 'array'],
        ]);

        if ($enumValue !== null) {
            // Preserve immutable columns when they are not provided in the update payload.
            $validated['type'] ??= $enumValue->type;
            $validated['key'] ??= $enumValue->key;
            $validated['value'] ??= $enumValue->value;
        }

        // Ensure boolean and metadata defaults mimic the Filament form behaviour.
        $validated['is_active'] = (bool) Arr::get($validated, 'is_active', true);
        $validated['is_default'] = (bool) Arr::get($validated, 'is_default', false);
        $validated['metadata'] = Arr::get($validated, 'metadata', []);

        return $validated;
    }

    /**
     * Extract a clean collection of numeric identifiers from the incoming request.
     *
     * @return Collection<int, int>
     */
    private function extractIdentifiers(Request $request): Collection
    {
        /** @var array<int|string, mixed> $records */
        $records = (array) $request->input('records', []);

        /** @var Collection<int, int> $collection */
        $collection = collect($records)
            ->filter(static fn ($id): bool => is_numeric($id))
            ->map(static fn ($id): int => (int) $id)
            ->values();

        return $collection;
    }
}
