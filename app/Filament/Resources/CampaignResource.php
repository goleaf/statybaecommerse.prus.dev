<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignResource\Pages;
use App\Filament\Resources\CampaignResource\RelationManagers\TranslationsRelationManager;
use App\Models\Campaign;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

final class CampaignResource extends Resource
{
    protected static ?string $model = Campaign::class;

    protected static ?int $navigationSort = 7;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return __('campaigns.navigation.campaigns');
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Marketing';
    }

    public static function getPluralModelLabel(): string
    {
        return __('campaigns.models.campaigns');
    }

    public static function getModelLabel(): string
    {
        return __('campaigns.models.campaign');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            SchemaSection::make(__('campaigns.sections.basic_information'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(self::label('campaigns.fields.name', 'Name'))
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug((string) $state)) : null),
                            TextInput::make('slug')
                                ->label(self::label('campaigns.fields.slug', 'Slug'))
                                ->unique(ignoreRecord: true),
                        ]),
                    Select::make('channel_id')
                        ->label(self::label('campaigns.fields.channel', 'Channel'))
                        ->relationship('channel', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable(),
                    Select::make('status')
                        ->label(self::label('campaigns.fields.status', 'Status'))
                        ->options([
                            'draft' => self::label('campaigns.status.draft', 'Draft'),
                            'active' => self::label('campaigns.status.active', 'Active'),
                            'scheduled' => self::label('campaigns.status.scheduled', 'Scheduled'),
                            'paused' => self::label('campaigns.status.paused', 'Paused'),
                            'completed' => self::label('campaigns.status.completed', 'Completed'),
                            'cancelled' => self::label('campaigns.status.cancelled', 'Cancelled'),
                        ])
                        ->default('draft')
                        ->required(),
                    Toggle::make('is_active')
                        ->label(self::label('campaigns.fields.is_active', 'Active'))
                        ->default(true),
                    Toggle::make('is_featured')
                        ->label(self::label('campaigns.fields.is_featured', 'Featured'))
                        ->default(false),
                    Toggle::make('social_media_ready')
                        ->label(self::label('campaigns.fields.social_media_ready', 'Social media ready'))
                        ->default(false),
                ]),
            SchemaSection::make(__('campaigns.sections.campaign_settings'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            DateTimePicker::make('starts_at')
                                ->label(self::label('campaigns.fields.start_date', 'Start date'))
                                ->seconds(false),
                            DateTimePicker::make('ends_at')
                                ->label(self::label('campaigns.fields.end_date', 'End date'))
                                ->seconds(false),
                            TextInput::make('max_uses')
                                ->label(self::label('campaigns.fields.max_uses', 'Max uses'))
                                ->numeric()
                                ->minValue(0)
                                ->default(0),
                            TextInput::make('budget_limit')
                                ->label(self::label('campaigns.fields.budget_limit', 'Budget limit'))
                                ->step(0.01)
                                ->prefix('€'),
                        ]),
                    SchemaGrid::make(3)
                        ->schema([
                            Toggle::make('send_notifications')
                                ->label(self::label('campaigns.fields.send_notifications', 'Send notifications'))
                                ->default(false),
                            Toggle::make('track_conversions')
                                ->label(self::label('campaigns.fields.track_conversions', 'Track conversions'))
                                ->default(false),
                            Toggle::make('auto_pause_on_budget')
                                ->label(self::label('campaigns.fields.auto_pause_on_budget', 'Pause on budget limit'))
                                ->default(false),
                        ]),
                ]),
            SchemaSection::make(__('campaigns.sections.targeting'))
                ->schema([
                    Select::make('targetCategories')
                        ->label(self::label('campaigns.fields.target_categories', 'Target categories'))
                        ->relationship('targetCategories', 'name')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->columnSpanFull(),
                    Select::make('targetProducts')
                        ->label(self::label('campaigns.fields.target_products', 'Target products'))
                        ->relationship('targetProducts', 'name')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->columnSpanFull(),
                    Select::make('targetCustomerGroups')
                        ->label(self::label('campaigns.fields.target_customer_groups', 'Target customer groups'))
                        ->relationship('targetCustomerGroups', 'name')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->columnSpanFull(),
                    Select::make('discounts')
                        ->label(self::label('campaigns.fields.discounts', 'Discounts'))
                        ->relationship('discounts', 'name')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->columnSpanFull(),
                ]),
            SchemaSection::make(__('campaigns.sections.content'))
                ->schema([
                    Textarea::make('description')
                        ->label(self::label('campaigns.fields.description', 'Description'))
                        ->rows(4)
                        ->columnSpanFull(),
                    SchemaGrid::make(2)
                        ->schema([
                            TextInput::make('cta_text')
                                ->label(self::label('campaigns.fields.cta_text', 'CTA text'))
                                ->maxLength(120),
                            TextInput::make('cta_url')
                                ->label(self::label('campaigns.fields.cta_url', 'CTA URL'))
                                ->url()
                                ->maxLength(255),
                            TextInput::make('banner_image')
                                ->label(self::label('campaigns.fields.banner', 'Banner image'))
                                ->maxLength(255),
                            TextInput::make('banner_alt_text')
                                ->label(self::label('campaigns.fields.banner_alt_text', 'Banner alt text'))
                                ->maxLength(255),
                            TextInput::make('display_priority')
                                ->label(self::label('campaigns.fields.display_priority', 'Display priority'))
                                ->numeric()
                                ->default(0),
                        ]),
                    SchemaGrid::make(2)
                        ->schema([
                            Toggle::make('auto_start')
                                ->label(self::label('campaigns.fields.auto_start', 'Auto start'))
                                ->default(false),
                            Toggle::make('auto_end')
                                ->label(self::label('campaigns.fields.auto_end', 'Auto end'))
                                ->default(false),
                        ]),
                ]),
            SchemaSection::make(__('campaigns.sections.seo'))
                ->schema([
                    TextInput::make('meta_title')
                        ->label(self::label('campaigns.fields.meta_title', 'Meta title'))
                        ->maxLength(255),
                    Textarea::make('meta_description')
                        ->label(self::label('campaigns.fields.meta_description', 'Meta description'))
                        ->rows(3)
                        ->maxLength(500),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(self::label('campaigns.fields.name', 'Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(self::label('campaigns.fields.status', 'Status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::label('campaigns.status.'.$state, Str::headline($state)))
                    ->colors([
                        'primary' => fn (string $state): bool => in_array($state, ['draft', 'scheduled']),
                        'success' => fn (string $state): bool => $state === 'active',
                        'warning' => fn (string $state): bool => $state === 'paused',
                        'info' => fn (string $state): bool => $state === 'completed',
                        'danger' => fn (string $state): bool => $state === 'cancelled',
                    ]),
                IconColumn::make('is_active')
                    ->label(self::label('campaigns.fields.is_active', 'Active'))
                    ->boolean(),
                TextColumn::make('channel.name')
                    ->label(self::label('campaigns.fields.channel', 'Channel'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('starts_at')
                    ->label(self::label('campaigns.fields.start_date', 'Start date'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ends_at')
                    ->label(self::label('campaigns.fields.end_date', 'End date'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total_views')
                    ->label(self::label('campaigns.fields.total_views', 'Total views'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('conversion_rate')
                    ->label(self::label('campaigns.fields.conversion_rate', 'Conversion rate'))
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2).'%')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('translations_count')
                    ->label(self::label('translations.title', 'Translations'))
                    ->counts('translations')
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(self::label('campaigns.fields.updated_at', 'Updated'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => self::label('campaigns.status.draft', 'Draft'),
                        'active' => self::label('campaigns.status.active', 'Active'),
                        'scheduled' => self::label('campaigns.status.scheduled', 'Scheduled'),
                        'paused' => self::label('campaigns.status.paused', 'Paused'),
                        'completed' => self::label('campaigns.status.completed', 'Completed'),
                        'cancelled' => self::label('campaigns.status.cancelled', 'Cancelled'),
                    ]),
                SelectFilter::make('channel_id')
                    ->relationship('channel', 'name'),
                TernaryFilter::make('is_active')
                    ->trueLabel(self::label('campaigns.filters.active', 'Active only'))
                    ->falseLabel(self::label('campaigns.filters.inactive', 'Inactive only'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('starts_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            TranslationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCampaigns::route('/'),
            'create' => Pages\CreateCampaign::route('/create'),
            'view' => Pages\ViewCampaign::route('/{record}'),
            'edit' => Pages\EditCampaign::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('translations');
    }

    private static function label(string $key, string $fallback): string
    {
        $translated = __($key);

        return $translated === $key ? $fallback : $translated;
    }
}
