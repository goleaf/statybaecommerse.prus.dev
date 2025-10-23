<?php

declare(strict_types=1);

namespace App\Filament\Resources\LegalResource\RelationManagers;

use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

use Filament\Schemas\Schema;
class TranslationsRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'translations';

    protected static ?string $title = 'Translations';

    protected static ?string $modelLabel = 'Translation';

    protected static ?string $pluralModelLabel = 'Translations';

    public function form(Schema $form): Schema
    {
        return $schema
            ->schema([
                Section::make('Translation Details')
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
                                    ->preload()
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Select the language for this translation'),

                                TextInput::make('slug')
                                    ->label('URL Slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->helperText('URL-friendly version of the title'),
                            ]),

                        TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->helperText('The title of this legal document'),

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
                                'grid',
                                'gridDelete',
                                'textColor',
                            ])
                            ->textColors([
                                'primary' => '#1d4ed8',
                                'emerald' => '#047857',
                                'amber'   => '#f59e0b',
                                'slate'   => '#475569',
                            ])
                            ->helperText('The main content of this legal document'),

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
                    ->columns(1),
            ]);
    }

    public function table(Table $table): Table
    {
        // Configure the relation manager table to satisfy Filament v4's return type requirements.
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                BadgeColumn::make('locale')
                    ->label('Language')
                    ->colors([
                        'success' => 'lt',
                        'info'    => 'en',
                        'warning' => 'ru',
                        'gray'    => 'de',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'lt'    => 'Lithuanian',
                        'en'    => 'English',
                        'ru'    => 'Russian',
                        'de'    => 'German',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }

                        return $state;
                    }),

                TextColumn::make('slug')
                    ->label('URL Slug')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Slug copied')
                    ->copyMessageDuration(1500)
                    ->limit(30),

                TextColumn::make('seo_title')
                    ->label('SEO Title')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

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
                //
            ])
            ->headerActions([
                RelationManagerRepeaterAction::make()
                    ->label('Quick edit ' . $this->getPluralModelLabel())
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading('Edit ' . $this->getPluralModelLabel())
                    ->modalWidth('5xl')
                    ->configureRepeater(function (Repeater $repeater): Repeater {
                        // Provide a quick-edit modal for managing records inline.
                        return $repeater->schema($this->getQuickEditSchema());
                    }),
                CreateAction::make()
                    ->label('Add Translation')
                    ->icon('heroicon-o-plus'),
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
            ->defaultSort('locale', 'asc')
            ->emptyStateHeading('No translations')
            ->emptyStateDescription('Add translations for this legal document.')
            ->emptyStateIcon('heroicon-o-language')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Add Translation')
                    ->icon('heroicon-o-plus'),
            ]);
    }
}