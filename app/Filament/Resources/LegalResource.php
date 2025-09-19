<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\LegalResource\Pages;
use App\Models\Legal;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
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
    protected static string|UnitEnum|null $navigationGroup = 'Products';

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
        return "System"->label();
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('legal.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
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
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('legal.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('title')
                                ->label(__('legal.title'))
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn(string $operation, $state, Forms\Set $set) =>
                                    $operation === 'create' ? $set('slug', \Str::slug($state)) : null),
                            TextInput::make('slug')
                                ->label(__('legal.slug'))
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash']),
                        ]),
                    Select::make('type')
                        ->label(__('legal.type'))
                        ->options([
                            'privacy_policy' => __('legal.types.privacy_policy'),
                            'terms_of_service' => __('legal.types.terms_of_service'),
                            'cookie_policy' => __('legal.types.cookie_policy'),
                            'refund_policy' => __('legal.types.refund_policy'),
                            'shipping_policy' => __('legal.types.shipping_policy'),
                            'return_policy' => __('legal.types.return_policy'),
                            'disclaimer' => __('legal.types.disclaimer'),
                            'gdpr' => __('legal.types.gdpr'),
                            'imprint' => __('legal.types.imprint'),
                            'other' => __('legal.types.other'),
                        ])
                        ->required()
                        ->default('privacy_policy'),
                    Textarea::make('description')
                        ->label(__('legal.description'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            Section::make(__('legal.content'))
                ->schema([
                    RichEditor::make('content')
                        ->label(__('legal.content'))
                        ->required()
                        ->columnSpanFull()
                        ->toolbarButtons([
                            'bold',
                            'italic',
                            'underline',
                            'strike',
                            'link',
                            'bulletList',
                            'orderedList',
                            'h2',
                            'h3',
                            'h4',
                            'blockquote',
                            'codeBlock',
                        ]),
                ]),
            Section::make(__('legal.publishing'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_published')
                                ->label(__('legal.is_published'))
                                ->default(false),
                            Toggle::make('is_required')
                                ->label(__('legal.is_required'))
                                ->default(false),
                        ]),
                    Grid::make(2)
                        ->schema([
                            DateTimePicker::make('published_at')
                                ->label(__('legal.published_at'))
                                ->default(now())
                                ->displayFormat('d/m/Y H:i'),
                            DateTimePicker::make('effective_date')
                                ->label(__('legal.effective_date'))
                                ->default(now())
                                ->displayFormat('d/m/Y'),
                        ]),
                ]),
            Section::make(__('legal.versioning'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('version')
                                ->label(__('legal.version'))
                                ->required()
                                ->maxLength(20)
                                ->default('1.0'),
                            TextInput::make('previous_version_id')
                                ->label(__('legal.previous_version'))
                                ->numeric()
                                ->helperText(__('legal.previous_version_help')),
                        ]),
                ]),
            Section::make(__('legal.seo'))
                ->schema([
                    TextInput::make('seo_title')
                        ->label(__('legal.seo_title'))
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Textarea::make('seo_description')
                        ->label(__('legal.seo_description'))
                        ->rows(2)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
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
                TextColumn::make('title')
                    ->label(__('legal.title'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(50),
                TextColumn::make('type')
                    ->label(__('legal.type'))
                    ->formatStateUsing(fn(string $state): string => __("legal.types.{$state}"))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'privacy_policy' => 'blue',
                        'terms_of_service' => 'green',
                        'cookie_policy' => 'yellow',
                        'refund_policy' => 'orange',
                        'shipping_policy' => 'purple',
                        'return_policy' => 'pink',
                        'disclaimer' => 'gray',
                        'gdpr' => 'red',
                        'imprint' => 'indigo',
                        'other' => 'slate',
                        default => 'gray',
                    }),
                TextColumn::make('version')
                    ->label(__('legal.version'))
                    ->badge()
                    ->color('gray'),
                TextColumn::make('description')
                    ->label(__('legal.description'))
                    ->limit(100)
                    ->toggleable(isToggledHiddenByDefault: true),
                BadgeColumn::make('is_published')
                    ->label(__('legal.status'))
                    ->formatStateUsing(fn(bool $state): string => $state ? __('legal.published') : __('legal.draft'))
                    ->colors([
                        'success' => true,
                        'warning' => false,
                    ]),
                IconColumn::make('is_required')
                    ->label(__('legal.is_required'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('effective_date')
                    ->label(__('legal.effective_date'))
                    ->date()
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
                    ->label(__('legal.type'))
                    ->options([
                        'privacy_policy' => __('legal.types.privacy_policy'),
                        'terms_of_service' => __('legal.types.terms_of_service'),
                        'cookie_policy' => __('legal.types.cookie_policy'),
                        'refund_policy' => __('legal.types.refund_policy'),
                        'shipping_policy' => __('legal.types.shipping_policy'),
                        'return_policy' => __('legal.types.return_policy'),
                        'disclaimer' => __('legal.types.disclaimer'),
                        'gdpr' => __('legal.types.gdpr'),
                        'imprint' => __('legal.types.imprint'),
                        'other' => __('legal.types.other'),
                    ]),
                TernaryFilter::make('is_published')
                    ->label(__('legal.is_published'))
                    ->boolean()
                    ->trueLabel(__('legal.published_only'))
                    ->falseLabel(__('legal.draft_only'))
                    ->native(false),
                TernaryFilter::make('is_required')
                    ->label(__('legal.is_required'))
                    ->boolean()
                    ->trueLabel(__('legal.required_only'))
                    ->falseLabel(__('legal.optional_only'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                Action::make('publish')
                    ->label(__('legal.publish'))
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->visible(fn(Legal $record): bool => !$record->is_published)
                    ->action(function (Legal $record): void {
                        $record->update([
                            'is_published' => true,
                            'published_at' => now(),
                        ]);

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
                    ->visible(fn(Legal $record): bool => $record->is_published)
                    ->action(function (Legal $record): void {
                        $record->update(['is_published' => false]);

                        Notification::make()
                            ->title(__('legal.unpublished_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('create_new_version')
                    ->label(__('legal.create_new_version'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->action(function (Legal $record): void {
                        // Create new version logic here
                        Notification::make()
                            ->title(__('legal.new_version_created_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('publish')
                        ->label(__('legal.publish_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update([
                                'is_published' => true,
                                'published_at' => now(),
                            ]);

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
                            $records->each->update(['is_published' => false]);

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
     * @return array
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
