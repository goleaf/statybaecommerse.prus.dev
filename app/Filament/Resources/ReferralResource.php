<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\ReferralResource\Pages;
use App\Models\Referral;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use BackedEnum;

final class ReferralResource extends Resource
{
    protected static ?string $model = Referral::class;
    protected static ?string $navigationIcon = 'heroicon-o-share';
    protected static ?int $navigationSort = 17;
    protected static ?string $recordTitleAttribute = 'referral_code';

    public static function getNavigationGroup(): string
    {
        return NavigationGroup::Referrals->getLabel();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->schema([
                Section::make('Referral Details')
                    ->columns(2)
                    ->schema([
                        Select::make('referrer_id')
                            ->relationship('referrer', 'name')
                            ->required(),
                        Select::make('referred_id')
                            ->relationship('referred', 'name')
                            ->required(),
                        TextInput::make('referral_code')
                            ->required()
                            ->maxLength(255),
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'active' => 'Active',
                                'completed' => 'Completed',
                                'expired' => 'Expired',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required(),
                        DatePicker::make('completed_at')
                            ->nullable(),
                        DatePicker::make('expires_at')
                            ->nullable(),
                        TextInput::make('source')
                            ->maxLength(255)
                            ->nullable(),
                        TextInput::make('campaign')
                            ->maxLength(255)
                            ->nullable(),
                        TextInput::make('utm_source')
                            ->maxLength(255)
                            ->nullable(),
                        TextInput::make('utm_medium')
                            ->maxLength(255)
                            ->nullable(),
                        TextInput::make('utm_campaign')
                            ->maxLength(255)
                            ->nullable(),
                        TextInput::make('ip_address')
                            ->maxLength(45)
                            ->nullable(),
                        TextInput::make('user_agent')
                            ->maxLength(255)
                            ->nullable(),
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->maxLength(65535)
                            ->nullable(),
                        Textarea::make('terms_conditions')
                            ->maxLength(65535)
                            ->nullable(),
                        Textarea::make('benefits_description')
                            ->maxLength(65535)
                            ->nullable(),
                        Textarea::make('how_it_works')
                            ->maxLength(65535)
                            ->nullable(),
                        TextInput::make('seo_title')
                            ->maxLength(255)
                            ->nullable(),
                        Textarea::make('seo_description')
                            ->maxLength(65535)
                            ->nullable(),
                        KeyValue::make('seo_keywords')
                            ->label('SEO Keywords (JSON)')
                            ->keyLabel('Keyword')
                            ->valueLabel('Value')
                            ->reorderable()
                            ->addActionLabel('Add Keyword')
                            ->columnSpanFull(),
                        KeyValue::make('metadata')
                            ->label('Metadata (JSON)')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->reorderable()
                            ->addActionLabel('Add Metadata Item')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('referral_code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('referrer.name')
                    ->label('Referrer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('referred.name')
                    ->label('Referred')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('source')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('campaign')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'expired' => 'Expired',
                        'cancelled' => 'Cancelled',
                    ]),
                SelectFilter::make('referrer_id')
                    ->relationship('referrer', 'name')
                    ->label('Referrer'),
                SelectFilter::make('referred_id')
                    ->relationship('referred', 'name')
                    ->label('Referred'),
                SelectFilter::make('source')
                    ->options(Referral::distinct()->pluck('source', 'source')->toArray()),
                SelectFilter::make('campaign')
                    ->options(Referral::distinct()->pluck('campaign', 'campaign')->toArray()),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListReferrals::route('/'),
            'create' => Pages\CreateReferral::route('/create'),
            'view' => Pages\ViewReferral::route('/{record}'),
            'edit' => Pages\EditReferral::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['referral_code', 'title', 'description', 'source', 'campaign', 'utm_source', 'utm_medium', 'utm_campaign'];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::$model::count();
    }
}