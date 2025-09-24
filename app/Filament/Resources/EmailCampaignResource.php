<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\EmailCampaignResource\Pages;
use App\Models\EmailCampaign;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

final class EmailCampaignResource extends Resource
{
    protected static ?string $model = EmailCampaign::class;

    protected static ?int $navigationSort = 4;

    public static function getNavigationIcon(): BackedEnum|string|null
    {
        return 'heroicon-o-envelope';
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.email_campaigns.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.email_campaigns.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.email_campaigns.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('admin.email_campaigns.basic_information'))
                ->description(__('admin.email_campaigns.basic_information_description'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('admin.email_campaigns.name'))
                        ->required()
                        ->maxLength(255),
                    Textarea::make('description')
                        ->label(__('admin.email_campaigns.description'))
                        ->rows(3)
                        ->columnSpanFull(),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('subject')
                                ->label(__('admin.email_campaigns.subject'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('from_email')
                                ->label(__('admin.email_campaigns.from_email'))
                                ->email()
                                ->required()
                                ->maxLength(255),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('from_name')
                                ->label(__('admin.email_campaigns.from_name'))
                                ->maxLength(255),
                            TextInput::make('reply_to')
                                ->label(__('admin.email_campaigns.reply_to'))
                                ->email()
                                ->maxLength(255),
                        ]),
                    Grid::make(2)
                        ->schema([
                            DateTimePicker::make('scheduled_at')
                                ->label(__('admin.email_campaigns.scheduled_at')),
                            Toggle::make('is_active')
                                ->label(__('admin.email_campaigns.is_active'))
                                ->default(true),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.email_campaigns.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subject')
                    ->label(__('admin.email_campaigns.subject'))
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    }),
                TextColumn::make('from_email')
                    ->label(__('admin.email_campaigns.from_email'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('from_name')
                    ->label(__('admin.email_campaigns.from_name'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label(__('admin.email_campaigns.is_active'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('scheduled_at')
                    ->label(__('admin.email_campaigns.scheduled_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin.common.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('admin.email_campaigns.is_active'))
                    ->boolean()
                    ->trueLabel(__('admin.common.active'))
                    ->falseLabel(__('admin.common.inactive'))
                    ->native(false),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailCampaigns::route('/'),
            'create' => Pages\CreateEmailCampaign::route('/create'),
            'view' => Pages\ViewEmailCampaign::route('/{record}'),
            'edit' => Pages\EditEmailCampaign::route('/{record}/edit'),
        ];
    }
}
