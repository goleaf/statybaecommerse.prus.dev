<?php declare(strict_types=1);

namespace App\Filament\Resources\CampaignResource\RelationManagers;

use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;

final class TranslationsRelationManager extends RelationManager
{
    protected static string $relationship = 'translations';

    protected static ?string $title = 'Translations';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Select::make('locale')
                ->label($this->label('Locale'))
                ->options($this->localeOptions())
                ->required()
                ->searchable(),
            TextInput::make('name')
                ->label($this->label('Name'))
                ->required()
                ->maxLength(255),
            TextInput::make('slug')
                ->label($this->label('Slug'))
                ->maxLength(255),
            Textarea::make('description')
                ->label($this->label('Description'))
                ->rows(3),
            TextInput::make('subject')
                ->label($this->label('Subject'))
                ->maxLength(255),
            Textarea::make('content')
                ->label($this->label('Content'))
                ->rows(4),
            TextInput::make('cta_text')
                ->label($this->label('CTA text'))
                ->maxLength(120),
            TextInput::make('banner_alt_text')
                ->label($this->label('Banner alt text'))
                ->maxLength(255),
            TextInput::make('meta_title')
                ->label($this->label('Meta title'))
                ->maxLength(255),
            Textarea::make('meta_description')
                ->label($this->label('Meta description'))
                ->rows(3),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('locale')
                    ->label($this->label('Locale'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('name')
                    ->label($this->label('Name'))
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                TextColumn::make('slug')
                    ->label($this->label('Slug'))
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label($this->label('Updated'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('locale')
                    ->label($this->label('Locale'))
                    ->options($this->localeOptions()),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('locale');
    }

    private function localeOptions(): array
    {
        $configured = explode(',', (string) config('app.supported_locales', 'lt,en'));

        $locales = collect($configured)
            ->map(fn($locale) => trim((string) $locale))
            ->filter()
            ->unique()
            ->values();

        $names = [
            'lt' => 'Lithuanian',
            'en' => 'English',
            'de' => 'German',
            'ru' => 'Russian',
        ];

        return $locales
            ->mapWithKeys(fn(string $locale) => [$locale => $names[$locale] ?? strtoupper($locale)])
            ->toArray();
    }

    private function label(string $key, ?string $fallback = null): string
    {
        $translated = __($key);

        if ($translated !== $key) {
            return $translated;
        }

        return $fallback ?? $key;
    }
}
