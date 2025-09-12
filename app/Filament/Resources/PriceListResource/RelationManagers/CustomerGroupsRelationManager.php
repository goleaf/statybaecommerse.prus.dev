<?php declare(strict_types=1);

namespace App\Filament\Resources\PriceListResource\RelationManagers;

use App\Models\CustomerGroup;
use Filament\Schemas\Schema;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Grid;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;

final class CustomerGroupsRelationManager extends RelationManager
{
    protected static string $relationship = 'customerGroups';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('admin.price_lists.customer_groups');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('admin.customer_groups.basic_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('admin.customer_groups.fields.name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->live()
                                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => 
                                        $operation === 'create' ? $set('slug', \Str::slug($state)) : null
                                    ),
                                TextInput::make('slug')
                                    ->label(__('admin.customer_groups.fields.slug'))
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->rules(['regex:/^[a-z0-9_-]+$/']),
                                TextInput::make('discount_percentage')
                                    ->label(__('admin.customer_groups.fields.discount_percentage'))
                                    ->numeric()
                                    ->suffix('%')
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->maxValue(100),
                                Toggle::make('is_enabled')
                                    ->label(__('admin.customer_groups.fields.is_enabled'))
                                    ->default(true),
                            ]),
                        Textarea::make('description')
                            ->label(__('admin.customer_groups.fields.description'))
                            ->rows(3),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.customer_groups.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label(__('admin.customer_groups.fields.slug'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('discount_percentage')
                    ->label(__('admin.customer_groups.fields.discount_percentage'))
                    ->numeric()
                    ->suffix('%')
                    ->alignEnd()
                    ->sortable(),
                IconColumn::make('is_enabled')
                    ->label(__('admin.customer_groups.fields.is_enabled'))
                    ->boolean(),
                TextColumn::make('users_count')
                    ->label(__('admin.customer_groups.fields.users_count'))
                    ->counts('users')
                    ->numeric()
                    ->alignEnd(),
                TextColumn::make('created_at')
                    ->label(__('admin.customer_groups.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_enabled')
                    ->label(__('admin.customer_groups.fields.is_enabled'))
                    ->options([
                        1 => __('admin.common.yes'),
                        0 => __('admin.common.no'),
                    ]),
                TernaryFilter::make('has_discount')
                    ->label(__('admin.customer_groups.fields.has_discount'))
                    ->queries(
                        true: fn (Builder $query) => $query->where('discount_percentage', '>', 0),
                        false: fn (Builder $query) => $query->where('discount_percentage', '=', 0),
                    ),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label(__('admin.price_lists.attach_customer_group'))
                    ->preloadRecordSelect(),
            ])
            ->actions([
                ViewAction::make()
                    ->label(__('admin.actions.view')),
                EditAction::make()
                    ->label(__('admin.actions.edit')),
                Tables\Actions\DetachAction::make()
                    ->label(__('admin.actions.detach')),
            ])
            ->defaultSort('name', 'asc')
            ->striped()
            ->paginated([10, 25, 50]);
    }
}
