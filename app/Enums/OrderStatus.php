<?php

declare(strict_types=1);

namespace App\Enums;

use Illuminate\Support\Collection;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case PARTIALLY_REFUNDED = 'partially_refunded';
    case FAILED = 'failed';
    case ON_HOLD = 'on_hold';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('translations.order_status_pending'),
            self::CONFIRMED => __('translations.order_status_confirmed'),
            self::PROCESSING => __('translations.order_status_processing'),
            self::SHIPPED => __('translations.order_status_shipped'),
            self::DELIVERED => __('translations.order_status_delivered'),
            self::CANCELLED => __('translations.order_status_cancelled'),
            self::REFUNDED => __('translations.order_status_refunded'),
            self::PARTIALLY_REFUNDED => __('translations.order_status_partially_refunded'),
            self::FAILED => __('translations.order_status_failed'),
            self::ON_HOLD => __('translations.order_status_on_hold'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::PENDING => __('translations.order_status_pending_description'),
            self::CONFIRMED => __('translations.order_status_confirmed_description'),
            self::PROCESSING => __('translations.order_status_processing_description'),
            self::SHIPPED => __('translations.order_status_shipped_description'),
            self::DELIVERED => __('translations.order_status_delivered_description'),
            self::CANCELLED => __('translations.order_status_cancelled_description'),
            self::REFUNDED => __('translations.order_status_refunded_description'),
            self::PARTIALLY_REFUNDED => __('translations.order_status_partially_refunded_description'),
            self::FAILED => __('translations.order_status_failed_description'),
            self::ON_HOLD => __('translations.order_status_on_hold_description'),
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PENDING => 'heroicon-o-clock',
            self::CONFIRMED => 'heroicon-o-check-circle',
            self::PROCESSING => 'heroicon-o-cog-6-tooth',
            self::SHIPPED => 'heroicon-o-truck',
            self::DELIVERED => 'heroicon-o-check-badge',
            self::CANCELLED => 'heroicon-o-x-circle',
            self::REFUNDED => 'heroicon-o-arrow-uturn-left',
            self::PARTIALLY_REFUNDED => 'heroicon-o-arrow-uturn-left',
            self::FAILED => 'heroicon-o-exclamation-triangle',
            self::ON_HOLD => 'heroicon-o-pause-circle',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'yellow',
            self::CONFIRMED => 'blue',
            self::PROCESSING => 'indigo',
            self::SHIPPED => 'purple',
            self::DELIVERED => 'green',
            self::CANCELLED => 'red',
            self::REFUNDED => 'orange',
            self::PARTIALLY_REFUNDED => 'amber',
            self::FAILED => 'red',
            self::ON_HOLD => 'gray',
        };
    }

    public function priority(): int
    {
        return match ($this) {
            self::PENDING => 1,
            self::CONFIRMED => 2,
            self::PROCESSING => 3,
            self::SHIPPED => 4,
            self::DELIVERED => 5,
            self::ON_HOLD => 6,
            self::CANCELLED => 7,
            self::FAILED => 8,
            self::PARTIALLY_REFUNDED => 9,
            self::REFUNDED => 10,
        };
    }

    public function isActive(): bool
    {
        return match ($this) {
            self::PENDING, self::CONFIRMED, self::PROCESSING, self::SHIPPED, self::ON_HOLD => true,
            default => false,
        };
    }

    public function isCompleted(): bool
    {
        return match ($this) {
            self::DELIVERED => true,
            default => false,
        };
    }

    public function isCancelled(): bool
    {
        return match ($this) {
            self::CANCELLED, self::FAILED => true,
            default => false,
        };
    }

    public function isRefunded(): bool
    {
        return match ($this) {
            self::REFUNDED, self::PARTIALLY_REFUNDED => true,
            default => false,
        };
    }

    public function canBeCancelled(): bool
    {
        return match ($this) {
            self::PENDING, self::CONFIRMED, self::ON_HOLD => true,
            default => false,
        };
    }

    public function canBeRefunded(): bool
    {
        return match ($this) {
            self::DELIVERED, self::SHIPPED => true,
            default => false,
        };
    }

    public function canBeShipped(): bool
    {
        return match ($this) {
            self::CONFIRMED, self::PROCESSING => true,
            default => false,
        };
    }

    public function canBeDelivered(): bool
    {
        return match ($this) {
            self::SHIPPED => true,
            default => false,
        };
    }

    public function nextStatuses(): array
    {
        return match ($this) {
            self::PENDING => [self::CONFIRMED, self::CANCELLED, self::ON_HOLD],
            self::CONFIRMED => [self::PROCESSING, self::CANCELLED, self::ON_HOLD],
            self::PROCESSING => [self::SHIPPED, self::CANCELLED, self::ON_HOLD],
            self::SHIPPED => [self::DELIVERED],
            self::DELIVERED => [self::REFUNDED, self::PARTIALLY_REFUNDED],
            self::ON_HOLD => [self::CONFIRMED, self::PROCESSING, self::CANCELLED],
            default => [],
        };
    }

    public function previousStatuses(): array
    {
        return match ($this) {
            self::CONFIRMED => [self::PENDING, self::ON_HOLD],
            self::PROCESSING => [self::CONFIRMED, self::ON_HOLD],
            self::SHIPPED => [self::PROCESSING],
            self::DELIVERED => [self::SHIPPED],
            self::REFUNDED, self::PARTIALLY_REFUNDED => [self::DELIVERED],
            self::ON_HOLD => [self::PENDING, self::CONFIRMED, self::PROCESSING],
            default => [],
        };
    }

    public function estimatedDays(): ?int
    {
        return match ($this) {
            self::PENDING => 1,
            self::CONFIRMED => 2,
            self::PROCESSING => 3,
            self::SHIPPED => 5,
            self::DELIVERED => null,
            default => null,
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->sortBy('priority')
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }

    public static function optionsWithDescriptions(): array
    {
        return collect(self::cases())
            ->sortBy('priority')
            ->mapWithKeys(fn ($case) => [
                $case->value => [
                    'label' => $case->label(),
                    'description' => $case->description(),
                    'icon' => $case->icon(),
                    'color' => $case->color(),
                    'priority' => $case->priority(),
                    'is_active' => $case->isActive(),
                    'is_completed' => $case->isCompleted(),
                    'is_cancelled' => $case->isCancelled(),
                    'is_refunded' => $case->isRefunded(),
                    'can_be_cancelled' => $case->canBeCancelled(),
                    'can_be_refunded' => $case->canBeRefunded(),
                    'can_be_shipped' => $case->canBeShipped(),
                    'can_be_delivered' => $case->canBeDelivered(),
                    'next_statuses' => $case->nextStatuses(),
                    'previous_statuses' => $case->previousStatuses(),
                    'estimated_days' => $case->estimatedDays(),
                ],
            ])
            ->toArray();
    }

    public static function active(): Collection
    {
        return collect(self::cases())
            ->filter(fn ($case) => $case->isActive());
    }

    public static function completed(): Collection
    {
        return collect(self::cases())
            ->filter(fn ($case) => $case->isCompleted());
    }

    public static function cancelled(): Collection
    {
        return collect(self::cases())
            ->filter(fn ($case) => $case->isCancelled());
    }

    public static function refunded(): Collection
    {
        return collect(self::cases())
            ->filter(fn ($case) => $case->isRefunded());
    }

    public static function ordered(): Collection
    {
        return collect(self::cases())
            ->sortBy('priority');
    }

    public static function fromLabel(string $label): ?self
    {
        return collect(self::cases())
            ->first(fn ($case) => $case->label() === $label);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labels(): array
    {
        return collect(self::cases())
            ->map(fn ($case) => $case->label())
            ->toArray();
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label(),
            'description' => $this->description(),
            'icon' => $this->icon(),
            'color' => $this->color(),
            'priority' => $this->priority(),
            'is_active' => $this->isActive(),
            'is_completed' => $this->isCompleted(),
            'is_cancelled' => $this->isCancelled(),
            'is_refunded' => $this->isRefunded(),
            'can_be_cancelled' => $this->canBeCancelled(),
            'can_be_refunded' => $this->canBeRefunded(),
            'can_be_shipped' => $this->canBeShipped(),
            'can_be_delivered' => $this->canBeDelivered(),
            'next_statuses' => $this->nextStatuses(),
            'previous_statuses' => $this->previousStatuses(),
            'estimated_days' => $this->estimatedDays(),
        ];
    }
}
