<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\AdminUser;
use App\Models\Comment;
use App\Models\User;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static ?string $recordTitleAttribute = 'content';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('messages.comments');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Textarea::make('content')
                    ->label(__('messages.comment'))
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('messages.user'))
                    ->sortable(),
                TextColumn::make('content')
                    ->label(__('messages.comment'))
                    ->limit(50),
                TextColumn::make('created_at')
                    ->label(__('messages.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        $data['content'] = trim((string) ($data['content'] ?? $data['body'] ?? ''));
                        unset($data['body']);

                        $data['user_id'] = $this->resolveCommentUserId();

                        if ($data['content'] === '') {
                            throw ValidationException::withMessages([
                                'content' => __('validation.required', ['attribute' => __('messages.comment')]),
                            ]);
                        }

                        $data['is_approved'] ??= true;
                        $data['is_pinned'] ??= false;
                        $data['likes_count'] ??= 0;

                        return $data;
                    })
                    ->using(fn (array $data): Comment => $this->getOwnerRecord()->comments()->create($data)),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    private function resolveCommentUserId(): ?int
    {
        $authenticatedUser = auth()->user() ?? auth('admin')->user();

        if ($authenticatedUser instanceof User) {
            return $authenticatedUser->getKey();
        }

        if ($authenticatedUser instanceof AdminUser) {
            $mappedUserId = User::query()
                ->where('email', $authenticatedUser->email)
                ->value('id');

            return is_numeric($mappedUserId) ? (int) $mappedUserId : null;
        }

        return null;
    }
}
