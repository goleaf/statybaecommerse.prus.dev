<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignCustomerSegmentResource\Pages;
use App\Models\CampaignCustomerSegment;
use App\Models\Scopes\ActiveScope;
use BackedEnum;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class CampaignCustomerSegmentResource extends Resource
{
    protected static ?string $model = CampaignCustomerSegment::class;

    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $recordTitleAttribute = 'segment_type';

    public static function getNavigationGroup(): ?string
    {
        return 'Marketing';
    }

    public static function getNavigationLabel(): string
    {
        return __('campaign_customer_segments.navigation');
    }

    public static function getPluralModelLabel(): string
    {
        return __('campaign_customer_segments.plural');
    }

    public static function getModelLabel(): string
    {
        return __('campaign_customer_segments.single');
    }

    public static function form(Schema $schema): Schema   
    {
        return $schema->schema([
            Section::make(__('campaign_customer_segments.tabs.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('campaign_id')
                                ->label(__('campaign_customer_segments.campaign'))
                                ->relationship('campaign', 'name', fn (Builder $query) => $query->withoutGlobalScopes())
                                ->searchable()
                                ->preload()
                                ->required(),
                            Select::make('customer_group_id')
                                ->label(__('campaign_customer_segments.customer_group'))
                                ->relationship('customerGroup', 'name', fn (Builder $query) => $query->withoutGlobalScopes())
                                ->searchable()
                                ->preload()
                                ->required(),
                            Select::make('segment_type')
                                ->label(__('campaign_customer_segments.segment_type'))
                                ->options([
                                    'demographic'   => __('campaign_customer_segments.types.demographic'),
                                    'behavioral'    => __('campaign_customer_segments.types.behavioral'),
                                    'geographic'    => __('campaign_customer_segments.types.geographic'),
                                    'psychographic' => __('campaign_customer_segments.types.psychographic'),
                                ])
                                ->required(),
                            Toggle::make('is_active')
                                ->label(__('campaign_customer_segments.is_active'))
                                ->default(true),
                            TextInput::make('sort_order')
                                ->label(__('campaign_customer_segments.sort_order'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->helperText(__('campaign_customer_segments.sort_order_help')),
                        ]),
                    KeyValue::make('segment_criteria')
                        ->label(__('campaign_customer_segments.segment_criteria'))
                        ->keyLabel(__('campaign_customer_segments.criteria_key'))
                        ->valueLabel(__('campaign_customer_segments.criteria_value'))
                        ->addButtonLabel(__('campaign_customer_segments.add_criteria'))
                        ->reorderable()
                        ->columnSpanFull()
                        ->helperText(__('campaign_customer_segments.segment_criteria_help')),
                    TagsInput::make('targeting_tags')
                        ->label(__('campaign_customer_segments.targeting_tags'))
                        ->placeholder(__('campaign_customer_segments.add_targeting_tag'))
                        ->columnSpanFull(),
                    Textarea::make('custom_conditions')
                        ->label(__('campaign_customer_segments.custom_conditions'))
                        ->helperText(__('campaign_customer_segments.custom_conditions_help'))
                        ->rows(4)
                        ->columnSpanFull(),
                ]),
            Section::make(__('campaign_customer_segments.tabs.advanced_settings'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Toggle::make('track_performance')
                                ->label(__('campaign_customer_segments.track_performance'))
                                ->default(false),
                            Toggle::make('auto_optimize')
                                ->label(__('campaign_customer_segments.auto_optimize'))
                                ->default(false),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table   
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return $table
            ->columns([
                TextColumn::make('campaign.name')
                    ->label(__('campaign_customer_segments.campaign_name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('customerGroup.name')
                    ->label(__('campaign_customer_segments.customer_group_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('segment_type')
                    ->label(__('campaign_customer_segments.segment_type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'demographic'   => 'primary',
                        'behavioral'    => 'success',
                        'geographic'    => 'info',
                        'psychographic' => 'warning',
                        default         => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => __('campaign_customer_segments.types.' . $state))
                    ->sortable(),
                TextColumn::make('targeting_tags')
                    ->label(__('campaign_customer_segments.targeting_tags'))
                    ->formatStateUsing(function ($state): string {
                        if (blank($state)) {
                            return __('campaign_customer_segments.no_targeting_tags');
                        }

                        if (is_array($state)) {
                            return implode(', ', $state);
                        }

                        return (string) $state;
                    })
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('track_performance')
                    ->label(__('campaign_customer_segments.track_performance'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('auto_optimize')
                    ->label(__('campaign_customer_segments.auto_optimize'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label(__('campaign_customer_segments.is_active'))
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label(__('campaign_customer_segments.sort_order'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('campaign_customer_segments.created_at'))
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('campaign_customer_segments.updated_at'))
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('segment_type')
                    ->label(__('campaign_customer_segments.segment_type'))
                    ->options([
                        'demographic'   => __('campaign_customer_segments.types.demographic'),
                        'behavioral'    => __('campaign_customer_segments.types.behavioral'),
                        'geographic'    => __('campaign_customer_segments.types.geographic'),
                        'psychographic' => __('campaign_customer_segments.types.psychographic'),
                    ]),
                TernaryFilter::make('is_active')
                    ->label(__('campaign_customer_segments.is_active'))
                    ->boolean(),
                TernaryFilter::make('track_performance')
                    ->label(__('campaign_customer_segments.track_performance'))
                    ->boolean(),
                TrashedFilter::make(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public static function infolist(Schema $schema): Schema   
    {
        // Provide the infolist schema using the Filament v4 return type.
        return $schema->schema([
            InfolistSection::make(__('campaign_customer_segments.tabs.basic_information'))
                ->schema([
                    TextEntry::make('campaign.name')
                        ->label(__('campaign_customer_segments.campaign_name')),
                    TextEntry::make('customerGroup.name')
                        ->label(__('campaign_customer_segments.customer_group_name')),
                    TextEntry::make('segment_type')
                        ->label(__('campaign_customer_segments.segment_type'))
                        ->badge(),
                    KeyValueEntry::make('segment_criteria')
                        ->label(__('campaign_customer_segments.segment_criteria')),
                    TextEntry::make('targeting_tags')
                        ->label(__('campaign_customer_segments.targeting_tags'))
                        ->formatStateUsing(fn (?array $state): string => empty($state) ? __('campaign_customer_segments.no_targeting_tags') : implode(', ', $state ?? []))
                        ->columnSpanFull(),
                    TextEntry::make('custom_conditions')
                        ->label(__('campaign_customer_segments.custom_conditions'))
                        ->columnSpanFull(),
                ])
                ->columns(2),
            InfolistSection::make(__('campaign_customer_segments.tabs.advanced_settings'))
                ->schema([
                    IconEntry::make('track_performance')
                        ->label(__('campaign_customer_segments.track_performance'))
                        ->boolean(),
                    IconEntry::make('auto_optimize')
                        ->label(__('campaign_customer_segments.auto_optimize'))
                        ->boolean(),
                    IconEntry::make('is_active')
                        ->label(__('campaign_customer_segments.is_active'))
                        ->boolean(),
                    TextEntry::make('sort_order')
                        ->label(__('campaign_customer_segments.sort_order')),
                ])
                ->columns(2),
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCampaignCustomerSegments::route('/'),
            'create' => Pages\CreateCampaignCustomerSegment::route('/create'),
            'view'   => Pages\ViewCampaignCustomerSegment::route('/{record}'),
            'edit'   => Pages\EditCampaignCustomerSegment::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'segment_type',
            'campaign.name',
            'customerGroup.name',
            'targeting_tags',
            'custom_conditions',
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = self::getEloquentQuery()->count();

        return $count > 0 ? (string) $count : null;
    }

    /**
     * @return Builder<CampaignCustomerSegment>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                ActiveScope::class,
            ]);
    }
}