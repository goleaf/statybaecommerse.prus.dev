<?php

declare(strict_types=1);

namespace App\Filament\Resources\News\Schemas;

use App\Enums\ModerationState;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

final class NewsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('newsTabs')
                    ->tabs(array_merge(
                        [self::generalTab()],
                        self::localeTabs(),
                    ))
                    ->columnSpanFull(),
            ]);
    }

    private static function generalTab(): Tab
    {
        return Tab::make(__('messages.general'))
            ->schema([
                Section::make(__('admin.news.publication'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('moderation_state')
                                    ->label(__('messages.moderation'))
                                    ->options([
                                        ModerationState::Draft->value     => __('admin.news.state_draft'),
                                        ModerationState::Review->value    => __('admin.news.state_review'),
                                        ModerationState::Published->value => __('admin.news.state_published'),
                                    ])
                                    ->default(ModerationState::Draft->value)
                                    ->required(),
                                DateTimePicker::make('published_at')
                                    ->label(__('admin.news.published_at')),
                                DateTimePicker::make('submitted_for_review_at')
                                    ->label(__('admin.news.submitted_for_review_at')),
                                DateTimePicker::make('approved_at')
                                    ->label(__('admin.news.approved_at')),
                                TextInput::make('author_name')
                                    ->label(__('admin.news.author_name'))
                                    ->maxLength(255),
                                TextInput::make('author_email')
                                    ->label(__('admin.news.author_email'))
                                    ->email()
                                    ->maxLength(255),
                            ]),
                        Grid::make(3)
                            ->schema([
                                Toggle::make('is_visible')
                                    ->label(__('messages.visible'))
                                    ->default(true)
                                    ->required(),
                                Toggle::make('is_featured')
                                    ->label(__('messages.featured'))
                                    ->default(false),
                                Toggle::make('is_breaking')
                                    ->label(__('admin.news.is_breaking'))
                                    ->default(false),
                            ]),
                        TextInput::make('view_count')
                            ->label(__('admin.news.view_count'))
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false),
                    ]),
                Section::make(__('admin.news.meta_data'))
                    ->schema([
                        KeyValue::make('meta_data')
                            ->label(__('admin.news.meta_data'))
                            ->keyLabel(__('messages.key'))
                            ->valueLabel(__('messages.value'))
                            ->addActionLabel(__('admin.news.add_meta_data'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * @return array<int, Tab>
     */
    private static function localeTabs(): array
    {
        $locales = self::resolveLocales();
        $defaultLocale = self::defaultLocale();

        return array_map(
            static fn (string $locale): Tab => Tab::make(self::localeLabel($locale))
                ->schema([
                    Section::make(__('admin.news.translations'))
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    TextInput::make("title.{$locale}")
                                        ->label(__('messages.title'))
                                        ->required($locale === $defaultLocale)
                                        ->maxLength(255),
                                    TextInput::make("slug.{$locale}")
                                        ->label(__('messages.slug'))
                                        ->required($locale === $defaultLocale)
                                        ->maxLength(255)
                                        ->dehydrateStateUsing(static function (mixed $state): ?string {
                                            if (! is_string($state)) {
                                                return null;
                                            }

                                            $value = trim($state);

                                            return $value === '' ? null : Str::slug($value);
                                        }),
                                ]),
                            Textarea::make("summary.{$locale}")
                                ->label(__('messages.summary'))
                                ->rows(3)
                                ->columnSpanFull(),
                            RichEditor::make("content.{$locale}")
                                ->label(__('messages.content'))
                                ->columnSpanFull(),
                            TextInput::make("seo_title.{$locale}")
                                ->label(__('admin.news.seo_title'))
                                ->maxLength(255),
                            Textarea::make("seo_description.{$locale}")
                                ->label(__('admin.news.seo_description'))
                                ->rows(3),
                        ]),
                ]),
            $locales,
        );
    }

    /**
     * @return array<int, string>
     */
    private static function resolveLocales(): array
    {
        $rawLocales = config('filament-language-tabs.default_locales', []);

        if (! is_array($rawLocales)) {
            $rawLocales = explode(',', (string) $rawLocales);
        }

        $normalized = array_map(
            static fn (string $locale): string => trim($locale),
            $rawLocales,
        );

        return array_values(array_filter($normalized, static fn (string $locale): bool => $locale !== ''));
    }

    private static function defaultLocale(): string
    {
        return (string) config('app.locale', 'lt');
    }

    private static function localeLabel(string $locale): string
    {
        $locales = config('app.locales', []);

        if (is_array($locales) && isset($locales[$locale]['native'])) {
            return (string) $locales[$locale]['native'];
        }

        return strtoupper($locale);
    }
}
