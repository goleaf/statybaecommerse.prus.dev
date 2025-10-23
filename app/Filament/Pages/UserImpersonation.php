<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use BackedEnum;
use App\Filament\Tables\Concerns\ConfiguresToggleableTableLayout;
use App\Models\User;
use Filament\Pages\Page;
use UnitEnum;
use Filament\Tables;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Hydrat\TableLayoutToggle\Concerns\HasToggleableTable;

final class UserImpersonation extends Page implements HasTable
{
    use ConfiguresToggleableTableLayout;
    use HasToggleableTable;
    use InteractsWithTable;

    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-user';

    public static function getNavigationGroup(): BackedEnum|string|null
    {
        return 'System'; // Keep impersonation tools alongside other system controls.
    }

    protected static ?string $title = 'User Impersonation';

    protected static ?string $slug = 'user-impersonation';

    protected string $view = 'filament.pages.user-impersonation';

    public function table(Table $table): Table   
    {
        // Configure the Filament table definition for the resource.
        $table = $table
            ->query(User::query()->where('is_admin', false))
            ->columns([
                TextColumn::make('name')->label('Name')->searchable(),
                TextColumn::make('email')->label('Email')->searchable(),
            ])
            ->actions([
                Action::make('impersonate')
                    ->label('Impersonate')
                    ->action(function (User $record): void {
                        session(['impersonate.original_user_id' => auth()->id()]);
                        auth()->login($record);
                    }),
                Action::make('send_notification')
                    ->form([
                        Tables\Components\TextInput::make('title')->required(),
                        Tables\Components\Textarea::make('message')->required(),
                        Tables\Components\Select::make('type')->options([
                            'info'    => 'Info',
                            'success' => 'Success',
                            'warning' => 'Warning',
                            'danger'  => 'Danger',
                        ])->required(),
                    ])
                    ->action(function (User $record, array $data): void {
                        $record->notify(new \Illuminate\Notifications\Messages\BroadcastMessage([
                            'title'   => $data['title'],
                            'message' => $data['message'],
                            'type'    => $data['type'],
                        ]));
                    }),
            ]);

        return $this->applyToggleableTableLayout($table); // Preserve stored table layout preferences.
    }
}
