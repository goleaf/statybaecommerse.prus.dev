<?php declare(strict_types=1);

namespace App\Filament\Resources\ReviewResource\Widgets;

use App\Models\Review;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

final class ReviewApprovalWidget extends BaseWidget
{
    protected static ?string $heading = 'admin.reviews.widgets.pending_approval';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Review::query()
                    ->with(['user', 'product'])
                    ->where('is_approved', false)
                    ->whereNull('rejected_at')
                    ->latest()
                    ->limit(20)
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('admin.reviews.fields.user'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label(__('admin.reviews.fields.product'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('rating')
                    ->label(__('admin.reviews.fields.rating'))
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn($state) => $state . ' ⭐'),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('admin.reviews.fields.title'))
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('comment')
                    ->label(__('admin.reviews.fields.comment'))
                    ->limit(100)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 100) {
                            return null;
                        }
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.reviews.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                TableAction::make('approve')
                    ->label(__('admin.reviews.actions.approve'))
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(fn(Review $record) => $record->approve())
                    ->requiresConfirmation(),
                TableAction::make('reject')
                    ->label(__('admin.reviews.actions.reject'))
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label(__('admin.reviews.fields.rejection_reason'))
                            ->rows(3)
                            ->required(),
                    ])
                    ->action(function (Review $record, array $data): void {
                        $record->reject();
                        // You could store the rejection reason in metadata
                        $record->update([
                            'metadata' => array_merge($record->metadata ?? [], [
                                'rejection_reason' => $data['rejection_reason'],
                                'rejected_by' => auth()->id(),
                            ])
                        ]);
                    })
                    ->requiresConfirmation(),
                Tables\Actions\ViewAction::make()
                    ->url(fn(Review $record): string => route('filament.admin.resources.reviews.view', $record)),
            ])
            ->paginated(false);
    }
}
