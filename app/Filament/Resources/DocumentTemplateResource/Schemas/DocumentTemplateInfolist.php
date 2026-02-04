<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentTemplateResource\Schemas;

use App\Enums\DocumentTemplateCategory;
use App\Enums\DocumentTemplateType;
use App\Models\DocumentTemplate;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Stringable;

class DocumentTemplateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.document_templates.sections.basic_information'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextEntry::make('name')
                                ->label(__('messages.name'))
                                ->formatStateUsing(fn ($state): string => self::stringifyState($state)),
                            TextEntry::make('slug')
                                ->label(__('messages.slug'))
                                ->formatStateUsing(fn ($state): string => self::stringifyState($state)),
                            TextEntry::make('type')
                                ->label(__('admin.document_templates.fields.type'))
                                ->badge()
                                ->formatStateUsing(fn (?string $state): string => self::resolveTypeLabel($state))
                                ->color(fn (?string $state): string => self::resolveTypeColor($state)),
                            TextEntry::make('category')
                                ->label(__('admin.document_templates.fields.category'))
                                ->badge()
                                ->formatStateUsing(fn (?string $state): string => self::resolveCategoryLabel($state))
                                ->color(fn (?string $state): string => self::resolveCategoryColor($state)),
                            IconEntry::make('is_active')
                                ->label(__('admin.document_templates.fields.is_active'))
                                ->boolean(),
                            TextEntry::make('updated_at')
                                ->label(__('messages.updated_at'))
                                ->dateTime(),
                        ]),
                    TextEntry::make('description')
                        ->label(__('messages.description'))
                        ->formatStateUsing(fn ($state): string => self::stringifyState($state))
                        ->columnSpanFull(),
                ]),
            Section::make(__('admin.document_templates.sections.content'))
                ->schema([
                    TextEntry::make('content')
                        ->label(__('admin.document_templates.fields.content'))
                        ->formatStateUsing(fn ($state): string => self::stringifyState($state))
                        ->html()
                        ->columnSpanFull(),
                ]),
            Section::make(__('admin.document_templates.sections.variables'))
                ->schema([
                    KeyValueEntry::make('variables')
                        ->label(__('admin.document_templates.fields.variables'))
                        ->state(fn (?DocumentTemplate $record): array => self::normalizeKeyValueState($record?->variables)),
                ]),
            Section::make(__('admin.document_templates.sections.settings'))
                ->schema([
                    KeyValueEntry::make('settings')
                        ->label(__('admin.document_templates.fields.settings'))
                        ->state(fn (?DocumentTemplate $record): array => self::normalizeKeyValueState($record?->settings)),
                ]),
        ]);
    }

    private static function resolveTypeLabel(?string $state): string
    {
        if ($state === null || $state === '') {
            return __('admin.not_set');
        }

        $enum = DocumentTemplateType::tryFrom($state);

        return $enum?->label() ?? Str::headline($state);
    }

    private static function resolveTypeColor(?string $state): string
    {
        if ($state === null || $state === '') {
            return 'gray';
        }

        return DocumentTemplateType::tryFrom($state)?->color() ?? 'gray';
    }

    private static function resolveCategoryLabel(?string $state): string
    {
        if ($state === null || $state === '') {
            return __('admin.not_set');
        }

        $enum = DocumentTemplateCategory::tryFrom($state);

        return $enum?->label() ?? Str::headline($state);
    }

    private static function resolveCategoryColor(?string $state): string
    {
        if ($state === null || $state === '') {
            return 'gray';
        }

        return DocumentTemplateCategory::tryFrom($state)?->color() ?? 'gray';
    }

    /**
     * @return array<string, string>
     */
    private static function normalizeKeyValueState(mixed $state): array
    {
        if (! is_array($state)) {
            return [];
        }

        return collect($state)
            ->mapWithKeys(function (mixed $value, mixed $key): array {
                $key = is_scalar($key) ? (string) $key : (string) json_encode($key);
                $value = self::stringifyState($value);

                return [$key => $value];
            })
            ->all();
    }

    private static function stringifyState(mixed $state): string
    {
        if ($state === null) {
            return '';
        }

        if (is_bool($state)) {
            return $state ? 'true' : 'false';
        }

        if (is_scalar($state) || $state instanceof Stringable) {
            return (string) $state;
        }

        if (is_array($state)) {
            $value = Arr::map($state, fn ($item) => self::stringifyState($item));

            return (string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return (string) json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
