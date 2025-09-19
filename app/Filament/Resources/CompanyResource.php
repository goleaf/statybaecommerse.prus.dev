<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\CompanyResource\Pages;
use App\Models\Company;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('companies.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('companies.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $schema
     * @return Form
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Section::make(__('companies.basic_information'))
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label(__('companies.name'))
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('email')
                                ->label(__('companies.email'))
                                ->email()
                                ->maxLength(255),
                        ]),
                    Forms\Components\TextInput::make('phone')
                        ->label(__('companies.phone'))
                        ->tel()
                        ->maxLength(20),
                    Forms\Components\TextInput::make('website')
                        ->label(__('companies.website'))
                        ->url()
                        ->maxLength(255),
                    Forms\Components\Textarea::make('description')
                        ->label(__('companies.description'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            Forms\Components\Section::make(__('companies.business_information'))
                ->schema([
                    Forms\Components\TextInput::make('industry')
                        ->label(__('companies.industry'))
                        ->maxLength(255),
                    Forms\Components\Select::make('size')
                        ->label(__('companies.size'))
                        ->options([
                            'small' => __('companies.sizes.small'),
                            'medium' => __('companies.sizes.medium'),
                            'large' => __('companies.sizes.large'),
                        ]),
                ]),
            Forms\Components\Section::make(__('companies.settings'))
                ->schema([
                    Forms\Components\Toggle::make('is_active')
                        ->label(__('companies.is_active'))
                        ->default(true),
                ]),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('companies.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('email')
                    ->label(__('companies.email'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label(__('companies.phone'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('website')
                    ->label(__('companies.website'))
                    ->url()
                    ->openUrlInNewTab()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('industry')
                    ->label(__('companies.industry'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('size')
                    ->label(__('companies.size'))
                    ->formatStateUsing(fn(string $state): string => __("companies.sizes.{$state}"))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'small' => 'green',
                        'medium' => 'blue',
                        'large' => 'purple',
                        default => 'gray',
                    }),
                IconColumn::make('is_active')
                    ->label(__('companies.is_active'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('companies.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('companies.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('size')
                    ->options([
                        'small' => __('companies.sizes.small'),
                        'medium' => __('companies.sizes.medium'),
                        'large' => __('companies.sizes.large'),
                    ]),
                TernaryFilter::make('is_active')
                    ->trueLabel(__('companies.active_only'))
                    ->falseLabel(__('companies.inactive_only'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggle_active')
                    ->label(fn(Company $record): string => $record->is_active ? __('companies.deactivate') : __('companies.activate'))
                    ->icon(fn(Company $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn(Company $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (Company $record): void {
                        $record->update(['is_active' => !$record->is_active]);

                        Notification::make()
                            ->title($record->is_active ? __('companies.activated_successfully') : __('companies.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label(__('companies.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('companies.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label(__('companies.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);
                            Notification::make()
                                ->title(__('companies.bulk_deactivated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Get the relations for this resource.
     * @return array
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the pages for this resource.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'view' => Pages\ViewCompany::route('/{record}'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
