<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignResource\Pages;
use App\Models\Campaign;
use App\Models\Channel;
use App\Models\Zone;
use App\Models\Category;
use App\Models\Product;
use App\Models\CustomerGroup;
use BackedEnum;
use Filament\Forms;
use UnitEnum;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class CampaignResource extends Resource
{
    protected static ?string $model = Campaign::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('campaigns.basic_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name_lt')
                                    ->label(__('campaigns.name_lt'))
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('name_en')
                                    ->label(__('campaigns.name_en'))
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('slug_lt')
                                    ->label(__('campaigns.slug_lt'))
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                                TextInput::make('slug_en')
                                    ->label(__('campaigns.slug_en'))
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                            ]),
                        RichEditor::make('description_lt')
                            ->label(__('campaigns.description_lt'))
                            ->columnSpanFull(),
                        RichEditor::make('description_en')
                            ->label(__('campaigns.description_en'))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('campaigns.scheduling'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('starts_at')
                                    ->label(__('campaigns.starts_at'))
                                    ->displayFormat('d/m/Y H:i'),
                                DateTimePicker::make('ends_at')
                                    ->label(__('campaigns.ends_at'))
                                    ->displayFormat('d/m/Y H:i'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                Toggle::make('auto_start')
                                    ->label(__('campaigns.auto_start')),
                                Toggle::make('auto_end')
                                    ->label(__('campaigns.auto_end')),
                                Toggle::make('auto_pause_on_budget')
                                    ->label(__('campaigns.auto_pause_on_budget')),
                            ]),
                    ])
                    ->columns(2),

                Section::make(__('campaigns.targeting'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('channel_id')
                                    ->label(__('campaigns.channel'))
                                    ->options(Channel::all()->pluck('name', 'id'))
                                    ->searchable(),
                                Select::make('zone_id')
                                    ->label(__('campaigns.zone'))
                                    ->options(Zone::all()->pluck('name', 'id'))
                                    ->searchable(),
                            ]),
                        Select::make('target_categories')
                            ->label(__('campaigns.target_categories'))
                            ->multiple()
                            ->options(Category::all()->pluck('name', 'id'))
                            ->searchable()
                            ->columnSpanFull(),
                        Select::make('target_products')
                            ->label(__('campaigns.target_products'))
                            ->multiple()
                            ->options(Product::all()->pluck('name', 'id'))
                            ->searchable()
                            ->columnSpanFull(),
                        Select::make('target_customer_groups')
                            ->label(__('campaigns.target_customer_groups'))
                            ->multiple()
                            ->options(CustomerGroup::all()->pluck('name', 'id'))
                            ->searchable()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('campaigns.display_settings'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('display_priority')
                                    ->label(__('campaigns.display_priority'))
                                    ->numeric()
                                    ->default(0),
                                Select::make('status')
                                    ->label(__('campaigns.status'))
                                    ->options([
                                        'draft' => __('campaigns.status_draft'),
                                        'active' => __('campaigns.status_active'),
                                        'paused' => __('campaigns.status_paused'),
                                        'completed' => __('campaigns.status_completed'),
                                    ])
                                    ->required(),
                            ]),
                        FileUpload::make('banner_image')
                            ->label(__('campaigns.banner_image'))
                            ->image()
                            ->directory('campaigns/banners')
                            ->columnSpanFull(),
                        TextInput::make('banner_alt_text_lt')
                            ->label(__('campaigns.banner_alt_text_lt'))
                            ->maxLength(255),
                        TextInput::make('banner_alt_text_en')
                            ->label(__('campaigns.banner_alt_text_en'))
                            ->maxLength(255),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('cta_text_lt')
                                    ->label(__('campaigns.cta_text_lt'))
                                    ->maxLength(255),
                                TextInput::make('cta_text_en')
                                    ->label(__('campaigns.cta_text_en'))
                                    ->maxLength(255),
                            ]),
                        TextInput::make('cta_url')
                            ->label(__('campaigns.cta_url'))
                            ->url()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('campaigns.tracking_settings'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('track_conversions')
                                    ->label(__('campaigns.track_conversions')),
                                Toggle::make('send_notifications')
                                    ->label(__('campaigns.send_notifications')),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('max_uses')
                                    ->label(__('campaigns.max_uses'))
                                    ->numeric()
                                    ->minValue(0),
                                TextInput::make('budget_limit')
                                    ->label(__('campaigns.budget_limit'))
                                    ->numeric()
                                    ->prefix('€')
                                    ->minValue(0),
                            ]),
                    ])
                    ->columns(2),

                Section::make(__('campaigns.seo_settings'))
                    ->schema([
                        TextInput::make('meta_title_lt')
                            ->label(__('campaigns.meta_title_lt'))
                            ->maxLength(255),
                        TextInput::make('meta_title_en')
                            ->label(__('campaigns.meta_title_en'))
                            ->maxLength(255),
                        Textarea::make('meta_description_lt')
                            ->label(__('campaigns.meta_description_lt'))
                            ->maxLength(500)
                            ->rows(3),
                        Textarea::make('meta_description_en')
                            ->label(__('campaigns.meta_description_en'))
                            ->maxLength(500)
                            ->rows(3),
                        Toggle::make('social_media_ready')
                            ->label(__('campaigns.social_media_ready')),
                    ])
                    ->columns(2),

                Section::make(__('campaigns.metadata'))
                    ->schema([
                        KeyValue::make('metadata')
                            ->label(__('campaigns.metadata'))
                            ->keyLabel(__('campaigns.key'))
                            ->valueLabel(__('campaigns.value'))
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('campaigns.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('campaigns.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'active' => 'success',
                        'paused' => 'warning',
                        'completed' => 'info',
                    }),
                TextColumn::make('channel.name')
                    ->label(__('campaigns.channel'))
                    ->sortable(),
                TextColumn::make('zone.name')
                    ->label(__('campaigns.zone'))
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->label(__('campaigns.starts_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label(__('campaigns.ends_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('total_views')
                    ->label(__('campaigns.total_views'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_clicks')
                    ->label(__('campaigns.total_clicks'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_conversions')
                    ->label(__('campaigns.total_conversions'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('conversion_rate')
                    ->label(__('campaigns.conversion_rate'))
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) . '%' : '0%')
                    ->sortable(),
                IconColumn::make('is_featured')
                    ->label(__('campaigns.is_featured'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('campaigns.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('campaigns.status'))
                    ->options([
                        'draft' => __('campaigns.status_draft'),
                        'active' => __('campaigns.status_active'),
                        'paused' => __('campaigns.status_paused'),
                        'completed' => __('campaigns.status_completed'),
                    ]),
                SelectFilter::make('channel_id')
                    ->label(__('campaigns.channel'))
                    ->relationship('channel', 'name'),
                SelectFilter::make('zone_id')
                    ->label(__('campaigns.zone'))
                    ->relationship('zone', 'name'),
                TernaryFilter::make('is_featured')
                    ->label(__('campaigns.is_featured')),
                TernaryFilter::make('track_conversions')
                    ->label(__('campaigns.track_conversions')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make('analytics')
                    ->label(__('campaigns.analytics'))
                    ->icon('heroicon-o-chart-bar')
                    ->url(fn (Campaign $record): string => route('campaigns.analytics', $record))
                    ->openUrlInNewTab(),
                Action::make('duplicate')
                    ->label(__('campaigns.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function (Campaign $record) {
                        $newCampaign = $record->replicate();
                        $newCampaign->name = $record->name . ' (Copy)';
                        $newCampaign->slug = $record->slug . '-copy';
                        $newCampaign->status = 'draft';
                        $newCampaign->starts_at = null;
                        $newCampaign->ends_at = null;
                        $newCampaign->save();
                        
                        return redirect()->route('filament.admin.resources.campaigns.edit', $newCampaign);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('campaigns.activate_selected'))
                        ->icon('heroicon-o-play')
                        ->action(fn ($records) => $records->each->update(['status' => 'active']))
                        ->requiresConfirmation(),
                    BulkAction::make('pause')
                        ->label(__('campaigns.pause_selected'))
                        ->icon('heroicon-o-pause')
                        ->action(fn ($records) => $records->each->update(['status' => 'paused']))
                        ->requiresConfirmation(),
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

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::active()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
