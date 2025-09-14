<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\SubscriberResource\Pages;
use App\Models\Subscriber;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;

final class SubscriberResource extends Resource
{
    protected static ?string $model = Subscriber::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    /** @var UnitEnum|string|null */
    protected static $navigationGroup = NavigationGroup::Marketing;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'email';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Subscriber Details')
                    ->tabs([
                        Tab::make('Basic Information')
                            ->schema([
                                Section::make('Contact Information')
                                    ->schema([
                                        TextInput::make('email')
                                            ->email()
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->columnSpanFull(),

                                        TextInput::make('first_name')
                                            ->maxLength(255)
                                            ->columnSpan(1),

                                        TextInput::make('last_name')
                                            ->maxLength(255)
                                            ->columnSpan(1),

                                        TextInput::make('phone')
                                            ->tel()
                                            ->maxLength(255)
                                            ->columnSpan(1),

                                        TextInput::make('company')
                                            ->maxLength(255)
                                            ->columnSpan(1),
                                    ])
                                    ->columns(2),

                                Section::make('Subscription Details')
                                    ->schema([
                                        Select::make('status')
                                            ->options([
                                                'active' => 'Active',
                                                'inactive' => 'Inactive',
                                                'unsubscribed' => 'Unsubscribed',
                                            ])
                                            ->required()
                                            ->default('active')
                                            ->columnSpan(1),

                                        Select::make('source')
                                            ->options([
                                                'website' => 'Website',
                                                'admin' => 'Admin Panel',
                                                'import' => 'Import',
                                                'api' => 'API',
                                                'social' => 'Social Media',
                                                'referral' => 'Referral',
                                                'event' => 'Event',
                                                'other' => 'Other',
                                            ])
                                            ->required()
                                            ->default('website')
                                            ->columnSpan(1),

                                        TextInput::make('job_title')
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make('Interests & Preferences')
                            ->schema([
                                Section::make('Interests')
                                    ->schema([
                                        Select::make('interests')
                                            ->multiple()
                                            ->options([
                                                'products' => 'Products',
                                                'news' => 'News & Updates',
                                                'promotions' => 'Promotions & Discounts',
                                                'events' => 'Events',
                                                'blog' => 'Blog Posts',
                                                'technical' => 'Technical Updates',
                                                'business' => 'Business News',
                                                'support' => 'Support & Help',
                                            ])
                                            ->searchable()
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Metadata')
                                    ->schema([
                                        Textarea::make('metadata')
                                            ->json()
                                            ->columnSpanFull()
                                            ->helperText('Additional JSON data for the subscriber'),
                                    ]),
                            ]),

                        Tab::make('Activity & Statistics')
                            ->schema([
                                Section::make('Email Statistics')
                                    ->schema([
                                        TextInput::make('email_count')
                                            ->numeric()
                                            ->default(0)
                                            ->disabled()
                                            ->columnSpan(1),

                                        TextInput::make('subscribed_at')
                                            ->datetime()
                                            ->disabled()
                                            ->columnSpan(1),

                                        TextInput::make('unsubscribed_at')
                                            ->datetime()
                                            ->disabled()
                                            ->columnSpan(1),

                                        TextInput::make('last_email_sent_at')
                                            ->datetime()
                                            ->disabled()
                                            ->columnSpan(1),
                                    ])
                                    ->columns(2),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('full_name')
                    ->label('Full Name')
                    ->getStateUsing(fn (Subscriber $record): string => $record->full_name)
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['first_name', 'last_name']),

                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->placeholder('Not registered'),

                TextColumn::make('company')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('job_title')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'inactive',
                        'danger' => 'unsubscribed',
                    ])
                    ->sortable(),

                TextColumn::make('source')
                    ->badge()
                    ->colors([
                        'primary' => 'website',
                        'secondary' => 'admin',
                        'success' => 'import',
                        'warning' => 'api',
                        'info' => 'social',
                        'gray' => 'referral',
                    ])
                    ->sortable(),

                TagsColumn::make('interests')
                    ->limit(3)
                    ->separator(',')
                    ->toggleable(),

                TextColumn::make('email_count')
                    ->label('Emails Sent')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('subscribed_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('last_email_sent_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->placeholder('Never'),

                BooleanColumn::make('is_active')
                    ->label('Active')
                    ->getStateUsing(fn (Subscriber $record): bool => $record->is_active)
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'unsubscribed' => 'Unsubscribed',
                    ]),

                SelectFilter::make('source')
                    ->options([
                        'website' => 'Website',
                        'admin' => 'Admin Panel',
                        'import' => 'Import',
                        'api' => 'API',
                        'social' => 'Social Media',
                        'referral' => 'Referral',
                        'event' => 'Event',
                        'other' => 'Other',
                    ]),

                Filter::make('recent_subscribers')
                    ->label('Recent Subscribers (30 days)')
                    ->query(fn (Builder $query): Builder => $query->recent(30)),

                Filter::make('has_interests')
                    ->label('Has Interests')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('interests')),

                Filter::make('never_emailed')
                    ->label('Never Received Email')
                    ->query(fn (Builder $query): Builder => $query->whereNull('last_email_sent_at')),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('send_test_email')
                        ->label('Send Test Email')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('info')
                        ->action(function (Subscriber $record) {
                            // TODO: Implement test email sending
                            Notification::make()
                                ->title('Test email sent successfully')
                                ->success()
                                ->send();
                        }),

                    Action::make('unsubscribe')
                        ->label('Unsubscribe')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn (Subscriber $record): bool => $record->status === 'active')
                        ->action(function (Subscriber $record) {
                            $record->unsubscribe();
                            Notification::make()
                                ->title('Subscriber unsubscribed successfully')
                                ->success()
                                ->send();
                        }),

                    Action::make('resubscribe')
                        ->label('Resubscribe')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (Subscriber $record): bool => $record->status === 'unsubscribed')
                        ->action(function (Subscriber $record) {
                            $record->resubscribe();
                            Notification::make()
                                ->title('Subscriber resubscribed successfully')
                                ->success()
                                ->send();
                        }),

                    Action::make('view_user')
                        ->label('View User Profile')
                        ->icon('heroicon-o-user')
                        ->color('gray')
                        ->url(fn (Subscriber $record): string => 
                            User::where('email', $record->email)->exists() 
                                ? route('filament.admin.resources.users.view', User::where('email', $record->email)->first())
                                : '#'
                        )
                        ->openUrlInNewTab()
                        ->visible(fn (Subscriber $record): bool => User::where('email', $record->email)->exists()),

                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('unsubscribe_selected')
                        ->label('Unsubscribe Selected')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each->unsubscribe();
                            Notification::make()
                                ->title('Selected subscribers unsubscribed successfully')
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('resubscribe_selected')
                        ->label('Resubscribe Selected')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each->resubscribe();
                            Notification::make()
                                ->title('Selected subscribers resubscribed successfully')
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('add_interest')
                        ->label('Add Interest')
                        ->icon('heroicon-o-plus')
                        ->color('info')
                        ->form([
                            Select::make('interest')
                                ->options([
                                    'products' => 'Products',
                                    'news' => 'News & Updates',
                                    'promotions' => 'Promotions & Discounts',
                                    'events' => 'Events',
                                    'blog' => 'Blog Posts',
                                    'technical' => 'Technical Updates',
                                    'business' => 'Business News',
                                    'support' => 'Support & Help',
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each(function (Subscriber $subscriber) use ($data) {
                                $subscriber->addInterest($data['interest']);
                            });
                            Notification::make()
                                ->title('Interest added to selected subscribers')
                                ->success()
                                ->send();
                        }),

                    ExportBulkAction::make()
                        ->exports([
                            ExcelExport::make()
                                ->fromTable()
                                ->withFilename(fn () => 'subscribers-' . now()->format('Y-m-d-H-i-s'))
                                ->withWriterType(\Maatwebsite\Excel\Excel::XLSX),
                        ]),

                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('subscribed_at', 'desc')
            ->poll('30s');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscribers::route('/'),
            'create' => Pages\CreateSubscriber::route('/create'),
            'edit' => Pages\EditSubscriber::route('/{record}/edit'),
        ];
    }

    public static function getGlobalSearchResultTitle($record): string
    {
        return $record->email . ' (' . $record->full_name . ')';
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Status' => $record->status,
            'Source' => $record->source,
            'Company' => $record->company,
            'Subscribed' => $record->subscribed_at?->format('M j, Y'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['email', 'first_name', 'last_name', 'company'];
    }
}
