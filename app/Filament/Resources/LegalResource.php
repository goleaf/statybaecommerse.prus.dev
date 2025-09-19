<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\LegalResource\Pages;
use App\Filament\Resources\LegalResource\RelationManagers;
use App\Models\Legal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Fieldset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LegalResource extends Resource
{
    protected static ?string $model = Legal::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?string $navigationLabel = 'Legal Documents';

    protected static ?string $modelLabel = 'Legal Document';

    protected static ?string $pluralModelLabel = 'Legal Documents';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Legal Document')
                    ->tabs([
                        Tabs\Tab::make('Basic Information')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('key')
                                            ->label('Document Key')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->helperText('Unique identifier for this legal document'),

                                        Select::make('type')
                                            ->label('Document Type')
                                            ->required()
                                            ->options(Legal::getTypes())
                                            ->searchable()
                                            ->preload(),
                                    ]),

                                Grid::make(3)
                                    ->schema([
                                        Toggle::make('is_enabled')
                                            ->label('Enabled')
                                            ->default(true)
                                            ->helperText('Enable this legal document'),

                                        Toggle::make('is_required')
                                            ->label('Required')
                                            ->default(false)
                                            ->helperText('Mark as required document'),

                                        TextInput::make('sort_order')
                                            ->label('Sort Order')
                                            ->numeric()
                                            ->default(0)
                                            ->helperText('Order in which documents appear'),
                                    ]),

                                DateTimePicker::make('published_at')
                                    ->label('Published At')
                                    ->helperText('When this document was published')
                                    ->displayFormat('d/m/Y H:i'),
                            ]),

                        Tabs\Tab::make('Translations')
                            ->schema([
                                Repeater::make('translations')
                                    ->label('Translations')
                                    ->relationship('translations')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('locale')
                                                    ->label('Language')
                                                    ->required()
                                                    ->options([
                                                        'lt' => 'Lithuanian',
                                                        'en' => 'English',
                                                        'ru' => 'Russian',
                                                        'de' => 'German',
                                                    ])
                                                    ->searchable()
                                                    ->preload(),

                                                TextInput::make('slug')
                                                    ->label('URL Slug')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->helperText('URL-friendly version of the title'),
                                            ]),

                                        TextInput::make('title')
                                            ->label('Title')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpanFull(),

                                        RichEditor::make('content')
                                            ->label('Content')
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
                                                'blockquote',
                                                'codeBlock',
                                            ]),

                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('seo_title')
                                                    ->label('SEO Title')
                                                    ->maxLength(255)
                                                    ->helperText('Title for search engines'),

                                                Textarea::make('seo_description')
                                                    ->label('SEO Description')
                                                    ->maxLength(500)
                                                    ->rows(3)
                                                    ->helperText('Description for search engines'),
                                            ]),
                                    ])
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state['locale'] ?? null)
                                    ->addActionLabel('Add Translation')
                                    ->defaultItems(1),
                            ]),

                        Tabs\Tab::make('Metadata')
                            ->schema([
                                KeyValue::make('meta_data')
                                    ->label('Metadata')
                                    ->helperText('Additional metadata for this document')
                                    ->keyLabel('Key')
                                    ->valueLabel('Value')
                                    ->addActionLabel('Add Metadata'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label('Key')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Key copied')
                    ->copyMessageDuration(1500),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'privacy_policy' => 'success',
                        'terms_of_use' => 'warning',
                        'refund_policy' => 'info',
                        'shipping_policy' => 'primary',
                        'cookie_policy' => 'secondary',
                        'gdpr_policy' => 'danger',
                        'legal_notice' => 'gray',
                        'imprint' => 'success',
                        'legal_document' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => Legal::getTypes()[$state] ?? $state)
                    ->sortable(),

                TextColumn::make('translations.title')
                    ->label('Title')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),

                IconColumn::make('is_enabled')
                    ->label('Enabled')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_required')
                    ->label('Required')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Document Type')
                    ->options(Legal::getTypes())
                    ->multiple(),

                TernaryFilter::make('is_enabled')
                    ->label('Enabled')
                    ->boolean()
                    ->trueLabel('Enabled only')
                    ->falseLabel('Disabled only')
                    ->native(false),

                TernaryFilter::make('is_required')
                    ->label('Required')
                    ->boolean()
                    ->trueLabel('Required only')
                    ->falseLabel('Optional only')
                    ->native(false),

                TernaryFilter::make('published_at')
                    ->label('Published')
                    ->nullable()
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('published_at'),
                        false: fn (Builder $query) => $query->whereNull('published_at'),
                        blank: fn (Builder $query) => $query,
                    )
                    ->trueLabel('Published only')
                    ->falseLabel('Draft only')
                    ->native(false),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc')
            ->reorderable('sort_order')
            ->emptyStateHeading('No legal documents')
            ->emptyStateDescription('Create your first legal document to get started.')
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Create Legal Document')
                    ->icon('heroicon-o-plus'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TranslationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLegals::route('/'),
            'create' => Pages\CreateLegal::route('/create'),
            'view' => Pages\ViewLegal::route('/{record}'),
            'edit' => Pages\EditLegal::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'success';
    }
}
