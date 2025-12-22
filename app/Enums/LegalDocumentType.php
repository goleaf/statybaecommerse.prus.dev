<?php

declare(strict_types=1);

namespace App\Enums;

use App\Contracts\EnumInterface;
use Illuminate\Support\Collection;

enum LegalDocumentType: string implements EnumInterface
{
    case PRIVACY_POLICY = 'privacy_policy';
    case TERMS_OF_USE = 'terms_of_use';
    case REFUND_POLICY = 'refund_policy';
    case SHIPPING_POLICY = 'shipping_policy';
    case COOKIE_POLICY = 'cookie_policy';
    case GDPR_POLICY = 'gdpr_policy';
    case LEGAL_NOTICE = 'legal_notice';
    case IMPRINT = 'imprint';
    case LEGAL_DOCUMENT = 'legal_document';

    public function label(): string
    {
        return match ($this) {
            self::PRIVACY_POLICY  => __('legal.types.privacy_policy'),
            self::TERMS_OF_USE    => __('legal.types.terms_of_use'),
            self::REFUND_POLICY   => __('legal.types.refund_policy'),
            self::SHIPPING_POLICY => __('legal.types.shipping_policy'),
            self::COOKIE_POLICY   => __('legal.types.cookie_policy'),
            self::GDPR_POLICY     => __('legal.types.gdpr_policy'),
            self::LEGAL_NOTICE    => __('legal.types.legal_notice'),
            self::IMPRINT         => __('legal.types.imprint'),
            self::LEGAL_DOCUMENT  => __('legal.types.legal_document'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::PRIVACY_POLICY  => __('legal.descriptions.privacy_policy'),
            self::TERMS_OF_USE    => __('legal.descriptions.terms_of_use'),
            self::REFUND_POLICY   => __('legal.descriptions.refund_policy'),
            self::SHIPPING_POLICY => __('legal.descriptions.shipping_policy'),
            self::COOKIE_POLICY   => __('legal.descriptions.cookie_policy'),
            self::GDPR_POLICY     => __('legal.descriptions.gdpr_policy'),
            self::LEGAL_NOTICE    => __('legal.descriptions.legal_notice'),
            self::IMPRINT         => __('legal.descriptions.imprint'),
            self::LEGAL_DOCUMENT  => __('legal.descriptions.legal_document'),
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PRIVACY_POLICY  => 'heroicon-o-shield-check',
            self::TERMS_OF_USE    => 'heroicon-o-document-text',
            self::REFUND_POLICY   => 'heroicon-o-arrow-uturn-left',
            self::SHIPPING_POLICY => 'heroicon-o-truck',
            self::COOKIE_POLICY   => 'heroicon-o-cog-6-tooth',
            self::GDPR_POLICY     => 'heroicon-o-lock-closed',
            self::LEGAL_NOTICE    => 'heroicon-o-information-circle',
            self::IMPRINT         => 'heroicon-o-building-office',
            self::LEGAL_DOCUMENT  => 'heroicon-o-document',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PRIVACY_POLICY  => 'blue',
            self::TERMS_OF_USE    => 'green',
            self::REFUND_POLICY   => 'orange',
            self::SHIPPING_POLICY => 'purple',
            self::COOKIE_POLICY   => 'yellow',
            self::GDPR_POLICY     => 'red',
            self::LEGAL_NOTICE    => 'indigo',
            self::IMPRINT         => 'gray',
            self::LEGAL_DOCUMENT  => 'slate',
        };
    }

    public function priority(): int
    {
        return match ($this) {
            self::PRIVACY_POLICY  => 1,
            self::TERMS_OF_USE    => 2,
            self::REFUND_POLICY   => 3,
            self::SHIPPING_POLICY => 4,
            self::COOKIE_POLICY   => 5,
            self::GDPR_POLICY     => 6,
            self::LEGAL_NOTICE    => 7,
            self::IMPRINT         => 8,
            self::LEGAL_DOCUMENT  => 9,
        };
    }

    public function toArray(): array
    {
        return [
            'value'       => $this->value,
            'label'       => $this->label(),
            'description' => $this->description(),
            'icon'        => $this->icon(),
            'color'       => $this->color(),
            'priority'    => $this->priority(),
            'is_required' => $this->isRequired(),
        ];
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labels(): array
    {
        $labels = [];
        foreach (self::cases() as $case) {
            $labels[$case->value] = $case->label();
        }

        return $labels;
    }

    public static function options(): array
    {
        return self::labels();
    }

    public static function optionsWithDescriptions(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = [
                'label'       => $case->label(),
                'description' => $case->description(),
            ];
        }

        return $options;
    }

    public static function ordered(): Collection
    {
        return collect(self::cases())
            ->sortBy(fn (self $case) => $case->priority())
            ->values();
    }

    public static function fromLabel(string $label): ?static
    {
        foreach (self::cases() as $case) {
            if ($case->label() === $label) {
                return $case;
            }
        }

        return null;
    }

    public static function collection(): Collection
    {
        return collect(self::cases());
    }

    /**
     * Get types that are required by default.
     *
     * @return array<int, self>
     */
    public static function getRequiredTypes(): array
    {
        return [
            self::PRIVACY_POLICY,
            self::TERMS_OF_USE,
        ];
    }

    /**
     * Check if this type is required by default.
     */
    public function isRequired(): bool
    {
        return in_array($this, self::getRequiredTypes(), true);
    }

    /**
     * Get all types as associative array for forms.
     *
     * @return array<string, string>
     */
    public static function getOptions(): array
    {
        return self::options();
    }
}
