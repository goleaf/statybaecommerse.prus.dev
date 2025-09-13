<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignResource\Pages;
use App\Models\Campaign;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class CampaignResource extends Resource
{
    use Translatable;

    protected static ?string $model = Campaign::class;

    /**
     * @var string|\BackedEnum|null
     */
    protected static $navigationIcon = 'heroicon-o-megaphone';

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.marketing');
    }

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = null;

    public static function getNavigationLabel(): string
    {
        return __('campaigns.navigation.campaigns');
    }

    public static function getModelLabel(): string
    {
        return __('campaigns.models.campaign');
    }

    public static function getPluralModelLabel(): string
    {
        return __('campaigns.models.campaigns');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('campaigns.sections.basic_information'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('campaigns.fields.name'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
                        Forms\Components\TextInput::make('slug')
                            ->label(__('campaigns.fields.slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(Campaign::class, 'slug', ignoreRecord: true)
                            ->rules(['alpha_dash']),
                        Forms\Components\Textarea::make('description')
                            ->label(__('campaigns.fields.description'))
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('type')
                            ->label(__('campaigns.fields.type'))
                            ->options([
                                'email' => __('campaigns.types.email'),
                                'sms' => __('campaigns.types.sms'),
                                'push' => __('campaigns.types.push'),
                                'banner' => __('campaigns.types.banner'),
                                'popup' => __('campaigns.types.popup'),
                                'social' => __('campaigns.types.social'),
                            ])
                            ->required()
                            ->live(),
                        Forms\Components\Select::make('status')
                            ->label(__('campaigns.fields.status'))
                            ->options([
                                'draft' => __('campaigns.status.draft'),
                                'scheduled' => __('campaigns.status.scheduled'),
                                'active' => __('campaigns.status.active'),
                                'paused' => __('campaigns.status.paused'),
                                'completed' => __('campaigns.status.completed'),
                                'cancelled' => __('campaigns.status.cancelled'),
                            ])
                            ->required()
                            ->default('draft'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make(__('campaigns.sections.campaign_settings'))
                    ->schema([
                        Forms\Components\DateTimePicker::make('start_date')
                            ->label(__('campaigns.fields.start_date'))
                            ->required(),
                        Forms\Components\DateTimePicker::make('end_date')
                            ->label(__('campaigns.fields.end_date'))
                            ->after('start_date'),
                        Forms\Components\TextInput::make('budget')
                            ->label(__('campaigns.fields.budget'))
                            ->numeric()
                            ->prefix('€')
                            ->minValue(0),
                        Forms\Components\TextInput::make('budget_limit')
                            ->label(__('campaigns.fields.budget_limit'))
                            ->numeric()
                            ->prefix('€')
                            ->minValue(0),
                        Forms\Components\Select::make('channel_id')
                            ->label(__('campaigns.fields.channel_id'))
                            ->relationship('channel', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('zone_id')
                            ->label(__('campaigns.fields.zone_id'))
                            ->relationship('zone', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('display_priority')
                            ->label(__('campaigns.fields.display_priority'))
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('max_uses')
                            ->label(__('campaigns.fields.max_uses'))
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->columns(2),
                Forms\Components\Section::make(__('campaigns.sections.content'))
                    ->schema([
                        Forms\Components\TextInput::make('subject')
                            ->label(__('campaigns.fields.subject'))
                            ->maxLength(255),
                        Forms\Components\RichEditor::make('content')
                            ->label(__('campaigns.fields.content'))
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('cta_text')
                            ->label(__('campaigns.fields.cta_text'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('cta_url')
                            ->label(__('campaigns.fields.cta_url'))
                            ->url()
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Forms\Components\Section::make(__('campaigns.sections.media'))
                    ->schema([
                        Forms\Components\FileUpload::make('banner_image')
                            ->label(__('campaigns.fields.banner_image'))
                            ->image()
                            ->directory('campaigns')
                            ->visibility('public'),
                        Forms\Components\TextInput::make('banner_alt_text')
                            ->label(__('campaigns.fields.banner_alt_text'))
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Forms\Components\Section::make(__('campaigns.sections.targeting'))
                    ->schema([
                        Forms\Components\TextInput::make('target_audience')
                            ->label(__('campaigns.fields.target_audience'))
                            ->maxLength(255),
                        Forms\Components\Select::make('target_categories')
                            ->label(__('campaigns.fields.target_categories'))
                            ->relationship('targetCategories', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('target_products')
                            ->label(__('campaigns.fields.target_products'))
                            ->relationship('targetProducts', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('target_customer_groups')
                            ->label(__('campaigns.fields.target_customer_groups'))
                            ->relationship('targetCustomerGroups', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make(__('campaigns.sections.automation'))
                    ->schema([
                        Forms\Components\Toggle::make('is_featured')
                            ->label(__('campaigns.fields.is_featured'))
                            ->default(false),
                        Forms\Components\Toggle::make('send_notifications')
                            ->label(__('campaigns.fields.send_notifications'))
                            ->default(true),
                        Forms\Components\Toggle::make('track_conversions')
                            ->label(__('campaigns.fields.track_conversions'))
                            ->default(true),
                        Forms\Components\Toggle::make('auto_start')
                            ->label(__('campaigns.fields.auto_start'))
                            ->default(false),
                        Forms\Components\Toggle::make('auto_end')
                            ->label(__('campaigns.fields.auto_end'))
                            ->default(false),
                        Forms\Components\Toggle::make('auto_pause_on_budget')
                            ->label(__('campaigns.fields.auto_pause_on_budget'))
                            ->default(false),
                    ])
                    ->columns(3),
                Forms\Components\Section::make(__('campaigns.sections.seo'))
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->label(__('campaigns.fields.meta_title'))
                            ->maxLength(255),
                        Forms\Components\Textarea::make('meta_description')
                            ->label(__('campaigns.fields.meta_description'))
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('social_media_ready')
                            ->label(__('campaigns.fields.social_media_ready'))
                            ->default(false),
                    ])
                    ->columns(2),
                Forms\Components\Section::make(__('campaigns.sections.tracking'))
                    ->schema([
                        Forms\Components\TextInput::make('total_views')
                            ->label(__('campaigns.fields.total_views'))
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('total_clicks')
                            ->label(__('campaigns.fields.total_clicks'))
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('total_conversions')
                            ->label(__('campaigns.fields.total_conversions'))
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('total_revenue')
                            ->label(__('campaigns.fields.total_revenue'))
                            ->numeric()
                            ->prefix('€')
                            ->disabled(),
                        Forms\Components\TextInput::make('conversion_rate')
                            ->label(__('campaigns.fields.conversion_rate'))
                            ->numeric()
                            ->suffix('%')
                            ->disabled(),
                    ])
                    ->columns(3)
                    ->visible(fn ($record) => $record !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('campaigns.fields.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('campaigns.fields.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'email' => 'info',
                        'sms' => 'warning',
                        'push' => 'success',
                        'banner' => 'primary',
                        'popup' => 'secondary',
                        'social' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('campaigns.fields.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'scheduled' => 'warning',
                        'paused' => 'secondary',
                        'completed' => 'info',
                        'cancelled' => 'danger',
                        'draft' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('start_date')
                    ->label(__('campaigns.fields.start_date'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label(__('campaigns.fields.end_date'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('budget')
                    ->label(__('campaigns.fields.budget'))
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_views')
                    ->label(__('campaigns.fields.total_views'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_clicks')
                    ->label(__('campaigns.fields.total_clicks'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_conversions')
                    ->label(__('campaigns.fields.total_conversions'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('conversion_rate')
                    ->label(__('campaigns.fields.conversion_rate'))
                    ->numeric()
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label(__('campaigns.fields.is_featured'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('channel.name')
                    ->label(__('campaigns.fields.channel_id'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('campaigns.fields.created_at'))
                    ->dateTime()
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
                        'completed' => __('campaigns.status.completed'),
                        'cancelled' => __('campaigns.status.cancelled'),
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('campaigns.fields.type'))
                    ->options([
                        'email' => __('campaigns.types.email'),
                        'sms' => __('campaigns.types.sms'),
                        'push' => __('campaigns.types.push'),
                        'banner' => __('campaigns.types.banner'),
                        'popup' => __('campaigns.types.popup'),
                        'social' => __('campaigns.types.social'),
                    ]),
                Tables\Filters\SelectFilter::make('channel_id')
                    ->label(__('campaigns.fields.channel_id'))
                    ->relationship('channel', 'name'),
                Tables\Filters\SelectFilter::make('zone_id')
                    ->label(__('campaigns.fields.zone_id'))
                    ->relationship('zone', 'name'),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label(__('campaigns.fields.is_featured')),
                Tables\Filters\Filter::make('active')
                    ->label(__('campaigns.filters.active'))
                    ->query(fn (Builder $query): Builder => $query->active()),
                Tables\Filters\Filter::make('scheduled')
                    ->label(__('campaigns.filters.scheduled'))
                    ->query(fn (Builder $query): Builder => $query->scheduled()),
                Tables\Filters\Filter::make('expired')
                    ->label(__('campaigns.filters.expired'))
                    ->query(fn (Builder $query): Builder => $query->expired()),
                Tables\Filters\Filter::make('featured')
                    ->label(__('campaigns.filters.featured'))
                    ->query(fn (Builder $query): Builder => $query->featured()),
                Tables\Filters\Filter::make('created_from')
                    ->label(__('campaigns.filters.created_from'))
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label(__('campaigns.filters.created_from')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            );
                    }),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('activate')
                    ->label(__('campaigns.actions.activate'))
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->action(fn (Campaign $record) => $record->update(['status' => 'active']))
                    ->requiresConfirmation()
                    ->visible(fn (Campaign $record): bool => $record->status !== 'active'),
                Tables\Actions\Action::make('pause')
                    ->label(__('campaigns.actions.pause'))
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->action(fn (Campaign $record) => $record->update(['status' => 'paused']))
                    ->requiresConfirmation()
                    ->visible(fn (Campaign $record): bool => $record->status === 'active'),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label(__('campaigns.actions.activate'))
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['status' => 'active']))
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('pause')
                        ->label(__('campaigns.actions.pause'))
                        ->icon('heroicon-o-pause')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['status' => 'paused']))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession();
    }

    public static function getWidgets(): array
    {
        return [
            Widgets\CampaignStatsWidget::class,
            Widgets\CampaignPerformanceWidget::class,
            Widgets\CampaignAnalyticsWidget::class,
            Widgets\CampaignTypeChartWidget::class,
            Widgets\CampaignGrowthChartWidget::class,
        ];
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

    // Authorization methods
    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('administrator') ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view campaigns') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create campaigns') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update campaigns') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete campaigns') ?? false;
    }
}
