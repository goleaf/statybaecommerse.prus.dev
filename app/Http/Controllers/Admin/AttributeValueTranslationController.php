<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAttributeValueTranslationRequest;
use App\Http\Requests\Admin\UpdateAttributeValueTranslationRequest;
use App\Models\AttributeValue;
use App\Models\Translations\AttributeValueTranslation;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

/**
 * AttributeValueTranslationController
 *
 * HTTP controller handling AttributeValueTranslationController related web requests, responses, and business logic with proper
validation and error handling.
 */
final class AttributeValueTranslationController extends Controller
{
    /**
     * Handle index functionality with proper error handling.
     */
    public function index(AttributeValue $attributeValue): JsonResponse
    {
        // Load translations in a deterministic order for consistent API responses.
        $translations = $attributeValue->translations()
            // Scope the query via the relationship to avoid leaking unrelated records.
            ->where('attribute_value_id', $attributeValue->getKey())
            ->orderBy('locale')
            ->get();

        $formattedTranslations = [];

        foreach ($translations as $translation) {
            $formattedTranslations[] = $this->formatTranslation($translation);
        }

        return response()->json([
            'data' => $formattedTranslations,
        ], Response::HTTP_OK);
    }

    /**
     * Handle store functionality with proper error handling.
     */
    public function store(
        StoreAttributeValueTranslationRequest $request,
        AttributeValue $attributeValue,
    ): JsonResponse {
        // Extract validated payload while preserving optional metadata arrays.
        $payload = $this->preparePayload($request->validated());

        // Persist the translation using the relationship to guarantee ownership linkage.
        $translation = $attributeValue->translations()->create($payload);

        return response()->json([
            'message'     => __('admin.messages.translation_created'),
            'translation' => $this->formatTranslation($translation),
        ], Response::HTTP_CREATED);
    }

    /**
     * Handle update functionality with proper error handling.
     */
    public function update(
        UpdateAttributeValueTranslationRequest $request,
        AttributeValue $attributeValue,
        int|string $translation,
    ): JsonResponse {
        // Resolve the translation using the attribute value relationship to ensure route IDs cannot be tampered with.
        $resolvedTranslation = $this->resolveTranslation($attributeValue, $translation);

        // Prepare the validated data and remove any null keys so the update is idempotent.
        $payload = $this->preparePayload($request->validated());

        $resolvedTranslation->fill($payload);
        $resolvedTranslation->save();

        return response()->json([
            'message'     => __('admin.messages.translation_updated'),
            'translation' => $this->formatTranslation($resolvedTranslation->refresh()),
        ], Response::HTTP_OK);
    }

    /**
     * Handle destroy functionality with proper error handling.
     */
    public function destroy(AttributeValue $attributeValue, int|string $translation): JsonResponse
    {
        $resolvedTranslation = $this->resolveTranslation($attributeValue, $translation);

        $resolvedTranslation->delete();

        return response()->json([
            'message' => __('admin.messages.translation_deleted'),
        ], Response::HTTP_OK);
    }

    /**
     * Handle formatTranslation functionality with proper error handling.
     *
     * @return array<string, mixed>
     */
    private function formatTranslation(Model $translation): array
    {
        if (! $translation instanceof AttributeValueTranslation) {
            throw new UnexpectedValueException('Unexpected translation model instance.');
        }

        $createdAt = $translation->created_at;
        $updatedAt = $translation->updated_at;

        return [
            'id'          => $translation->getKey(),
            'locale'      => $translation->locale,
            'value'       => $translation->value,
            'description' => $translation->description,
            'meta_data'   => $translation->meta_data,
            'created_at'  => $createdAt instanceof CarbonInterface ? $createdAt->toISOString() : null,
            'updated_at'  => $updatedAt instanceof CarbonInterface ? $updatedAt->toISOString() : null,
        ];
    }

    /**
     * Handle resolveTranslation functionality with proper error handling.
     */
    private function resolveTranslation(
        AttributeValue $attributeValue,
        int|string $translation,
    ): AttributeValueTranslation {
        $translationId = (int) $translation;

        $resolved = $attributeValue->translations()
            // Restrict lookup to the current attribute value to enforce ownership boundaries.
            ->where('attribute_value_id', $attributeValue->getKey())
            ->find($translationId);

        if ($resolved === null) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return $resolved;
    }

    /**
     * Handle preparePayload functionality with proper error handling.
     *
     * @param  array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function preparePayload(array $payload): array
    {
        // Normalize optional keys so that null values remove the column while empty arrays remain intact.
        /** @var array<string, mixed> $filtered */
        $filtered = Arr::where(
            $payload,
            static fn ($value, string $key): bool => $value !== null || $key === 'meta_data',
        );

        return $filtered;
    }
}
