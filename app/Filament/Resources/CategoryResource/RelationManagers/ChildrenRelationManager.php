<?php declare(strict_types=1);

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use App\Filament\Resources\CategoryResource;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\BulkAction;

final class ChildrenRelationManager extends RelationManager
{
    protected static string $relationship = 'children';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $schema): Schema
    {
        return CategoryResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return CategoryResource::table($table)
            ->headerActions([
                \Filament\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['parent_id'] = $this->ownerRecord->getKey();
                        return $data;
                    }),
                Action::make('attach_existing_category')
                    ->label(__('translations.attach_existing_category'))
                    ->icon('heroicon-o-link')
                    ->form([
                        Forms\Components\Select::make('child_id')
                            ->label(__('translations.category'))
                            ->options(fn() => \App\Models\Category::query()
                                ->whereNull('parent_id')
                                ->whereKeyNot($this->ownerRecord->getKey())
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->filter(fn($label) => filled($label))
                                ->toArray())
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        \App\Models\Category::query()
                            ->whereKey((int) $data['child_id'])
                            ->update(['parent_id' => $this->ownerRecord->getKey()]);
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Action::make('detach_child')
                    ->label(__('translations.detach_child'))
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->requiresConfirmation()
                    ->action(function ($record): void {
                        $record->update(['parent_id' => null]);
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('bulk_detach')
                        ->label(__('translations.bulk_detach'))
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->action(function ($records): void {
                            foreach ($records as $record) {
                                $record->update(['parent_id' => null]);
                            }
                        }),
                ]),
            ])
            ->reorderable('sort_order');
    }
}
