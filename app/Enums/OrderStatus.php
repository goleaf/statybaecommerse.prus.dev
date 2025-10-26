<?php

declare(strict_types=1);

namespace App\Enums;

use App\Contracts\EnumInterface;
use Illuminate\Support\Collection;

/**
 * OrderStatus
 *
 * Enumeration describing the lifecycle states an order can occupy.
 */
enum OrderStatus: string implements EnumInterface
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case RETURNED = 'returned';

    private const LABEL_DEFAULTS = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'refunded' => 'Refunded',
        'returned' => 'Returned',
    ];

    private const DESCRIPTION_DEFAULTS = [
        'pending' => 'Order received and awaiting confirmation.',
        'confirmed' => 'Order confirmed and preparing for processing.',
        'processing' => 'Order is being prepared for shipment.',
        'shipped' => 'Order has left the warehouse and is in transit.',
        'delivered' => 'Order successfully delivered to the customer.',
        'completed' => 'Order completed including any post-delivery checks.',
        'cancelled' => 'Order cancelled before fulfillment.',
        'refunded' => 'Order refunded to the customer.',
        'returned' => 'Order returned by the customer.',
    ];

    private const ICON_MAP = [
        'pending' => 'heroicon-o-clock',
        'confirmed' => 'heroicon-o-check-circle',
        'processing' => 'heroicon-o-cog-6-tooth',
        'shipped' => 'heroicon-o-truck',
        'delivered' => 'heroicon-o-check-badge',
        'completed' => 'heroicon-o-flag',
        'cancelled' => 'heroicon-o-x-circle',
        'refunded' => 'heroicon-o-arrow-uturn-left',
        'returned' => 'heroicon-o-arrow-uturn-right',
    ];

    private const COLOR_MAP = [
        'pending' => 'warning',
        'confirmed' => 'info',
        'processing' => 'primary',
        'shipped' => 'success',
        'delivered' => 'success',
        'completed' => 'success',
        'cancelled' => 'danger',
        'refunded' => 'secondary',
        'returned' => 'warning',
    ];

    private const PRIORITY_MAP = [
        'pending' => 1,
        'confirmed' => 2,
        'processing' => 3,
        'shipped' => 4,
        'delivered' => 5,
        'completed' => 6,
        'cancelled' => 7,
        'refunded' => 8,
        'returned' => 9,
    ];

    /**
     * Translate a string key with a graceful fallback to the provided default.
     */
    private static function translate(string $key, string $default): string
    {
        $translation = __($key);

        if (! is_string($translation) || $translation === $key) {
            return $default;
        }

        return $translation;
    }

    public function label(): string
    {
        // Combine translations with sensible defaults to keep admin views human readable.
        return self::translate('enums.order_status.' . $this->value, self::LABEL_DEFAULTS[$this->value]);
    }

    /**
     * Retain backwards compatibility for legacy usages referencing getLabel().
     */
    public function getLabel(): string
    {
        return $this->label();
    }

    public function description(): string
    {
        return self::translate('translations.order_status_' . $this->value . '_description', self::DESCRIPTION_DEFAULTS[$this->value]);
    }

    public function icon(): string
    {
        return self::ICON_MAP[$this->value];
    }

    /**
     * Preserve older helper expectations that still call getIcon().
     */
    public function getIcon(): string
    {
        return $this->icon();
    }

    public function color(): string
    {
        return self::COLOR_MAP[$this->value];
    }

    /**
     * Provide a compatibility bridge for historical getColor() usages.
     */
    public function getColor(): string
    {
        return $this->color();
    }

    public function priority(): int
    {
        return self::PRIORITY_MAP[$this->value];
    }

    public function toArray(): array
    {
        // Supply a consistent metadata payload for UI components and API responses.
        return [
            'value' => $this->value,
            'label' => $this->label(),
            'description' => $this->description(),
            'icon' => $this->icon(),
            'color' => $this->color(),
            'priority' => $this->priority(),
        ];
    }

    public static function options(): array
    {
        return self::ordered()
            ->mapWithKeys(fn (self $case): array => [$case->value => $case->label()])
            ->toArray();
    }

    /**
     * Backwards compatibility wrapper for legacy getOptions() calls.
     */
    public static function getOptions(): array
    {
        return self::options();
    }

    public static function optionsWithDescriptions(): array
    {
        return self::ordered()
            ->mapWithKeys(fn (self $case): array => [
                $case->value => [
                    'label' => $case->label(),
                    'description' => $case->description(),
                    'icon' => $case->icon(),
                    'color' => $case->color(),
                    'priority' => $case->priority(),
                ],
            ])
            ->toArray();
    }

    /**
     * Provide a legacy alias for consumers still expecting getOptionsWithDescriptions().
     */
    public static function getOptionsWithDescriptions(): array
    {
        return self::optionsWithDescriptions();
    }

    public static function collection(): Collection
    {
        return collect(self::cases());
    }

    public static function ordered(): Collection
    {
        return self::collection()
            ->sortBy(fn (self $case): int => $case->priority())
            ->values();
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labels(): array
    {
        return self::collection()
            ->map(fn (self $case): string => $case->label())
            ->toArray();
    }

    public static function fromLabel(string $label): ?self
    {
        return self::collection()->first(fn (self $case): bool => $case->label() === $label);
    }
}
