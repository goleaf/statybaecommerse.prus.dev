<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

final class UserImpersonation extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.user-impersonation';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-user';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'System';
    }

    protected static ?string $title = 'User Impersonation';

    protected static ?string $slug = 'user-impersonation';

    public function table(Table $table): Table
    {
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
                        TextInput::make('title')->required(),
                        Textarea::make('message')->required(),
                        Select::make('type')->options([
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

        return $this->applyToggleableTableLayout($table);
    }
}
