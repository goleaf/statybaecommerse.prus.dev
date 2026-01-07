<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationConfigResourceSimple\Pages;

use App\Filament\Resources\RecommendationConfigResourceSimple;
use App\Models\RecommendationConfigSimple;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

final class EditRecommendationConfigSimple extends EditRecord
{
    protected static string $resource = RecommendationConfigResourceSimple::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Normalise relationship identifiers ahead of hydration so form state mirrors the deterministic
        // ordering expected by feature tests and the resource sync logic. When Filament omits relation
        // attributes from the initial payload we eagerly fall back to the loaded record so pivot data is
        // still present inside the Livewire form state.
        $data['products'] = $this->resolveRelationIdentifiers('products', $data['products'] ?? null);
        $data['categories'] = $this->resolveRelationIdentifiers('categories', $data['categories'] ?? null);

        return $data;
    }

    protected bool $hasSyncedRelationState = false;

    protected function afterFill(): void
    {
        if ($this->hasSyncedRelationState) {
            return;
        }

        $this->hasSyncedRelationState = true;

        $state = $this->form->getState();

        $products = $this->resolveRelationIdentifiers('products', data_get($state, 'products'));
        $categories = $this->resolveRelationIdentifiers('categories', data_get($state, 'categories'));

        $this->form->fill([
            ...$state,
            'products'   => $products,
            'categories' => $categories,
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Action::make('toggle_active')
                ->label(fn (RecommendationConfigSimple $record): string => $record->is_active ? __('recommendation_configs_simple.deactivate') : __('recommendation_configs_simple.activate'))
                ->icon(fn (RecommendationConfigSimple $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                ->color(fn (RecommendationConfigSimple $record): string => $record->is_active ? 'warning' : 'success')
                ->action(function (RecommendationConfigSimple $record): void {
                    $record->update(['is_active' => ! $record->is_active]);
                })
                ->requiresConfirmation(),
            Action::make('set_default')
                ->label(__('recommendation_configs_simple.set_default'))
                ->icon('heroicon-o-star')
                ->color('warning')
                ->visible(fn (RecommendationConfigSimple $record): bool => ! $record->is_default)
                ->action(function (RecommendationConfigSimple $record): void {
                    RecommendationConfigSimple::where('is_default', true)->update(['is_default' => false]);
                    $record->update(['is_default' => true]);
                })
                ->requiresConfirmation(),
        ];
    }

    /**
     * Resolve a relation field into the canonical identifier array expected by the resource helper.
     *
     * @return array<int, string>
     */
    private function resolveRelationIdentifiers(string $relation, mixed $value): array
    {
        // Defer to the provided value first so manual overrides survive subsequent hydration passes.
        $state = $this->prepareRelationState($value);

        if ($state === null || $state === []) {
            // When Filament supplies an empty placeholder we fallback to the eager loaded relation on the
            // record. This keeps edit forms populated even if mutateFormDataBeforeFill receives no pivot data.
            $state = $this->prepareRelationState($this->getRelationValue($relation));
        }

        return RecommendationConfigResourceSimple::normaliseRelationIdentifiers($state);
    }

    /**
     * Flatten relation payloads into simple arrays so downstream normalisation can work uniformly.
     *
     * @return array<int|string, mixed>|null
     */
    private function prepareRelationState(mixed $value): ?array
    {
        if ($value instanceof Collection) {
            return $value->all();
        }

        if ($value instanceof Arrayable) {
            return (array) $value->toArray();
        }

        if (is_array($value)) {
            return $value;
        }

        if ($value === null) {
            return null;
        }

        return [$value];
    }

    /**
     * Fetch relation data from the underlying record while ensuring the relationship is loaded once.
     */
    private function getRelationValue(string $relation): mixed
    {
        $record = $this->getRecord();

        // Load the relation lazily to avoid unnecessary queries when the edit form receives explicit data.
        $record->loadMissing($relation);

        return $record->{$relation} ?? null;
    }
}
