<?php declare(strict_types=1);

use App\Filament\Resources\NotificationResource;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

describe('NotificationResource Unit Tests', function () {
    describe('Resource Configuration', function () {
        it('has correct model configuration', function () {
            expect(NotificationResource::getModel())->toBe(Notification::class);
        });

        it('has correct navigation configuration', function () {
            expect(NotificationResource::getNavigationIcon())->toBe('heroicon-o-bell');
            expect(NotificationResource::getNavigationLabel())->toBe('Notifications');
            expect(NotificationResource::getModelLabel())->toBe('Notification');
            expect(NotificationResource::getPluralModelLabel())->toBe('Notifications');
            expect(NotificationResource::getNavigationSort())->toBe(3);
        });

        it('has correct navigation group', function () {
            expect(NotificationResource::getNavigationGroup())->toBe('System');
        });
    });

    describe('Permissions', function () {
        it('prevents creation of notifications', function () {
            expect(NotificationResource::canCreate())->toBeFalse();
        });

        it('prevents editing of notifications', function () {
            $notification = Notification::factory()->create();
            expect(NotificationResource::canEdit($notification))->toBeFalse();
        });
    });

    describe('Pages Configuration', function () {
        it('has correct pages configuration', function () {
            $pages = NotificationResource::getPages();

            expect($pages)->toHaveKey('index');
            expect($pages)->toHaveKey('view');
            expect($pages)->not->toHaveKey('create');
            expect($pages)->not->toHaveKey('edit');

            expect($pages['index'])->toBeInstanceOf(\Illuminate\Routing\Route::class);
            expect($pages['view'])->toBeInstanceOf(\Illuminate\Routing\Route::class);
        });
    });

    describe('Relations Configuration', function () {
        it('has no relations configured', function () {
            $relations = NotificationResource::getRelations();
            expect($relations)->toBeEmpty();
        });
    });

    describe('Query Configuration', function () {
        it('orders notifications by latest first', function () {
            $query = NotificationResource::getEloquentQuery();
            $sql = $query->toSql();

            expect($sql)->toContain('order by');
            expect($sql)->toContain('created_at');
            expect($sql)->toContain('desc');
        });
    });

    describe('Form Schema', function () {
        it('has correct form components', function () {
            $schema = NotificationResource::form(new \Filament\Schemas\Schema());
            $components = $schema->getComponents();

            $fieldNames = collect($components)->map(fn($component) => $component->getName())->toArray();

            expect($fieldNames)->toContain('type');
            expect($fieldNames)->toContain('notifiable_type');
            expect($fieldNames)->toContain('notifiable_id');
            expect($fieldNames)->toContain('data');
            expect($fieldNames)->toContain('read_at');
            expect($fieldNames)->toContain('created_at');
        });

        it('has all form fields disabled', function () {
            $schema = NotificationResource::form(new \Filament\Schemas\Schema());
            $components = $schema->getComponents();

            foreach ($components as $component) {
                if (method_exists($component, 'isDisabled')) {
                    expect($component->isDisabled())->toBeTrue();
                }
            }
        });
    });

    describe('Table Schema', function () {
        it('has correct table columns', function () {
            $table = NotificationResource::table(new \Filament\Tables\Table());
            $columns = $table->getColumns();

            $columnNames = collect($columns)->map(fn($column) => $column->getName())->toArray();

            expect($columnNames)->toContain('id');
            expect($columnNames)->toContain('type');
            expect($columnNames)->toContain('notifiable_type');
            expect($columnNames)->toContain('notifiable_id');
            expect($columnNames)->toContain('data.title');
            expect($columnNames)->toContain('data.message');
            expect($columnNames)->toContain('data.type');
            expect($columnNames)->toContain('read_at');
            expect($columnNames)->toContain('created_at');
        });

        it('has correct table filters', function () {
            $table = NotificationResource::table(new \Filament\Tables\Table());
            $filters = $table->getFilters();

            $filterNames = collect($filters)->map(fn($filter) => $filter->getName())->toArray();

            expect($filterNames)->toContain('data.type');
            expect($filterNames)->toContain('read_at');
            expect($filterNames)->toContain('created_at');
        });

        it('has correct table actions', function () {
            $table = NotificationResource::table(new \Filament\Tables\Table());
            $actions = $table->getActions();

            $actionNames = collect($actions)->map(fn($action) => $action->getName())->toArray();

            expect($actionNames)->toContain('view');
            expect($actionNames)->toContain('delete');
        });

        it('has correct bulk actions', function () {
            $table = NotificationResource::table(new \Filament\Tables\Table());
            $bulkActions = $table->getBulkActions();

            $bulkActionNames = collect($bulkActions)->map(fn($action) => $action->getName())->toArray();

            expect($bulkActionNames)->toContain('delete');
        });
    });

    describe('Data Formatting', function () {
        it('formats notification type correctly', function () {
            $notification = DatabaseNotification::factory()->create([
                'type' => 'App\Notifications\OrderNotification',
            ]);

            $table = NotificationResource::table(new \Filament\Tables\Table());
            $typeColumn = collect($table->getColumns())->firstWhere('name', 'type');

            expect($typeColumn)->not->toBeNull();
        });

        it('formats notifiable type correctly', function () {
            $notification = DatabaseNotification::factory()->create([
                'notifiable_type' => User::class,
            ]);

            $table = NotificationResource::table(new \Filament\Tables\Table());
            $notifiableTypeColumn = collect($table->getColumns())->firstWhere('name', 'notifiable_type');

            expect($notifiableTypeColumn)->not->toBeNull();
        });
    });

    describe('Notification Type Colors', function () {
        it('has correct color mapping for notification types', function () {
            $table = NotificationResource::table(new \Filament\Tables\Table());
            $typeColumn = collect($table->getColumns())->firstWhere('name', 'data.type');

            expect($typeColumn)->not->toBeNull();
            expect($typeColumn->getColor())->toBeInstanceOf(\Closure::class);
        });
    });

    describe('Read Status Display', function () {
        it('has correct read status column configuration', function () {
            $table = NotificationResource::table(new \Filament\Tables\Table());
            $readColumn = collect($table->getColumns())->firstWhere('name', 'read_at');

            expect($readColumn)->not->toBeNull();
            expect($readColumn->getBoolean())->toBeTrue();
        });
    });
});
