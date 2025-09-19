<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignCustomerSegmentResource\Pages;
use App\Models\Campaign;
use App\Models\CampaignCustomerSegment;
use App\Models\CustomerGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

final class CampaignCustomerSegmentResource extends Resource
{
    protected static ?string $model = CampaignCustomerSegment::class;

    protected static $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'segment_type';

    public static function getModelLabel(): string
    {
        return __('campaign_customer_segments.single');
    }

    public static function getPluralModelLabel(): string
    {
        return __('campaign_customer_segments.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('campaign_customer_segments.navigation');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make(__('campaign_customer_segments.tabs'))
                    ->tabs([
                        Tab::make(__('campaign_customer_segments.basic_information'))
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Select::make('campaign_id')
                                    ->label(__('campaign_customer_segments.campaign'))
                                    ->relationship('campaign', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Select::make('customer_group_id')
                                    ->label(__('campaign_customer_segments.customer_group'))
                                    ->relationship('customerGroup', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Select::make('segment_type')
                                    ->label(__('campaign_customer_segments.segment_type'))
                                    ->options([
                                        'demographic' => __('campaign_customer_segments.types.demographic'),
                                        'behavioral' => __('campaign_customer_segments.types.behavioral'),
                                        'geographic' => __('campaign_customer_segments.types.geographic'),
                                        'psychographic' => __('campaign_customer_segments.types.psychographic'),
                                    ])
                                    ->required()
                                    ->native(false),

                                TextInput::make('segment_criteria')
                                    ->label(__('campaign_customer_segments.segment_criteria'))
                                    ->helperText(__('campaign_customer_segments.segment_criteria_help'))
                                    ->maxLength(255),
                            ]),

                        Tab::make(__('campaign_customer_segments.settings'))
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Toggle::make('is_active')
                                    ->label(__('campaign_customer_segments.is_active'))
                                    ->default(true),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('campaign.name')
                    ->label(__('campaign_customer_segments.campaign'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customerGroup.name')
                    ->label(__('campaign_customer_segments.customer_group'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('segment_type')
                    ->label(__('campaign_customer_segments.segment_type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'demographic' => 'success',
                        'behavioral' => 'info',
                        'geographic' => 'warning',
                        'psychographic' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('segment_criteria')
                    ->label(__('campaign_customer_segments.segment_criteria'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),

                IconColumn::make('is_active')
                    ->label(__('campaign_customer_segments.is_active'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('created_at')
                    ->label(__('campaign_customer_segments.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('campaign_customer_segments.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('campaign_id')
                    ->label(__('campaign_customer_segments.campaign'))
                    ->relationship('campaign', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('customer_group_id')
                    ->label(__('campaign_customer_segments.customer_group'))
                    ->relationship('customerGroup', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('segment_type')
                    ->label(__('campaign_customer_segments.segment_type'))
                    ->options([
                        'demographic' => __('campaign_customer_segments.types.demographic'),
                        'behavioral' => __('campaign_customer_segments.types.behavioral'),
                        'geographic' => __('campaign_customer_segments.types.geographic'),
                        'psychographic' => __('campaign_customer_segments.types.psychographic'),
                    ]),

                TernaryFilter::make('is_active')
                    ->label(__('campaign_customer_segments.is_active'))
                    ->placeholder(__('campaign_customer_segments.all_records'))
                    ->trueLabel(__('campaign_customer_segments.active_only'))
                    ->falseLabel(__('campaign_customer_segments.inactive_only')),
            ])
            ->actions([
                // Actions will be handled by pages
            ])
            ->bulkActions([
                // Bulk actions will be handled by pages
            ]);
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
            'index' => Pages\ListCampaignCustomerSegments::route('/'),
            'create' => Pages\CreateCampaignCustomerSegment::route('/create'),
            'edit' => Pages\EditCampaignCustomerSegment::route('/{record}/edit'),
        ];
    }
}
