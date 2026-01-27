<?php

declare(strict_types=1);

namespace App\Enums;

enum ModerationState: string
{
    case Draft = 'draft';
    case Review = 'review';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Draft     => __('messages.moderation'),
            self::Review    => __('messages.moderation'),
            self::Published => __('messages.moderation'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft     => 'warning',
            self::Review    => 'info',
            self::Published => 'success',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $case) => [
            $case->value => $case->label(),
        ])->toArray();
    }
}
