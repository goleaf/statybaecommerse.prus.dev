<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EnumValue;
use Carbon\CarbonInterface;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

/**
 * OrderStatusController
 *
 * HTTP controller handling Order Status related web requests, responses, and business logic with proper validation and error handling.
 */
final class OrderStatusController extends Controller
{
    /**
     * Display a listing of the order status enum values.
     */
    public function index(): JsonResponse
    {
        // Retrieve all order status enum values ordered by their configured sort order and localized name for deterministic output.
        $statuses = EnumValue::query()
            ->byType('order_status')
            ->ordered()
            ->get()
            ->map($this->transformEnumValue(...))
            ->values();

        // Return the transformed data payload in a JSON API style response.
        return response()->json([
            'data' => $statuses,
        ]);
    }

    /**
     * Store a newly created order status enum value in storage.
     */
    public function store(Request $request): JsonResponse
    {
        // Validate the incoming payload while ensuring key uniqueness within the order_status namespace.
        $validated = $this->validatePayload($request);

        // Persist the enum value while forcing the type to remain scoped to order statuses.
        $enumValue = new EnumValue;

        /** @var array<string, mixed> $attributes */
        $attributes = array_merge($validated, [
            'type'       => 'order_status',
            'is_active'  => $validated['is_active'] ?? true,
            'is_default' => $validated['is_default'] ?? false,
        ]);

        $enumValue->fill($attributes);
        $enumValue->save();

        // When flagged as default ensure other enum values of the same type are reset accordingly.
        if ($enumValue->is_default) {
            $enumValue->setAsDefault();
            $enumValue->refresh();
        }

        // Respond with the freshly saved resource and a 201 status code.
        return response()->json([
            'data' => $this->transformEnumValue($enumValue),
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified order status enum value.
     */
    public function show(EnumValue $orderStatus): JsonResponse
    {
        // Guard against incorrectly bound enum values by validating the type discriminator.
        $this->ensureOrderStatus($orderStatus);

        // Return a single transformed representation for the requested enum value.
        return response()->json([
            'data' => $this->transformEnumValue($orderStatus),
        ]);
    }

    /**
     * Update the specified order status enum value in storage.
     */
    public function update(Request $request, EnumValue $orderStatus): JsonResponse
    {
        // Guard against incorrectly bound enum values by validating the type discriminator.
        $this->ensureOrderStatus($orderStatus);

        // Validate the incoming payload while honoring unique constraints for updates.
        $validated = $this->validatePayload($request, $orderStatus->id, true);

        // Apply the validated fields while locking the enum type to order_status entries only.
        /** @var array<string, mixed> $attributes */
        $attributes = array_merge($validated, [
            'type' => 'order_status',
        ]);

        $orderStatus->fill($attributes);
        $orderStatus->save();

        // When toggled as the default ensure all sibling enum values are updated.
        if (($validated['is_default'] ?? false) === true) {
            $orderStatus->setAsDefault();
            $orderStatus->refresh();
        }

        // Return the updated resource payload for client consumption.
        return response()->json([
            'data' => $this->transformEnumValue($orderStatus),
        ]);
    }

    /**
     * Remove the specified order status enum value from storage.
     */
    public function destroy(EnumValue $orderStatus): Response
    {
        // Guard against incorrectly bound enum values by validating the type discriminator.
        $this->ensureOrderStatus($orderStatus);

        // Delete the enum value while leaving the database connection to handle cascading effects.
        $orderStatus->delete();

        // Respond with a 204 (No Content) status to signal successful deletion.
        return response()->noContent();
    }

    /**
     * Validate the request payload shared across create and update operations.
     *
     * @return array<string, mixed>
     */
    private function validatePayload(Request $request, ?int $ignoreId = null, bool $isUpdate = false): array
    {
        // Build validation rules with conditional requirements for update operations.
        $keyRules = [
            $isUpdate ? 'sometimes' : 'required',
            'string',
            'max:100',
            Rule::unique('enum_values', 'key')
                ->where(fn (QueryBuilder $query) => $query->where('type', 'order_status'))
                ->ignore($ignoreId),
        ];

        $rules = [
            'key'         => $keyRules,
            'value'       => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'name'        => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['sometimes', 'boolean'],
            'is_default'  => ['sometimes', 'boolean'],
            'metadata'    => ['nullable', 'array'],
        ];

        // Execute validation and return the sanitized payload.
        /** @var array<string, mixed> $validated */
        $validated = $request->validate($rules);

        return $validated;
    }

    /**
     * Normalize an enum value model into an easily consumable array structure.
     *
     * @return array<string, mixed>
     */
    private function transformEnumValue(EnumValue $enumValue): array
    {
        // Assemble a deterministic array representation of the enum value including auditing metadata.
        $createdAt = $enumValue->getAttribute('created_at');
        $updatedAt = $enumValue->getAttribute('updated_at');

        $createdAtString = $createdAt instanceof CarbonInterface ? $createdAt->toAtomString() : null;
        $updatedAtString = $updatedAt instanceof CarbonInterface ? $updatedAt->toAtomString() : null;

        return [
            'id'          => $enumValue->id,
            'key'         => $enumValue->key,
            'value'       => $enumValue->value,
            'name'        => $enumValue->name,
            'description' => $enumValue->description,
            'sort_order'  => $enumValue->sort_order,
            'is_active'   => $enumValue->is_active,
            'is_default'  => $enumValue->is_default,
            'metadata'    => $enumValue->metadata ?? [],
            'usage_count' => $enumValue->getUsageCount(),
            'created_at'  => $createdAtString,
            'updated_at'  => $updatedAtString,
        ];
    }

    /**
     * Ensure the provided enum value belongs to the order_status namespace.
     */
    private function ensureOrderStatus(EnumValue $enumValue): void
    {
        // Abort with a 404 if a non-order status enum value was bound by the router.
        if ($enumValue->type !== 'order_status') {
            abort(Response::HTTP_NOT_FOUND);
        }
    }
}
