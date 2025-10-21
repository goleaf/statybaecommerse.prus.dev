<?php

declare(strict_types=1);

namespace App\Enums;

enum DocumentTemplateType: string
{
    case Invoice = 'invoice';
    case Quote = 'quote';
    case Receipt = 'receipt';
    case Contract = 'contract';
    case Report = 'report';
    case Email = 'email';
    case Other = 'other';

    public function label(): string
    {
        return __('admin/document_templates.types.' . $this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::Invoice  => 'success',
            self::Receipt  => 'info',
            self::Quote    => 'warning',
            self::Contract => 'danger',
            self::Report   => 'gray',
            self::Email    => 'primary',
            self::Other    => 'secondary',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case): array => [$case->value => $case->label()])
            ->all();
    }
}
