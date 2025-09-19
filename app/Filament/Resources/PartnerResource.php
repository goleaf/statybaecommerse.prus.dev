<?php declare(strict_types=1);

namespace App\Filament\Resources;
use App\Filament\Resources\PartnerResource\Pages;
use App\Models\Partner;
use Filament\Forms\Form;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use UnitEnum;
use BackedEnum;
final class PartnerResource extends Resource
{
    protected static ?string $model = Partner::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-handshake';
    /*protected static string | UnitEnum | null $navigationGroup = NavigationGroup::Users;
    protected static ?int $navigationSort = 1;
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('code')
                            ->maxLength(255)
                            ->unique(Partner::class, 'code', ignoreRecord: true),
                        Forms\Components\Select::make('tier_id')
                            ->relationship('tier', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Toggle::make('is_enabled')
                            ->label('Enabled')
                            ->default(true),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Contact Information')
                        Forms\Components\TextInput::make('contact_email')
                            ->email()
                        Forms\Components\TextInput::make('contact_phone')
                            ->tel()
                Forms\Components\Section::make('Financial Settings')
                        Forms\Components\TextInput::make('discount_rate')
                            ->label('Discount Rate (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.0001),
                        Forms\Components\TextInput::make('commission_rate')
                            ->label('Commission Rate (%)')
                Forms\Components\Section::make('Media')
                        Forms\Components\FileUpload::make('logo')
                            ->image()
                            ->directory('partner-logos')
                            ->visibility('public'),
                        Forms\Components\FileUpload::make('banner')
                            ->directory('partner-banners')
            ]);
    }
    public static function table(Table $table): Table
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->size(40)
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                Tables\Columns\TextColumn::make('tier.name')
                    ->label('Tier')
                Tables\Columns\TextColumn::make('contact_email')
                    ->label('Email')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('contact_phone')
                    ->label('Phone')
                Tables\Columns\TextColumn::make('discount_rate')
                    ->label('Discount Rate')
                    ->formatStateUsing(fn($state) => $state ? $state . '%' : '-')
                Tables\Columns\TextColumn::make('commission_rate')
                    ->label('Commission Rate')
                Tables\Columns\IconColumn::make('is_enabled')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                Tables\Columns\TextColumn::make('updated_at')
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tier_id')
                    ->relationship('tier', 'name')
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_enabled')
                    ->label('Enabled Only'),
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ->defaultSort('name');
    public static function getRelations(): array
        return [
            //
        ];
    public static function getPages(): array
            'index' => Pages\ListPartners::route('/'),
            'create' => Pages\CreatePartner::route('/create'),
            'edit' => Pages\EditPartner::route('/{record}/edit'),
}
