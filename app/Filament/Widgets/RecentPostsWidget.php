<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Post;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;

final class RecentPostsWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Recent Posts';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Post::query()->latest()->limit(5)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('images')
                    ->collection('images')
                    ->conversion('thumb')
                    ->size(40)
                    ->circular(),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('posts.fields.title'))
                    ->limit(30)
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('posts.fields.user_id'))
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('posts.fields.status'))
                    ->formatStateUsing(fn(string $state): string => __('posts.status.' . $state))
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'published',
                        'danger' => 'archived',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('posts.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }
}

