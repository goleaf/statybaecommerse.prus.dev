<?php declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\CustomerGroup;
use App\Models\Order;
use App\Models\User;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Actions as Actions;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;
use UnitEnum;

final class CustomerSegmentation extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.customer-segmentation';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-user-group';
    protected static string|UnitEnum|null $navigationGroup = \App\Enums\NavigationGroup::Marketing;
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.marketing');
    }

    public ?string $segmentType = null;
    public array $segmentCriteria = [];

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.customer_segmentation');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getCustomerQuery())
            ->deferLoading(false)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.table.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('admin.table.email'))
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('orders_count')
                    ->label(__('admin.table.total_orders'))
                    ->numeric()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('orders_sum_total')
                    ->label(__('admin.table.total_spent'))
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('avg_order_value')
                    ->label(__('admin.table.avg_order_value'))
                    ->money('EUR')
                    ->getStateUsing(fn(User $record): float =>
                        $record->orders_count > 0 ? $record->orders_sum_total / $record->orders_count : 0),
                Tables\Columns\TextColumn::make('last_order_date')
                    ->label(__('admin.table.last_order'))
                    ->getStateUsing(fn(User $record): ?string =>
                        $record->orders()->latest()->first()?->created_at?->diffForHumans()),
                Tables\Columns\TextColumn::make('customer_segment')
                    ->label(__('admin.table.segment'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'vip' => 'success',
                        'regular' => 'info',
                        'new' => 'warning',
                        'inactive' => 'danger',
                        default => 'gray',
                    })
                    ->getStateUsing(fn(User $record): string => $this->calculateCustomerSegment($record)),
            ])
            ->headerActions([])
            ->recordActions([
                Actions\Action::make('assign_to_group')
                    ->label(__('admin.actions.assign_to_group'))
                    ->icon('heroicon-o-user-plus')
                    ->form([
                        Forms\Components\Select::make('customer_group_id')
                            ->label(__('admin.fields.customer_group'))
                            ->relationship('customerGroups', 'name')
                            ->required(),
                    ])
                    ->action(function (array $data, User $record): void {
                        $record->customerGroups()->syncWithoutDetaching([$data['customer_group_id']]);

                        \Filament\Notifications\Notification::make()
                            ->title(__('admin.notifications.customer_assigned'))
                            ->success()
                            ->send();
                    }),
                Actions\Action::make('send_marketing_email')
                    ->label(__('admin.actions.send_email'))
                    ->icon('heroicon-o-envelope')
                    ->color('info')
                    ->form([
                        Forms\Components\TextInput::make('subject')
                            ->label(__('admin.fields.email_subject'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\RichEditor::make('content')
                            ->label(__('admin.fields.email_content'))
                            ->required(),
                        Forms\Components\Select::make('template')
                            ->label(__('admin.fields.email_template'))
                            ->options([
                                'promotional' => __('admin.email_templates.promotional'),
                                'newsletter' => __('admin.email_templates.newsletter'),
                                'product_update' => __('admin.email_templates.product_update'),
                                'discount_offer' => __('admin.email_templates.discount_offer'),
                            ])
                            ->default('promotional'),
                    ])
                    ->action(function (array $data, User $record): void {
                        // Send marketing email
                        $record->notify(new \App\Notifications\MarketingEmail(
                            $data['subject'],
                            $data['content'],
                            $data['template']
                        ));

                        \Filament\Notifications\Notification::make()
                            ->title(__('admin.notifications.email_sent'))
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\BulkAction::make('bulk_assign_group')
                        ->label(__('admin.actions.assign_to_group'))
                        ->icon('heroicon-m-user-group')
                        ->form([
                            Forms\Components\Select::make('customer_group_id')
                                ->label(__('admin.fields.customer_group'))
                                ->relationship('customerGroups', 'name')
                                ->required(),
                        ])
                        ->action(function (array $data, $records): void {
                            foreach ($records as $record) {
                                $record->customerGroups()->syncWithoutDetaching([$data['customer_group_id']]);
                            }

                            \Filament\Notifications\Notification::make()
                                ->title(__('admin.notifications.bulk_assignment_completed'))
                                ->success()
                                ->send();
                        }),
                    Actions\BulkAction::make('bulk_marketing_email')
                        ->label(__('admin.actions.send_bulk_email'))
                        ->icon('heroicon-m-envelope')
                        ->color('info')
                        ->form([
                            Forms\Components\TextInput::make('subject')
                                ->label(__('admin.fields.email_subject'))
                                ->required(),
                            Forms\Components\RichEditor::make('content')
                                ->label(__('admin.fields.email_content'))
                                ->required(),
                        ])
                        ->action(function (array $data, $records): void {
                            foreach ($records as $record) {
                                $record->notify(new \App\Notifications\MarketingEmail(
                                    $data['subject'],
                                    $data['content'],
                                    'bulk'
                                ));
                            }

                            \Filament\Notifications\Notification::make()
                                ->title(__('admin.notifications.bulk_emails_sent'))
                                ->body(__('admin.notifications.sent_to_customers', ['count' => count($records)]))
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }

    protected function getCustomerQuery(): Builder
    {
        $query = User::query()
            ->where('is_admin', false)
            ->withCount(['orders'])
            ->withSum(['orders' => function (Builder $orderQuery) {
                $orderQuery->where('status', 'completed');
            }], 'total');

        // Apply segmentation filters
        if ($this->segmentType === 'value') {
            // High-value customers - total order value > 500
            $query->where(function ($q) {
                $q->whereRaw('(SELECT SUM(total) FROM orders WHERE orders.user_id = users.id AND status = "completed") > ?', [500]);
            });
        } elseif ($this->segmentType === 'frequency') {
            // Frequent customers - more than 5 orders
            $query->where(function ($q) {
                $q->whereRaw('(SELECT COUNT(*) FROM orders WHERE orders.user_id = users.id) > ?', [5]);
            });
        } elseif ($this->segmentType === 'recent') {
            $query->whereHas('orders', function (Builder $orderQuery) {
                $orderQuery->where('created_at', '>=', now()->subDays(30));
            });
        }

        return $query;
    }

    private function calculateCustomerSegment(User $customer): string
    {
        $totalSpent = $customer->orders_sum_total ?? 0;
        $orderCount = $customer->orders_count ?? 0;
        $lastOrder = $customer->orders()->latest()->first()?->created_at;

        if ($totalSpent > 1000 && $orderCount > 10) {
            return 'vip';
        } elseif ($totalSpent > 500 || $orderCount > 5) {
            return 'regular';
        } elseif ($orderCount === 0) {
            return 'new';
        } elseif (!$lastOrder || $lastOrder->lt(now()->subMonths(6))) {
            return 'inactive';
        }

        return 'regular';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create_segment')
                ->label(__('admin.actions.create_segment'))
                ->icon('heroicon-o-plus-circle')
                ->form([
                    Forms\Components\TextInput::make('name')
                        ->label(__('admin.fields.segment_name'))
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Textarea::make('description')
                        ->label(__('admin.fields.description'))
                        ->rows(3),
                    Forms\Components\Select::make('criteria_type')
                        ->label(__('admin.fields.criteria_type'))
                        ->options([
                            'total_spent' => __('admin.segment_criteria.total_spent'),
                            'order_count' => __('admin.segment_criteria.order_count'),
                            'last_order_days' => __('admin.segment_criteria.last_order_days'),
                            'avg_order_value' => __('admin.segment_criteria.avg_order_value'),
                        ])
                        ->required()
                        ->live(),
                    Forms\Components\TextInput::make('criteria_value')
                        ->label(__('admin.fields.criteria_value'))
                        ->numeric()
                        ->required(),
                    Forms\Components\Select::make('criteria_operator')
                        ->label(__('admin.fields.operator'))
                        ->options([
                            'gt' => __('admin.operators.greater_than'),
                            'gte' => __('admin.operators.greater_than_equal'),
                            'lt' => __('admin.operators.less_than'),
                            'lte' => __('admin.operators.less_than_equal'),
                            'eq' => __('admin.operators.equal'),
                        ])
                        ->default('gte')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $baseSlug = \Illuminate\Support\Str::slug((string) $data['name']);
                    $slug = $baseSlug;
                    $i = 1;
                    while (CustomerGroup::where('slug', $slug)->exists()) {
                        $slug = $baseSlug . '-' . $i;
                        $i++;
                    }

                    CustomerGroup::create([
                        'name' => $data['name'],
                        'slug' => $slug,
                        'description' => $data['description'] ?? '',
                        'is_enabled' => true,
                        'conditions' => [
                            'type' => $data['criteria_type'] ?? null,
                            'operator' => $data['criteria_operator'] ?? null,
                            'value' => $data['criteria_value'] ?? null,
                        ],
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title(__('admin.notifications.segment_created'))
                        ->success()
                        ->send();
                }),
            Actions\Action::make('segment_analysis')
                ->label(__('admin.actions.segment_analysis'))
                ->icon('heroicon-o-chart-pie')
                ->action(function (): void {
                    $segments = [
                        'vip' => 0,
                        'regular' => 0,
                        'new' => 0,
                        'inactive' => 0,
                    ];

                    User::where('is_admin', false)
                        ->withCount('orders')
                        ->withSum('orders', 'total')
                        ->chunk(100, function ($customers) use (&$segments) {
                            foreach ($customers as $customer) {
                                $segment = $this->calculateCustomerSegment($customer);
                                $segments[$segment]++;
                            }
                        });

                    \Filament\Notifications\Notification::make()
                        ->title(__('admin.notifications.segment_analysis_complete'))
                        ->body(sprintf(
                            'VIP: %d, Regular: %d, New: %d, Inactive: %d',
                            $segments['vip'],
                            $segments['regular'],
                            $segments['new'],
                            $segments['inactive']
                        ))
                        ->info()
                        ->send();
                }),
        ];
    }
}
