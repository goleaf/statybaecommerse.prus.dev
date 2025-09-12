<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignResource\Pages;
use App\Models\Campaign;
use App\Models\Channel;
use App\Models\Zone;
use App\Models\Category;
use App\Models\Product;
use App\Models\CustomerGroup;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\FontWeight;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\ImageEntry;

final class CampaignResource extends Resource
{
    protected static ?string $model = Campaign::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-megaphone';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.marketing');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.campaigns');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('Campaign Details')
                    ->tabs([
                        Tabs\Tab::make(__('campaigns.tabs.basic_info'))
                            ->schema([
                                Section::make(__('campaigns.sections.basic_information'))
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label(__('campaigns.fields.name'))
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => 
                                                $operation === 'create' ? $set('slug', \Str::slug($state)) : null
                                            ),

                                        Forms\Components\TextInput::make('slug')
                                            ->label(__('campaigns.fields.slug'))
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(Campaign::class, 'slug', ignoreRecord: true),

                                        Forms\Components\Textarea::make('description')
                                            ->label(__('campaigns.fields.description'))
                                            ->rows(3)
                                            ->columnSpanFull(),

                                        Forms\Components\Select::make('status')
                                            ->label(__('campaigns.fields.status'))
                                            ->options([
                                                'draft' => __('campaigns.status.draft'),
                                                'scheduled' => __('campaigns.status.scheduled'),
                                                'active' => __('campaigns.status.active'),
                                                'paused' => __('campaigns.status.paused'),
                                                'expired' => __('campaigns.status.expired'),
                                            ])
                                            ->default('draft')
                                            ->required(),

                                        Forms\Components\DateTimePicker::make('starts_at')
                                            ->label(__('campaigns.fields.starts_at'))
                                            ->native(false)
                                            ->displayFormat('d/m/Y H:i'),

                                        Forms\Components\DateTimePicker::make('ends_at')
                                            ->label(__('campaigns.fields.ends_at'))
                                            ->native(false)
                                            ->displayFormat('d/m/Y H:i')
                                            ->after('starts_at'),
                                    ])
                                    ->columns(2),

                                Section::make(__('campaigns.sections.channel_zone'))
                                    ->schema([
                                        Forms\Components\Select::make('channel_id')
                                            ->label(__('campaigns.fields.channel'))
                                            ->options(Channel::pluck('name', 'id'))
                                            ->searchable()
                                            ->preload(),

                                        Forms\Components\Select::make('zone_id')
                                            ->label(__('campaigns.fields.zone'))
                                            ->options(Zone::pluck('name', 'id'))
                                            ->searchable()
                                            ->preload(),
                                    ])
                                    ->columns(2),
                            ]),

                        Tabs\Tab::make(__('campaigns.tabs.display_settings'))
                            ->schema([
                                Section::make(__('campaigns.sections.display_settings'))
                                    ->schema([
                                        Forms\Components\Toggle::make('is_featured')
                                            ->label(__('campaigns.fields.is_featured'))
                                            ->default(false),

                                        Forms\Components\TextInput::make('display_priority')
                                            ->label(__('campaigns.fields.display_priority'))
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->maxValue(10),

                                        Forms\Components\FileUpload::make('banner_image')
                                            ->label(__('campaigns.fields.banner_image'))
                                            ->image()
                                            ->directory('campaigns')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->imageEditorAspectRatios([
                                                '16:9',
                                                '4:3',
                                                '1:1',
                                            ]),

                                        Forms\Components\TextInput::make('banner_alt_text')
                                            ->label(__('campaigns.fields.banner_alt_text'))
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('cta_text')
                                            ->label(__('campaigns.fields.cta_text'))
                                            ->maxLength(255)
                                            ->placeholder('Shop Now'),

                                        Forms\Components\TextInput::make('cta_url')
                                            ->label(__('campaigns.fields.cta_url'))
                                            ->url()
                                            ->maxLength(255),
                                    ])
                                    ->columns(2),

                                Section::make(__('campaigns.sections.seo_settings'))
                                    ->schema([
                                        Forms\Components\TextInput::make('meta_title')
                                            ->label(__('campaigns.fields.meta_title'))
                                            ->maxLength(255),

                                        Forms\Components\Textarea::make('meta_description')
                                            ->label(__('campaigns.fields.meta_description'))
                                            ->rows(3)
                                            ->maxLength(500),

                                        Forms\Components\Toggle::make('social_media_ready')
                                            ->label(__('campaigns.fields.social_media_ready'))
                                            ->default(false),
                                    ])
                                    ->columns(1),
                            ]),

                        Tabs\Tab::make(__('campaigns.tabs.targeting'))
                            ->schema([
                                Section::make(__('campaigns.sections.audience_targeting'))
                                    ->schema([
                                        Forms\Components\KeyValue::make('target_audience')
                                            ->label(__('campaigns.fields.target_audience'))
                                            ->keyLabel(__('campaigns.fields.audience_criteria'))
                                            ->valueLabel(__('campaigns.fields.audience_value'))
                                            ->addActionLabel(__('campaigns.actions.add_criteria')),

                                        Forms\Components\Select::make('target_categories')
                                            ->label(__('campaigns.fields.target_categories'))
                                            ->options(Category::pluck('name', 'id'))
                                            ->multiple()
                                            ->searchable()
                                            ->preload(),

                                        Forms\Components\Select::make('target_products')
                                            ->label(__('campaigns.fields.target_products'))
                                            ->options(Product::pluck('name', 'id'))
                                            ->multiple()
                                            ->searchable()
                                            ->preload(),

                                        Forms\Components\Select::make('target_customer_groups')
                                            ->label(__('campaigns.fields.target_customer_groups'))
                                            ->options(CustomerGroup::pluck('name', 'id'))
                                            ->multiple()
                                            ->searchable()
                                            ->preload(),
                                    ])
                                    ->columns(2),
                            ]),

                        Tabs\Tab::make(__('campaigns.tabs.budget_limits'))
                            ->schema([
                                Section::make(__('campaigns.sections.budget_settings'))
                                    ->schema([
                                        Forms\Components\TextInput::make('budget_limit')
                                            ->label(__('campaigns.fields.budget_limit'))
                                            ->numeric()
                                            ->prefix('€')
                                            ->step(0.01),

                                        Forms\Components\TextInput::make('max_uses')
                                            ->label(__('campaigns.fields.max_uses'))
                                            ->numeric()
                                            ->minValue(1),

                                        Forms\Components\Toggle::make('auto_pause_on_budget')
                                            ->label(__('campaigns.fields.auto_pause_on_budget'))
                                            ->default(false),
                                    ])
                                    ->columns(2),

                                Section::make(__('campaigns.sections.automation'))
                                    ->schema([
                                        Forms\Components\Toggle::make('auto_start')
                                            ->label(__('campaigns.fields.auto_start'))
                                            ->default(false),

                                        Forms\Components\Toggle::make('auto_end')
                                            ->label(__('campaigns.fields.auto_end'))
                                            ->default(false),

                                        Forms\Components\Toggle::make('send_notifications')
                                            ->label(__('campaigns.fields.send_notifications'))
                                            ->default(true),

                                        Forms\Components\Toggle::make('track_conversions')
                                            ->label(__('campaigns.fields.track_conversions'))
                                            ->default(true),
                                    ])
                                    ->columns(2),
                            ]),

                        Tabs\Tab::make(__('campaigns.tabs.analytics'))
                            ->schema([
                                Section::make(__('campaigns.sections.performance_metrics'))
                                    ->schema([
                                        Forms\Components\TextInput::make('total_views')
                                            ->label(__('campaigns.fields.total_views'))
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated(false),

                                        Forms\Components\TextInput::make('total_clicks')
                                            ->label(__('campaigns.fields.total_clicks'))
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated(false),

                                        Forms\Components\TextInput::make('total_conversions')
                                            ->label(__('campaigns.fields.total_conversions'))
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated(false),

                                        Forms\Components\TextInput::make('total_revenue')
                                            ->label(__('campaigns.fields.total_revenue'))
                                            ->numeric()
                                            ->prefix('€')
                                            ->disabled()
                                            ->dehydrated(false),

                                        Forms\Components\TextInput::make('conversion_rate')
                                            ->label(__('campaigns.fields.conversion_rate'))
                                            ->numeric()
                                            ->suffix('%')
                                            ->disabled()
                                            ->dehydrated(false),
                                    ])
                                    ->columns(2),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('banner_image')
                    ->label(__('campaigns.fields.banner_image'))
                    ->circular()
                    ->size(40),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('campaigns.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('campaigns.fields.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'scheduled' => 'warning',
                        'paused' => 'secondary',
                        'expired' => 'danger',
                        'draft' => 'info',
                        default => 'secondary',
                    }),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label(__('campaigns.fields.is_featured'))
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('display_priority')
                    ->label(__('campaigns.fields.display_priority'))
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label(__('campaigns.fields.starts_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('ends_at')
                    ->label(__('campaigns.fields.ends_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_views')
                    ->label(__('campaigns.fields.total_views'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_clicks')
                    ->label(__('campaigns.fields.total_clicks'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_conversions')
                    ->label(__('campaigns.fields.total_conversions'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_revenue')
                    ->label(__('campaigns.fields.total_revenue'))
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('conversion_rate')
                    ->label(__('campaigns.fields.conversion_rate'))
                    ->suffix('%')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('campaigns.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('campaigns.fields.status'))
                    ->options([
                        'draft' => __('campaigns.status.draft'),
                        'scheduled' => __('campaigns.status.scheduled'),
                        'active' => __('campaigns.status.active'),
                        'paused' => __('campaigns.status.paused'),
                        'expired' => __('campaigns.status.expired'),
                    ]),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label(__('campaigns.fields.is_featured')),

                Tables\Filters\SelectFilter::make('channel_id')
                    ->label(__('campaigns.fields.channel'))
                    ->relationship('channel', 'name'),

                Tables\Filters\SelectFilter::make('zone_id')
                    ->label(__('campaigns.fields.zone'))
                    ->relationship('zone', 'name'),

                Tables\Filters\Filter::make('active_campaigns')
                    ->label(__('campaigns.filters.active_campaigns'))
                    ->query(fn (Builder $query): Builder => $query->active()),

                Tables\Filters\Filter::make('featured_campaigns')
                    ->label(__('campaigns.filters.featured_campaigns'))
                    ->query(fn (Builder $query): Builder => $query->featured()),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('display_priority', 'desc');
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
            'index' => Pages\ListCampaigns::route('/'),
            'create' => Pages\CreateCampaign::route('/create'),
            'view' => Pages\ViewCampaign::route('/{record}'),
            'edit' => Pages\EditCampaign::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfoSection::make(__('campaigns.sections.basic_information'))
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('campaigns.fields.name'))
                            ->weight(FontWeight::Bold)
                            ->size(TextEntry\TextEntrySize::Large),

                        TextEntry::make('slug')
                            ->label(__('campaigns.fields.slug'))
                            ->copyable(),

                        TextEntry::make('status')
                            ->label(__('campaigns.fields.status'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'active' => 'success',
                                'scheduled' => 'warning',
                                'paused' => 'secondary',
                                'expired' => 'danger',
                                'draft' => 'info',
                                default => 'secondary',
                            }),

                        TextEntry::make('starts_at')
                            ->label(__('campaigns.fields.starts_at'))
                            ->dateTime('d/m/Y H:i'),

                        TextEntry::make('ends_at')
                            ->label(__('campaigns.fields.ends_at'))
                            ->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(2),

                InfoSection::make(__('campaigns.sections.performance_metrics'))
                    ->schema([
                        TextEntry::make('total_views')
                            ->label(__('campaigns.fields.total_views'))
                            ->numeric(),

                        TextEntry::make('total_clicks')
                            ->label(__('campaigns.fields.total_clicks'))
                            ->numeric(),

                        TextEntry::make('total_conversions')
                            ->label(__('campaigns.fields.total_conversions'))
                            ->numeric(),

                        TextEntry::make('total_revenue')
                            ->label(__('campaigns.fields.total_revenue'))
                            ->money('EUR'),

                        TextEntry::make('conversion_rate')
                            ->label(__('campaigns.fields.conversion_rate'))
                            ->suffix('%'),

                        TextEntry::make('budget_limit')
                            ->label(__('campaigns.fields.budget_limit'))
                            ->money('EUR'),
                    ])
                    ->columns(3),

                InfoSection::make(__('campaigns.sections.banner_preview'))
                    ->schema([
                        ImageEntry::make('banner_image')
                            ->label(__('campaigns.fields.banner_image'))
                            ->height(200),
                    ])
                    ->visible(fn ($record) => $record->banner_image),
            ]);
    }
}