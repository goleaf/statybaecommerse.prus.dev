<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignCustomerSegmentResource\Pages;
use App\Models\CampaignCustomerSegment;
use App\Models\Scopes\ActiveScope;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
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
     * Keep the navigation icon compatible with Filament v4 expectations while supporting BackedEnum fallbacks.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $recordTitleAttribute = 'segment_type';

    public static function getNavigationGroup(): string
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Group the primary campaign relationship details inside a clearly labelled section.
                Section::make(__('campaign_customer_segments.tabs.basic_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                // Allow administrators to link the segment to any campaign without respecting global scopes.
                                Select::make('campaign_id')
                                    ->label(__('campaign_customer_segments.campaign'))
                                    ->relationship('campaign', 'name', fn (Builder $query) => $query->withoutGlobalScopes())
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                // Provide customer group selection with eager loading for smoother search results.
                                Select::make('customer_group_id')
                                    ->label(__('campaign_customer_segments.customer_group'))
                                    ->relationship('customerGroup', 'name', fn (Builder $query) => $query->withoutGlobalScopes())
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                // Offer an enum-like selector so segment types stay consistent with translation keys.
                                Select::make('segment_type')
                                    ->label(__('campaign_customer_segments.segment_type'))
                                    ->options([
                                        'demographic'   => __('campaign_customer_segments.types.demographic'),
                                        'behavioral'    => __('campaign_customer_segments.types.behavioral'),
                                        'geographic'    => __('campaign_customer_segments.types.geographic'),
                                        'psychographic' => __('campaign_customer_segments.types.psychographic'),
                                    ])
                                    ->required(),
                                // Keep activation toggles visible when creating a segment.
                                Toggle::make('is_active')
                                    ->label(__('campaign_customer_segments.is_active'))
                                    ->default(true),
                                // Maintain manual ordering controls with validation hints for administrators.
                                TextInput::make('sort_order')
                                    ->label(__('campaign_customer_segments.sort_order'))
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->helperText(__('campaign_customer_segments.sort_order_help')),
                            ]),
                        // Store flexible criteria as JSON while providing a helpful interface for key/value editing.
                        KeyValue::make('segment_criteria')
                            ->label(__('campaign_customer_segments.segment_criteria'))
                            ->keyLabel(__('campaign_customer_segments.criteria_key'))
                            ->valueLabel(__('campaign_customer_segments.criteria_value'))
                            ->addButtonLabel(__('campaign_customer_segments.add_criteria'))
                            ->reorderable()
                            ->columnSpanFull()
                            ->helperText(__('campaign_customer_segments.segment_criteria_help')),
                        // Ensure tags are stored as arrays, supporting multiple targeting strategies per segment.
                        TagsInput::make('targeting_tags')
                            ->label(__('campaign_customer_segments.targeting_tags'))
                            ->placeholder(__('campaign_customer_segments.add_targeting_tag'))
                            ->columnSpanFull(),
                        // Provide room for additional textual rules that may not map to structured criteria.
                        Textarea::make('custom_conditions')
                            ->label(__('campaign_customer_segments.custom_conditions'))
                            ->helperText(__('campaign_customer_segments.custom_conditions_help'))
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
                // Split advanced options into their own section to avoid overwhelming the main form.
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

                        if (is_scalar($state)) {
                            return (string) $state;
                        }

                        return __('campaign_customer_segments.no_targeting_tags');
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
                // Use shared action classes so table actions stay in sync with Filament's centralized behaviours.
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->bulkActions([
                // Group destructive bulk actions together to provide a predictable dropdown in the toolbar.
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order')
            ->striped()
            ->paginationPageOptions([10, 25, 50, 100]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        // Provide the infolist schema using the Filament v4 return type.
        return $infolist
            ->schema([
                InfolistSection::make(__('campaign_customer_segments.tabs.basic_information'))
                    ->schema([
                        TextEntry::make('campaign.name')
                            ->label(__('campaign_customer_segments.campaign_name')),
                        TextEntry::make('customerGroup.name')
                            ->label(__('campaign_customer_segments.customer_group_name')),
                        TextEntry::make('segment_type')
                            ->label(__('campaign_customer_segments.segment_type'))
                            ->badge(),
                        // Render the stored JSON criteria as a key/value list for quicker debugging.
                        KeyValueEntry::make('segment_criteria')
                            ->label(__('campaign_customer_segments.segment_criteria')),
                        // Present tags as a comma separated list while gracefully handling empty states.
                        TextEntry::make('targeting_tags')
                            ->label(__('campaign_customer_segments.targeting_tags'))
                            ->formatStateUsing(
                                fn ($state): string => match (true) {
                                    is_array($state) && $state !== []  => implode(', ', $state),
                                    is_string($state) && $state !== '' => $state,
                                    default                            => __('campaign_customer_segments.no_targeting_tags'),
                                }
                            )
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
