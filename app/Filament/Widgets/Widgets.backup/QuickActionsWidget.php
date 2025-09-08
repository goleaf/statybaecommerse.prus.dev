<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\ProductResource;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\BrandResource;
use App\Filament\Resources\CategoryResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Widgets\Widget;

final class QuickActionsWidget extends Widget
{
    protected string $view = 'filament.widgets.quick-actions';

    protected static ?int $sort = 2;

    protected function getViewData(): array
    {
        return [
            'actions' => $this->getActions(),
        ];
    }

    protected function getActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('create_product')
                    ->label(__('Create Product'))
                    ->icon('heroicon-o-plus-circle')
                    ->color('primary')
                    ->url(ProductResource::getUrl('create')),

                Action::make('create_order')
                    ->label(__('Create Order'))
                    ->icon('heroicon-o-shopping-bag')
                    ->color('success')
                    ->url(OrderResource::getUrl('create')),

                Action::make('create_user')
                    ->label(__('Create User'))
                    ->icon('heroicon-o-user-plus')
                    ->color('info')
                    ->url(UserResource::getUrl('create')),

                Action::make('create_brand')
                    ->label(__('Create Brand'))
                    ->icon('heroicon-o-building-storefront')
                    ->color('warning')
                    ->url(BrandResource::getUrl('create')),

                Action::make('create_category')
                    ->label(__('Create Category'))
                    ->icon('heroicon-o-folder-plus')
                    ->color('secondary')
                    ->url(CategoryResource::getUrl('create')),
            ])
                ->label(__('Quick Create'))
                ->icon('heroicon-o-plus')
                ->color('primary'),

            ActionGroup::make([
                Action::make('view_products')
                    ->label(__('Manage Products'))
                    ->icon('heroicon-o-cube')
                    ->url(ProductResource::getUrl('index')),

                Action::make('view_orders')
                    ->label(__('Manage Orders'))
                    ->icon('heroicon-o-shopping-bag')
                    ->url(OrderResource::getUrl('index')),

                Action::make('view_users')
                    ->label(__('Manage Users'))
                    ->icon('heroicon-o-users')
                    ->url(UserResource::getUrl('index')),
            ])
                ->label(__('Management'))
                ->icon('heroicon-o-cog-6-tooth')
                ->color('gray'),
        ];
    }
    
    public static function canView(): bool
    {
        return auth()->user()->can('view_dashboard');
    }
}
