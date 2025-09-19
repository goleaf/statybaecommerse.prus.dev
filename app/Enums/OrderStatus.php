<?php declare(strict_types=1);

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case RETURNED = 'returned';

    public function getLabel(): string
    {
        return match($this) {
            self::PENDING => __('enums.order_status.pending'),
            self::CONFIRMED => __('enums.order_status.confirmed'),
            self::PROCESSING => __('enums.order_status.processing'),
            self::SHIPPED => __('enums.order_status.shipped'),
            self::DELIVERED => __('enums.order_status.delivered'),
            self::CANCELLED => __('enums.order_status.cancelled'),
            self::REFUNDED => __('enums.order_status.refunded'),
            self::RETURNED => __('enums.order_status.returned'),
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::PENDING => 'warning',
            self::CONFIRMED => 'info',
            self::PROCESSING => 'primary',
            self::SHIPPED => 'success',
            self::DELIVERED => 'success',
            self::CANCELLED => 'danger',
            self::REFUNDED => 'secondary',
            self::RETURNED => 'warning',
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::PENDING => 'heroicon-o-clock',
            self::CONFIRMED => 'heroicon-o-check-circle',
            self::PROCESSING => 'heroicon-o-cog-6-tooth',
            self::SHIPPED => 'heroicon-o-truck',
            self::DELIVERED => 'heroicon-o-check-badge',
            self::CANCELLED => 'heroicon-o-x-circle',
            self::REFUNDED => 'heroicon-o-arrow-uturn-left',
            self::RETURNED => 'heroicon-o-arrow-uturn-right',
        };
    }

    public static function getOptions(): array
    {
        return collect(self::cases())->mapWithKeys(fn($case) => [
            $case->value => $case->getLabel()
        ])->toArray();
    }
}