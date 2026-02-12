<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductVariantResource\RelationManagers;

use App\Models\VariantImage;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected static ?string $recordTitleAttribute = 'alt_text';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('messages.images');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                FileUpload::make('image_path')
                    ->label(__('messages.image'))
                    ->image()
                    ->disk('public')
                    ->directory('variant-images')
                    ->imageEditor()
                    ->required(fn (string $operation): bool => $operation === 'create'),
                TextInput::make('alt_text')
                    ->label(__('messages.alt_text'))
                    ->maxLength(255),
                TextInput::make('description')
                    ->label(__('messages.description')),
                TextInput::make('sort_order')
                    ->label(__('messages.sort_order'))
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                Toggle::make('is_primary')
                    ->label(__('messages.is_main'))
                    ->default(false),
                Toggle::make('is_active')
                    ->label(__('messages.active'))
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_url')
                    ->label(__('messages.preview')),
                TextColumn::make('alt_text')
                    ->label(__('messages.alt_text'))
                    ->searchable(),
                TextColumn::make('sort_order')
                    ->label(__('messages.sort_order'))
                    ->sortable(),
                IconColumn::make('is_primary')
                    ->sortable()
                    ->label(__('messages.is_main'))
                    ->boolean(),
                IconColumn::make('is_active')
                    ->sortable()
                    ->label(__('messages.active'))
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(fn (array $data): array => $this->normalizePayload($data))
                    ->using(fn (array $data): VariantImage => $this->getOwnerRecord()->images()->create($data)),
            ])
            ->actions([
                EditAction::make()
                    ->mutateDataUsing(fn (array $data): array => $this->normalizePayload($data)),
                DeleteAction::make(),
            ])
            ->bulkActions([
                //
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
    }

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizePayload(array $data): array
    {
        $imagePath = $data['image_path'] ?? null;

        if (is_array($imagePath)) {
            $data['image_path'] = Arr::first($imagePath);
        }

        $data['sort_order'] = max(0, (int) ($data['sort_order'] ?? 0));
        $data['is_primary'] = (bool) ($data['is_primary'] ?? false);
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        return $data;
    }
}
