<?php

declare(strict_types=1);

namespace App\Enums;

enum ScheduleType: string
{
    case ONCE = 'once';
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
    case CUSTOM = 'custom';

    public function getLabel(): string
    {
        return match ($this) {
            self::ONCE => __('admin.campaign_schedules.schedule_types.once'),
            self::DAILY => __('admin.campaign_schedules.schedule_types.daily'),
            self::WEEKLY => __('admin.campaign_schedules.schedule_types.weekly'),
            self::MONTHLY => __('admin.campaign_schedules.schedule_types.monthly'),
            self::CUSTOM => __('admin.campaign_schedules.schedule_types.custom'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::ONCE => 'primary',
            self::DAILY => 'success',
            self::WEEKLY => 'warning',
            self::MONTHLY => 'info',
            self::CUSTOM => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::ONCE => 'heroicon-o-calendar',
            self::DAILY => 'heroicon-o-calendar-days',
            self::WEEKLY => 'heroicon-o-calendar',
            self::MONTHLY => 'heroicon-o-calendar',
            self::CUSTOM => 'heroicon-o-cog-6-tooth',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])
            ->toArray();
    }
}
