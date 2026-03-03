<?php

declare(strict_types=1);

namespace App\Support\Products;

use App\Models\Product;
use Illuminate\Validation\ValidationException;

final class ProductPublicationGuard
{
    /**
     * @param array<string, mixed> $data
     *
     * @throws ValidationException
     */
    public static function ensureCreateIsAllowed(array $data): void
    {
        if (! self::isPublishingStatus($data['status'] ?? null)) {
            return;
        }

        throw ValidationException::withMessages([
            'status' => __('admin.suppliers.publish_on_create_not_allowed'),
        ]);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws ValidationException
     */
    public static function ensureEditPublishability(array $data, Product $product): void
    {
        if (! self::isPublishingStatus($data['status'] ?? null)) {
            return;
        }

        $selectedSupplierIds = self::normalizeSupplierIds($data['suppliers'] ?? null);

        if ($selectedSupplierIds !== []) {
            return;
        }

        if ($product->hasSuppliers()) {
            return;
        }

        throw ValidationException::withMessages([
            'suppliers' => __('admin.suppliers.publish_requires_supplier'),
        ]);
    }

    public static function isPublishingStatus(mixed $status): bool
    {
        if (! is_string($status)) {
            return false;
        }

        return in_array($status, ['published', 'active'], true);
    }

    /**
     * @return array<int, int>
     */
    public static function normalizeSupplierIds(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->filter(static fn (mixed $id): bool => is_numeric($id))
            ->map(static fn (mixed $id): int => (int) $id)
            ->filter(static fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }
}
