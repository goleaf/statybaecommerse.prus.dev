<?php declare(strict_types=1);

namespace App\Filament\Resources;
use App\Filament\Resources\ProductRequestResource\Pages;
use App\Models\ProductRequest;
use Filament\Forms\Form;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use UnitEnum;
use BackedEnum;
final class ProductRequestResource extends Resource
{
    protected static ?string $model = ProductRequest::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static string | UnitEnum | null $navigationGroup = "Products";
    protected static ?int $navigationSort = 16;
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                Forms\Components\TextInput::make('name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                Forms\Components\TextInput::make('phone')
                    ->tel()
                Forms\Components\Textarea::make('message')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('requested_quantity')
                    ->numeric()
                    ->default(1)
                    ->minValue(1),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'completed' => 'Completed',
                    ])
                    ->default('pending'),
                Forms\Components\Textarea::make('admin_notes')
                    ->label('Admin Notes')
                Forms\Components\DateTimePicker::make('responded_at')
                    ->label('Responded At'),
                Forms\Components\Select::make('responded_by')
                    ->relationship('responder', 'name')
                    ->label('Responded By')
                    ->searchable(),
            ]);
    }
    public static function table(Table $table): Table
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                Tables\Columns\TextColumn::make('name')
                Tables\Columns\TextColumn::make('email')
                Tables\Columns\TextColumn::make('phone')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('requested_quantity')
                    ->label('Quantity')
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'info' => 'completed',
                    ]),
                Tables\Columns\TextColumn::make('responded_at')
                    ->label('Responded At')
                    ->dateTime()
                    ->sortable()
                Tables\Columns\TextColumn::make('created_at')
                Tables\Columns\TextColumn::make('updated_at')
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                Tables\Filters\SelectFilter::make('product_id')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ->defaultSort('created_at', 'desc');
    public static function getRelations(): array
        return [
            //
        ];
    public static function getPages(): array
            'index' => Pages\ListProductRequests::route('/'),
            'create' => Pages\CreateProductRequest::route('/create'),
            'edit' => Pages\EditProductRequest::route('/{record}/edit'),
}
