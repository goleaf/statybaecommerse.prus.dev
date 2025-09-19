<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\LegalResource\Pages;
use App\Models\Translations\LegalTranslation;
use App\Models\Legal;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * LegalResource
 *
 * Filament v4 resource for Legal document management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class LegalResource extends Resource
{
    protected static ?string $model = Legal::class;

    /**
     * @var UnitEnum|string|null
     */
    protected static string | UnitEnum | null $navigationGroup = "Products";

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'title';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('legal.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return "System"->value;
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('legal.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('legal.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('Legal Document')
                ->tabs([
                    Tab::make(__('legal.basic_information'))
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('key')
                                        ->label(__('legal.key'))
                                        ->required()
                                        ->maxLength(255)
                                        ->unique(ignoreRecord: true)
                                        ->helperText(__('legal.key_help')),
                                    Select::make('type')
                                        ->label(__('legal.type'))
                                        ->options(Legal::getTypes())
                                        ->required()
                                        ->default('privacy_policy')
                                        ->searchable(),
                                ]),
                            Grid::make(2)
                                ->schema([
                                    Toggle::make('is_enabled')
                                        ->label(__('legal.is_enabled'))
                                        ->default(true),
                                    Toggle::make('is_required')
                                        ->label(__('legal.is_required'))
                                        ->default(false),
                                ]),
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('sort_order')
                                        ->label(__('legal.sort_order'))
                                        ->numeric()
                                        ->default(0),
                                    DateTimePicker::make('published_at')
                                        ->label(__('legal.published_at'))
                                        ->displayFormat('d/m/Y H:i'),
                                ]),
                            KeyValue::make('meta_data')
                                ->label(__('legal.meta_data'))
                                ->helperText(__('legal.meta_data_help')),
                        ]),
                    Tab::make(__('legal.translations'))
                        ->schema([
                            Repeater::make('translations')
                                ->label(__('legal.translations'))
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Select::make('locale')
                                                ->label(__('legal.locale'))
                                                ->options([
                                                    'lt' => 'Lietuvių',
                                                    'en' => 'English',
                                                ])
                                                ->required(),
                                            TextInput::make('title')
                                                ->label(__('legal.title'))
                                                ->required()
                                                ->maxLength(255)
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(function (Forms\Set $set, $state, $get) {
                                                    if ($get('locale') && $state) {
                                                        $set('slug', \Str::slug($state) . '-' . $get('locale'));
                                                    }
                                                }),
                                        ]),
                                    TextInput::make('slug')
                                        ->label(__('legal.slug'))
                                        ->maxLength(255)
                                        ->helperText(__('legal.slug_help')),
                                    Textarea::make('content')
                                        ->label(__('legal.content'))
                                        ->rows(10)
                                        ->columnSpanFull(),
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('seo_title')
                                                ->label(__('legal.seo_title'))
                                                ->maxLength(255),
                                            Textarea::make('seo_description')
                                                ->label(__('legal.seo_description'))
                                                ->rows(2)
                                                ->maxLength(500),
                                        ]),
                                ])
                                ->defaultItems(2)
                                ->addActionLabel(__('legal.add_translation'))
                                ->reorderable(false)
                                ->collapsible(),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label(__('legal.key'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(30),
                TextColumn::make('type')
                    ->label(__('legal.type'))
                    ->formatStateUsing(fn(string $state): string => __("legal.types.{$state}"))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'privacy_policy' => 'blue',
                        'terms_of_use' => 'green',
                        'cookie_policy' => 'yellow',
                        'refund_policy' => 'orange',
                        'shipping_policy' => 'purple',
                        'return_policy' => 'pink',
                        'disclaimer' => 'gray',
                        'gdpr_policy' => 'red',
                        'imprint' => 'indigo',
                        'legal_document' => 'slate',
                        default => 'gray',
                    }),
                TextColumn::make('translations.title')
                    ->label(__('legal.title'))
                    ->searchable()
                    ->limit(50)
                    ->formatStateUsing(function ($record) {
                        return $record->getTranslatedTitle() ?? $record->key;
                    }),
                BadgeColumn::make('is_enabled')
                    ->label(__('legal.status'))
                    ->formatStateUsing(fn(bool $state): string => $state ? __('legal.enabled') : __('legal.disabled'))
                    ->colors([
                        'success' => true,
                        'danger' => false,
                    ]),
                IconColumn::make('is_required')
                    ->label(__('legal.is_required'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label(__('legal.sort_order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('published_at')
                    ->label(__('legal.published_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('legal.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('legal.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(Legal::getTypes())
                    ->searchable(),
                TernaryFilter::make('is_enabled')
                    ->label(__('legal.is_enabled'))
                    ->trueLabel(__('legal.enabled_only'))
                    ->falseLabel(__('legal.disabled_only'))
                    ->native(false),
                TernaryFilter::make('is_required')
                    ->label(__('legal.is_required'))
                    ->trueLabel(__('legal.required_only'))
                    ->falseLabel(__('legal.optional_only'))
                    ->native(false),
                Tables\Filters\Filter::make('published_at')
                    ->form([
                        DateTimePicker::make('published_from')
                            ->label(__('legal.published_from')),
                        DateTimePicker::make('published_until')
                            ->label(__('legal.published_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['published_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('published_at', '>=', $date),
                            )
                            ->when(
                                $data['published_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('published_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                Action::make('enable')
                    ->label(__('legal.enable'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(Legal $record): bool => !$record->is_enabled)
                    ->action(function (Legal $record): void {
                        $record->enable();
                        Notification::make()
                            ->title(__('legal.enabled_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('disable')
                    ->label(__('legal.disable'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(Legal $record): bool => $record->is_enabled)
                    ->action(function (Legal $record): void {
                        $record->disable();
                        Notification::make()
                            ->title(__('legal.disabled_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('publish')
                    ->label(__('legal.publish'))
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn(Legal $record): bool => !$record->published_at)
                    ->action(function (Legal $record): void {
                        $record->publish();
                        Notification::make()
                            ->title(__('legal.published_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('unpublish')
                    ->label(__('legal.unpublish'))
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->visible(fn(Legal $record): bool => $record->published_at)
                    ->action(function (Legal $record): void {
                        $record->unpublish();
                        Notification::make()
                            ->title(__('legal.unpublished_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('duplicate')
                    ->label(__('legal.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->action(function (Legal $record): void {
                        $newRecord = $record->replicate();
                        $newRecord->key = $record->key . '-copy-' . time();
                        $newRecord->save();

                        // Copy translations
                        foreach ($record->translations as $translation) {
                            $newTranslation = $translation->replicate();
                            $newTranslation->legal_id = $newRecord->id;
                            $newTranslation->save();
                        }

                        Notification::make()
                            ->title(__('legal.duplicated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('enable')
                        ->label(__('legal.enable_selected'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->enable();
                            Notification::make()
                                ->title(__('legal.bulk_enabled_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('disable')
                        ->label(__('legal.disable_selected'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Collection $records): void {
                            $records->each->disable();
                            Notification::make()
                                ->title(__('legal.bulk_disabled_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('publish')
                        ->label(__('legal.publish_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            $records->each->publish();
                            Notification::make()
                                ->title(__('legal.bulk_published_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('unpublish')
                        ->label(__('legal.unpublish_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->unpublish();
                            Notification::make()
                                ->title(__('legal.bulk_unpublished_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Get the relations for this resource.
     * @return array
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the pages for this resource.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLegal::route('/'),
            'create' => Pages\CreateLegal::route('/create'),
            'view' => Pages\ViewLegal::route('/{record}'),
            'edit' => Pages\EditLegal::route('/{record}/edit'),
        ];
    }
}
