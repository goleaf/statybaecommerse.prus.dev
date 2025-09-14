<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\CompanyResource\Pages;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\BulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

final class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    /** @var UnitEnum|string|null */
    protected static $navigationGroup = NavigationGroup::Marketing;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Company Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('email')
                            ->email()
                            ->maxLength(255)
                            ->columnSpan(1),

                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(255)
                            ->columnSpan(1),

                        TextInput::make('website')
                            ->url()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('address')
                            ->rows(3)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Business Details')
                    ->schema([
                        Select::make('industry')
                            ->options([
                                'construction' => 'Construction',
                                'manufacturing' => 'Manufacturing',
                                'technology' => 'Technology',
                                'retail' => 'Retail',
                                'services' => 'Services',
                                'healthcare' => 'Healthcare',
                                'education' => 'Education',
                                'finance' => 'Finance',
                                'other' => 'Other',
                            ])
                            ->searchable()
                            ->columnSpan(1),

                        Select::make('size')
                            ->options([
                                'small' => 'Small (1-50 employees)',
                                'medium' => 'Medium (51-200 employees)',
                                'large' => 'Large (200+ employees)',
                            ])
                            ->columnSpan(1),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('phone')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                BadgeColumn::make('industry')
                    ->colors([
                        'primary' => 'construction',
                        'secondary' => 'manufacturing',
                        'success' => 'technology',
                        'warning' => 'retail',
                        'info' => 'services',
                    ])
                    ->sortable(),

                BadgeColumn::make('size')
                    ->colors([
                        'success' => 'small',
                        'warning' => 'medium',
                        'danger' => 'large',
                    ])
                    ->sortable(),

                TextColumn::make('subscriber_count')
                    ->label('Subscribers')
                    ->getStateUsing(fn (Company $record): int => $record->subscriber_count)
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('active_subscriber_count')
                    ->label('Active Subscribers')
                    ->getStateUsing(fn (Company $record): int => $record->active_subscriber_count)
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('industry')
                    ->options([
                        'construction' => 'Construction',
                        'manufacturing' => 'Manufacturing',
                        'technology' => 'Technology',
                        'retail' => 'Retail',
                        'services' => 'Services',
                        'healthcare' => 'Healthcare',
                        'education' => 'Education',
                        'finance' => 'Finance',
                        'other' => 'Other',
                    ]),

                SelectFilter::make('size')
                    ->options([
                        'small' => 'Small',
                        'medium' => 'Medium',
                        'large' => 'Large',
                    ]),

                SelectFilter::make('is_active')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('view_subscribers')
                    ->label('View Subscribers')
                    ->icon('heroicon-o-users')
                    ->color('info')
                    ->url(fn (Company $record): string => 
                        route('filament.admin.resources.subscribers.index', ['tableFilters' => [
                            'company' => ['value' => $record->name]
                        ]])
                    )
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title('Companies activated successfully')
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->action(function (Collection $records) {
                            $records->each->update(['is_active' => false]);
                            Notification::make()
                                ->title('Companies deactivated successfully')
                                ->success()
                                ->send();
                        }),

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
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }

    public static function getGlobalSearchResultTitle($record): string
    {
        return $record->name;
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Industry' => $record->industry,
            'Size' => $record->size,
            'Email' => $record->email,
            'Subscribers' => $record->subscriber_count,
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'industry'];
    }
}
