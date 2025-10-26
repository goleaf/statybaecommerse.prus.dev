<?php

declare(strict_types=1);

use App\Contracts\EnumInterface;
use App\Factories\EnumFactory;
use Illuminate\Support\Collection;

beforeEach(function (): void {
    // Always reset the factory to its default state before each test to avoid leaking registrations.
    EnumFactory::reset();
});

afterEach(function (): void {
    // Ensure we revert any stateful mutations even if a test fails mid-execution.
    EnumFactory::reset();
});

it('registers custom enums and surfaces helper metadata', function (): void {
    // Register a bespoke enum so the factory learns about new cases at runtime.
    EnumFactory::register('fake_status', FakePublishingStatus::class);

    $enum = EnumFactory::create('fake_status', FakePublishingStatus::Draft->value);

    expect($enum)
        ->toBeInstanceOf(FakePublishingStatus::class)
        ->and(EnumFactory::getEnumValues('fake_status'))
        ->toBe(FakePublishingStatus::values())
        ->and(EnumFactory::getEnumLabels('fake_status'))
        ->toBe(FakePublishingStatus::labels())
        ->and(EnumFactory::getEnumOptions('fake_status'))
        ->toBe(FakePublishingStatus::options())
        ->and(EnumFactory::validateValue('fake_status', FakePublishingStatus::Published->value))
        ->toBeTrue();
});

it('resets overrides to restore default enum registrations', function (): void {
    // Override an existing enum mapping to emulate a custom seed scenario.
    EnumFactory::register('payment_type', FakePublishingStatus::class);
    EnumFactory::register('another_status', FakePublishingStatus::class);

    expect(EnumFactory::getEnumClassName('payment_type'))
        ->toBe(FakePublishingStatus::class)
        ->and(EnumFactory::exists('another_status'))
        ->toBeTrue();

    // Reset should drop temporary mappings and restore default class bindings.
    EnumFactory::reset();

    expect(EnumFactory::getEnumClassName('payment_type'))
        ->toBe(\App\Enums\PaymentType::class)
        ->and(EnumFactory::exists('another_status'))
        ->toBeFalse();
});

/**
 * A lightweight fake enum implementation used to verify runtime factory registration.
 */
enum FakePublishingStatus: string implements EnumInterface
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';

    public function label(): string
    {
        // Provide user-facing labels mirroring a typical localisation lookup.
        return match ($this) {
            self::Draft     => 'Draft',
            self::Published => 'Published',
            self::Archived  => 'Archived',
        };
    }

    public function description(): string
    {
        // Mirror descriptive tooltips that would normally power UI hints.
        return match ($this) {
            self::Draft     => 'Work in progress entry.',
            self::Published => 'Visible to customers.',
            self::Archived  => 'Hidden but retained for history.',
        };
    }

    public function icon(): string
    {
        // Keep icon identifiers consistent with the Filament icon set.
        return match ($this) {
            self::Draft     => 'heroicon-o-pencil-square',
            self::Published => 'heroicon-o-bolt',
            self::Archived  => 'heroicon-o-archive-box',
        };
    }

    public function color(): string
    {
        // Return Tailwind-friendly colour hints for quick status styling.
        return match ($this) {
            self::Draft     => 'gray',
            self::Published => 'emerald',
            self::Archived  => 'slate',
        };
    }

    public function priority(): int
    {
        // Smaller numbers represent higher priority ordering in dropdowns.
        return match ($this) {
            self::Published => 1,
            self::Draft     => 2,
            self::Archived  => 3,
        };
    }

    public function toArray(): array
    {
        // Provide a structured payload similar to production enums for serialization.
        return [
            'value'       => $this->value,
            'label'       => $this->label(),
            'description' => $this->description(),
            'icon'        => $this->icon(),
            'color'       => $this->color(),
            'priority'    => $this->priority(),
        ];
    }

    public static function values(): array
    {
        // Expose the raw string values used when persisting to the database.
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }

    public static function labels(): array
    {
        // Keep label lookups fast by mapping values to translated strings.
        return array_map(static fn (self $case): string => $case->label(), self::cases());
    }

    public static function options(): array
    {
        // Provide a key/value map suitable for select inputs.
        return collect(self::cases())
            ->mapWithKeys(static fn (self $case): array => [$case->value => $case->label()])
            ->all();
    }

    public static function optionsWithDescriptions(): array
    {
        // Return a richer payload for advanced UI components requiring descriptions.
        return collect(self::cases())
            ->mapWithKeys(static function (self $case): array {
                return [
                    $case->value => [
                        'label'       => $case->label(),
                        'description' => $case->description(),
                        'icon'        => $case->icon(),
                        'color'       => $case->color(),
                        'priority'    => $case->priority(),
                    ],
                ];
            })
            ->all();
    }

    /**
     * @return Collection<int, self>
     */
    public static function ordered(): Collection
    {
        // Sort cases by priority while returning a collection for fluent chaining.
        return collect(self::cases())
            ->sortBy(static fn (self $case): int => $case->priority())
            ->values();
    }

    public static function fromLabel(string $label): ?static
    {
        // Resolve a case by its human-readable label to mimic production helpers.
        $match = collect(self::cases())
            ->first(static fn (self $case): bool => $case->label() === $label);

        return $match instanceof self ? $match : null;
    }

    /**
     * @return Collection<int, self>
     */
    public static function collection(): Collection
    {
        // Provide a convenience wrapper that mirrors other enums in the application.
        return collect(self::cases());
    }
}
