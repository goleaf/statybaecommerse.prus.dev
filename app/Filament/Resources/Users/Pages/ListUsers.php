<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return [
            'all'     => Tab::make('All'),
            'company' => Tab::make(__('messages.company'))
                ->modifyQueryUsing(static fn (Builder $query): Builder => $query->whereNotNull('company_id')),
            'customer_groups' => self::relationTab('customerGroups', __('admin.navigation.customer_groups')),
            'partners' => self::relationTab('partners', __('messages.partners')),
        ];
    }

    private static function relationTab(string $relation, string $label): Tab
    {
        return Tab::make($label)
            ->modifyQueryUsing(static fn (Builder $query): Builder => $query->whereHas(
                $relation,
                static fn (Builder $relatedQuery): Builder => $relatedQuery->withoutGlobalScopes(),
            ));
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
