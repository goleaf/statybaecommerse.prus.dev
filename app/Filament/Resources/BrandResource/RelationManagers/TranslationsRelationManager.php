<?php

declare(strict_types=1);

namespace App\Filament\Resources\BrandResource\RelationManagers;

use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Zvizvi\RelationManagerRepeater\Tables\RelationManagerRepeaterAction;

final class TranslationsRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'translations';

    protected static ?string $title = 'Translations';

    public function form(Schema $schema): Schema
    {
        // Share the same component list across create and edit so validation stays predictable.
        return $schema->components($this->getFormComponents());
    }

    public function table(Table $table): Table
    {
        // Configure the relation manager table to satisfy Filament v4's return type requirements.
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('locale')
                    ->label(__('admin/brands.fields.locale'))
                    ->badge()
                    ->color(fn (string $state): string => $this->localeBadgeColor($state))
                    ->formatStateUsing(fn (string $state): string => $this->localeLabel($state))
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('admin/brands.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('description')
                    ->label(__('admin/brands.fields.description'))
                    ->searchable()
                    ->limit(100)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('meta_title')
                    ->label(__('admin/brands.fields.seo_title'))
                    ->searchable()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('meta_description')
                    ->label(__('admin/brands.fields.seo_description'))
                    ->searchable()
                    ->limit(100)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('meta_keywords')
                    ->label(__('translations.product_meta_keywords'))
                    ->searchable()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label(__('admin/brands.fields.is_active'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin/brands.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin/brands.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('locale')
                    ->label(__('admin/brands.fields.locale'))
                    ->options($this->getLocaleOptions()),
            ])
            ->headerActions([
                RelationManagerRepeaterAction::make()
                    ->label('Quick edit ' . $this->getPluralModelLabel())
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading('Edit ' . $this->getPluralModelLabel())
                    ->modalWidth('5xl')
                    ->configureRepeater(function (Repeater $repeater): Repeater {
                        // Provide a quick-edit modal for managing records inline.
                        return $repeater
                            ->collapsible()
                            ->defaultItems(0)
                            ->schema($this->getQuickEditSchema());
                    }),
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('locale');
    }

    /**
     * @return array<int, SchemaSection>
     */
    private function getFormComponents(): array
    {
        $localeOptions = $this->getLocaleOptions();

        return [
            SchemaSection::make(__('admin/brands.sections.basic_information'))
                ->components([
                    SchemaGrid::make(2)
                        ->components([
                            Select::make('locale')
                                ->label(__('admin/brands.fields.locale'))
                                ->options($localeOptions)
                                ->required()
                                ->searchable(),
                            TextInput::make('name')
                                ->label(__('admin/brands.fields.name'))
                                ->required()
                                ->maxLength(255),
                        ]),
                    Textarea::make('description')
                        ->label(__('admin/brands.fields.description'))
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
            SchemaSection::make(__('admin/brands.sections.seo'))
                ->components([
                    SchemaGrid::make(2)
                        ->components([
                            TextInput::make('meta_title')
                                ->label(__('admin/brands.fields.seo_title'))
                                ->maxLength(255),
                            TextInput::make('meta_keywords')
                                ->label(__('translations.product_meta_keywords'))
                                ->maxLength(255),
                        ]),
                    Textarea::make('meta_description')
                        ->label(__('admin/brands.fields.seo_description'))
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
            SchemaSection::make(__('admin/brands.sections.settings'))
                ->components([
                    Toggle::make('is_active')
                        ->label(__('admin/brands.fields.is_active'))
                        ->default(true),
                ]),
        ];
    }

    /**
     * @return array<int, Component>
     */
    protected function getQuickEditSchema(): array
    {
        $localeOptions = $this->getLocaleOptions();

        return [
            Hidden::make('id'),
            Select::make('locale')
                ->label(__('admin/brands.fields.locale'))
                ->options($localeOptions)
                ->required()
                ->searchable()
                ->disabled(fn (callable $get): bool => filled($get('id')))
                ->dehydrated(true),
            TextInput::make('name')
                ->label(__('admin/brands.fields.name'))
                ->required()
                ->maxLength(255),
            Textarea::make('description')
                ->label(__('admin/brands.fields.description'))
                ->rows(3),
            TextInput::make('meta_title')
                ->label(__('admin/brands.fields.seo_title'))
                ->maxLength(255),
            Textarea::make('meta_description')
                ->label(__('admin/brands.fields.seo_description'))
                ->rows(3),
            TextInput::make('meta_keywords')
                ->label(__('translations.product_meta_keywords'))
                ->maxLength(255),
            Toggle::make('is_active')
                ->label(__('admin/brands.fields.is_active'))
                ->default(true),
        ];
    }

    /**
     * Build locale options from the configured languages while keeping display labels human readable.
     */
    private function getLocaleOptions(): array
    {
        $configured = explode(',', (string) config('app.supported_locales', 'lt,en'));

        return collect($configured)
            ->map(static fn (string $locale): string => trim($locale))
            ->filter()
            ->unique()
            ->mapWithKeys(function (string $locale): array {
                $labels = [
                    'lt' => 'Lithuanian',
                    'en' => 'English',
                    'de' => 'German',
                    'fr' => 'French',
                    'es' => 'Spanish',
                    'it' => 'Italian',
                    'pl' => 'Polish',
                    'ru' => 'Russian',
                ];

                return [$locale => $labels[$locale] ?? Str::upper($locale)];
            })
            ->toArray();
    }

    private function localeBadgeColor(string $locale): string
    {
        return match ($locale) {
            'lt' => 'success',
            'en' => 'primary',
            'de' => 'warning',
            'fr' => 'info',
            'es' => 'danger',
            'it' => 'secondary',
            'pl' => 'gray',
            'ru' => 'slate',
            default => 'gray',
        };
    }

    private function localeLabel(string $locale): string
    {
        return $this->getLocaleOptions()[$locale] ?? Str::upper($locale);
    }
}
